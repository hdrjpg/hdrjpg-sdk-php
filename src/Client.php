<?php

namespace TinCat\HdrjpgSdkPhp;

use ZipArchive;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Provider\ApiProvider;
use TinCat\HdrjpgSdkPhp\Entity\ConversionFile;
use TinCat\HdrjpgSdkPhp\Service\ResponseBuilderFile;
use TinCat\HdrjpgSdkPhp\Exception\ApiClientException;
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
     * @param array $formats
     * @return Conversion
     */
    function submit(
        string $filePath,
        array $variants = [],
        array $formats = []
    ): Conversion
    {
        return
            $this->apiProvider->query(
                new ResponseBuilderSingleConversion,
                'convert',
                'POST',
                [
                    'variant' => $this->buildVariantQueryString($variants),
                    'output' => $this->buildOutputFormatQueryString($formats)
                ],
                [
                    'sourceHDRImage' => $filePath
                ]
            );
    }

    /**
     * @param mixed $variants
     * @return string A string that defines the specified $variants according to the `variant` API parameter syntax.
     */
    private function buildVariantQueryString(
        array $variants
    ): string
    {
        return
            implode(
                ',',
                array_map(
                    fn ($variant) =>
                        ($variant['width'] ?? '').
                        ($variant['height'] ?? false ? 'x'.$variant['height'] : '').
                        ($variant['quality'] ?? false || $variant['baseQuality'] ?? false || $variant['gainmapQuality'] ?? false ?
                            'q'.
                            ($variant['baseQuality'] ?? $variant['quality']).
                            ($variant['gainmapQuality'] ?? false ? '-'.$variant['gainmapQuality'] : '')
                        : '').
                        ($variant['fileName'] ?? false ? ';'.$variant['fileName'] : ''),
                    $variants
                )
            );
    }

    /**
     * @param int $format
     * @return string A string that defines a specific output format according to the `output` API parameter syntax.
     */
    private function buildOutputFormatQueryString(
        array $formats
    ): string
    {
        return
            implode(
                ',',
                array_map(
                    fn ($format) =>
                        [
                            ConversionFile::OUTPUT_FORMAT_JPEG => 'jpg',
                            ConversionFile::OUTPUT_FORMAT_JXL => 'jxl',
                            ConversionFile::OUTPUT_FORMAT_AVIF => 'avif',
                        ][$format],
                    $formats
                )
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
     * @param array $formats
     * @param string $destinationDirectory If not specified, the default system temp directory will be used.
     * @param callable $onProgress A function that will be called while the conversion is running each time the conversion state changes. The Conversion object will be passed to the function as the only parameter.
     * @return Conversion
     */
    function convert(
        string $filePath,
        array $variants = [],
        array $formats = [],
        string $destinationDirectory = '',
        callable $onProgress = null
    ): Conversion
    {
        $conversion =
            $this->submit(
                $filePath,
                $variants,
                $formats
            );

        while (true) {

            $updatedConversion = $this->getConversion($conversion->uuid);

            if ($onProgress && $updatedConversion->isDifferentState($conversion)) {
                $onProgress($updatedConversion);
            }

            $conversion = $updatedConversion;

            if ($updatedConversion->isCanBeDownloaded()) {
                break;
            }

            sleep(2);
        }

        $this->downloadConversionResultFiles($conversion->uuid, $destinationDirectory);

        if ($onProgress) {
            $onProgress($conversion);
        }

        return $conversion;
    }
}
