<?php

namespace TinCat\HdrjpgSdkPhp;

use ZipArchive;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Provider\ApiProvider;
use TinCat\HdrjpgSdkPhp\Entity\ConversionFile;
use TinCat\HdrjpgSdkPhp\Service\ResponseBuilderFile;
use TinCat\HdrjpgSdkPhp\Exception\ApiClientException;
use TinCat\HdrjpgSdkPhp\Exception\ConversionFailedException;
use TinCat\HdrjpgSdkPhp\Service\ResponseBuilderSingleConversion;

class Client
{
    private ApiProvider $apiProvider;

    /**
     * @param string $apiKey The hdrjpg.com secret api key.
     * @param bool $hostIsHttps Whether the requests to the API should be done via HTTPs. By default all requests are done via HTTPs.
     * @param string $hostName The API's hostname if it's different than the default api.hdrjpg.com.
     * @param int $hostPort The API's port if it's different than the default 443.
     */
    function __construct(
        string $apiKey,
        bool $hostIsHttps = true,
        string $hostName = 'api.hdrjpg.com',
        int $hostPort = 443
    )
    {
        $this->apiProvider = new ApiProvider(
            $apiKey,
            $hostIsHttps,
            $hostName,
            $hostPort
        );
    }

    /**
     * Submits a file for conversion
     * @param string $filePath
     * @param array $variants
     * @return Conversion
     */
    function submit(
        string $filePath,
        array $variants = []
    ): Conversion
    {
        return
            $this->apiProvider->query(
                new ResponseBuilderSingleConversion,
                'convert',
                'POST',
                [
                    'variant' => json_encode($variants)
                ],
                [
                    'sourceImage' => $filePath
                ]
            );
    }

    /**
     * Gets information about a conversion
     * @param string $conversionUuid The conversion UUID
     * @return Conversion
     */
    function getConversion(
        string $conversionUuid
    ): Conversion
    {
        return
            $this->apiProvider->query(
                new ResponseBuilderSingleConversion,
                'status',
                'GET',
                [
                    'conversionUuid' => $conversionUuid
                ],
            );
    }

    /**
     * Downloads a ZIP file that contains all the files resulting from a conversion
     * @param string $conversionUuid
     * @param string $destinationDirectory If not specified, the default system temp directory will be used.
     * @return string The complete downloaded file path
     */
    function downloadConversionResultZip(
        string $conversionUuid,
        string $destinationDirectory = ''
    ): string
    {
        if (!$destinationDirectory) {
            $destinationDirectory = sys_get_temp_dir();
        }

        $tempFilePath = sys_get_temp_dir().'/'.uniqid();

        $fileName =
            $this->apiProvider->query(
                new ResponseBuilderFile,
                'download',
                'GET',
                [
                    'conversionUuid' => $conversionUuid
                ],
                [],
                $tempFilePath
            );

        $finalFilePath = rtrim($destinationDirectory, '/').'/'.$fileName;
        rename($tempFilePath, $finalFilePath);
        return $finalFilePath;
    }

    /**
     * Downloads the files resulting from a conversion and stores them in the specified directory
     * @param string $conversionUuid
     * @param string $destinationDirectory If not specified, the default system temp directory will be used.
     * @return void
     */
    function downloadConversionResultFiles(
        string $conversionUuid,
        string $destinationDirectory = ''
    )
    {
        $fileName = $this->downloadConversionResultZip($conversionUuid);
        $zip = new ZipArchive;
        if ($zip->open($fileName) !== true) {
            throw new ApiClientException('Could not unzip the conversion result file');
        }
        $zip->extractTo($destinationDirectory);
        $zip->close();
        unlink($fileName);
    }

    /**
     * Submits a file for conversion
     * @param string $filePath
     * @param array $variants
     * @param string $destinationDirectory If not specified, the default system temp directory will be used.
     * @param callable $onProgress A function that will be called while the conversion is running each time the conversion state changes. The Conversion object will be passed to the function as the only parameter.
     * @param int $pollInterval The number of seconds to wait between calls to the API to obtain the status of the conversion.
     * @return Conversion
     */
    function convert(
        string $filePath,
        array $variants = [],
        string $destinationDirectory = '',
        callable $onProgress = null,
        int $pollInterval = 2
    ): Conversion
    {
        if ($pollInterval < 2) {
            throw new ApiClientException('Please do not poll faster than 2 seconds');
        }

        $conversion =
            $this->submit(
                $filePath,
                $variants
            );

        $onProgress($conversion);

        while (true) {

            $updatedConversion = $this->getConversion($conversion->uuid);

            if ($onProgress && $updatedConversion->isDifferentState($conversion)) {
                $onProgress($updatedConversion);
            }

            $conversion = $updatedConversion;

            if ($updatedConversion->isCanBeDownloaded()) {
                break;
            }

            if ($updatedConversion->status === Conversion::STATUS_FAILED) {

                $failDescriptions = [];
                if ($updatedConversion->failDescriptions) {
                    $failDescriptions = array_merge($failDescriptions, $updatedConversion->failDescriptions);
                }

                if ($updatedConversion->variants) {
                    foreach ($updatedConversion->variants as $variantKey => $variant) {
                        if ($variant->failDescriptions) {
                            $failDescriptions = array_merge(
                                $failDescriptions,
                                array_map(
                                    fn ($failDescriptions) => 'Variant #'.$variantKey.': '.$failDescriptions.' ('.$variant->outputImageFileName.')',
                                    $variant->failDescriptions
                                )
                            );
                        }
                    }
                }

                throw new ConversionFailedException('Conversion failed'.($failDescriptions ? ' ('.implode(' / ', $failDescriptions).')' : null));
            }

            sleep($pollInterval);
        }

        $this->downloadConversionResultFiles($conversion->uuid, $destinationDirectory);

        return $conversion;
    }
}
