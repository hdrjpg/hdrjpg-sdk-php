<?php

/**
 * Example on how to use the advanced conversion method that performs the three conversion steps manually in order to have finer control on the conersion process.
 */

require_once '../vendor/autoload.php';

use TinCat\HdrjpgSdkPhp\Client;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Entity\ConversionFile;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;

$client = new Client('<your API key>');

try {

    echo 'Submitting file for conversion ...'."\n";

    // First step: Send the file for conversion
    $conversion =
        $client->submit(
            'example.heic',
            [
                [
                    'width' => 1200,
                    'quality' => 97
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'gainmapQuality' => 80
                ]
            ],
            [
                ConversionFile::OUTPUT_FORMAT_JPEG,
                ConversionFile::OUTPUT_FORMAT_JXL,
                ConversionFile::OUTPUT_FORMAT_AVIF
            ]
        );

    echo 'File '.$conversion->sourceImageFileName.' Submitted for conversion with UUID '.$conversion->uuid."\n";

    // Second step: Poll the API continuously and wait until the conversion is ready
    while (true) {

        $conversion = $client->getConversion($conversion->uuid);

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

        if ($conversion->status === Conversion::STATUS_READY) {
            break;
        }

        sleep(2);
    }

    // Third step: Download the resulting file
    $destinationDirectory = './download';

    if (!file_exists($destinationDirectory)) {
        mkdir($destinationDirectory, 0777, true);
    }

    $filePath = $client->downloadConversionResultZip($conversion->uuid, $destinationDirectory); // Use the `downloadConversionResultFiles` method instead to store unzipped files instead.

    echo 'ZIP file downloaded to '.$filePath."\n";

} catch (ApiException $e) {
    echo 'API Error: '.$e->getMessage()."\n";

}
