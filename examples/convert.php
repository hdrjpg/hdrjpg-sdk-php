<?php

/**
 * Example on how to perform an HDR image conversion using the simplified method
 */

require_once '../vendor/autoload.php';

use TinCat\HdrjpgSdkPhp\Client;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Entity\ConversionFile;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;

$client = new Client('<your API key>');

$destinationDirectory = './download';

if (!file_exists($destinationDirectory)) {
    mkdir($destinationDirectory, 0777, true);
}

try {

    echo 'Submitting file for conversion ...'."\n";

    // First step: Send the file for conversion
    $conversion =
        $client->convert(
            'example.heic',
            [
                [
                    'width' => 1200,
                    'quality' => 97
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'gainmapQuality' => 80,
                    'fileName' => 'image-converted'
                ]
            ],
            [
                ConversionFile::OUTPUT_FORMAT_JPEG,
                ConversionFile::OUTPUT_FORMAT_JXL,
                ConversionFile::OUTPUT_FORMAT_AVIF
            ],
            $destinationDirectory,
            function ($conversion)
            {
                echo
                    'Conversion status: '.
                    [
                        Conversion::STATUS_PENDING => 'Pending',
                        Conversion::STATUS_RUNNING => 'Running',
                        Conversion::STATUS_FAILED => 'Failed',
                        Conversion::STATUS_READY => 'Ready',
                        Conversion::STATUS_DELIVERED => 'Delivered',
                    ][$conversion->status].
                    ' / progress '.$conversion->progressPercentage.'%'.
                    ' / conversion time '.($conversion->conversionTime).'s'.
                    ' / estimated '.($conversion->estimatedRemainingTime).'s remaining'.
                    "\n";
            }
        );

    echo 'Conversion result files stored in '.$destinationDirectory."\n";

} catch (ApiException $e) {
    echo 'API Error: '.$e->getMessage()."\n";

}
