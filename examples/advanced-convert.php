<?php

/**
 * Example on how to use the advanced conversion method that performs the three conversion steps manually in order to have finer control on the conersion process.
 */

require_once '../vendor/autoload.php';

use TinCat\HdrjpgSdkPhp\Client;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;

$client = new Client('iS1nrfLP7KSCkeVlb1hKUL39eyUHsjE+KnLwmh6j'); // Replace with your API Key

try {

    echo 'Submitting file for conversion ...'."\n";

    // First step: Send the file for conversion
    $conversion =
        $client->submit(
            'example.heic',
            [
                [
                    'width' => 1200,
                    'baseQuality' => 97,
                    'gainmapQuality' => 80,
                    'format' => 'jpeg-xt',
                    'onSdr' => 'continue' // This allows the file to still be generated if the provided image is not HDR. Resulting file won't be HDR.
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'format' => 'jpeg-xl',
                    'onSdr' => 'continue'
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'format' => 'avif',
                    'onSdr' => 'continue'
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'format' => 'jpeg', // For convenience, you can also convert to non-HDR standard JPG format
                    'onSdr' => 'continue'
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'format' => 'jpeg',
                    'onSdr' => 'continue'
                ]
            ],
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
            ' / Step: '.
            ([
                Conversion::STEP_PRIMING => 'Priming',
                Conversion::STEP_PREPARING => 'Preparing',
                Conversion::STEP_CONVERTING => 'Converting',
                Conversion::STEP_FINALIZING => 'Finalizing'
            ][$conversion->step] ?? '-').
            ' / progress '.$conversion->progressPercentage.'%'.
            ' / conversion time '.($conversion->conversionTime).'s'.
            "\n";

        if (
            $conversion->status === Conversion::STATUS_READY
            ||
            $conversion->status === Conversion::STATUS_FAILED
        ) {
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
