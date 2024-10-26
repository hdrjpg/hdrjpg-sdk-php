<?php

/**
 * Example on how to perform an HDR image conversion using the simplified method
 */

require_once '../vendor/autoload.php';

use TinCat\HdrjpgSdkPhp\Client;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Entity\ConversionFile;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;

$client = new Client('iS1nrfLP7KSCkeVlb1hKUL39eyUHsjE+KnLwmh6j');

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
                    'baseQuality' => 97,
                    'format' => 'jpeg-xt'
                ],
                [
                    'width' => 1200,
                    'baseQuality' => 97,
                    'format' => 'jpeg-xl'
                ],
                [
                    'height' => 300,
                    'baseQuality' => 95,
                    'gainmapQuality' => 80,
                    'fileName' => 'image-converted',
                    'format' => 'avif'
                ]
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
            }
        );

    echo 'Conversion result files stored in '.$destinationDirectory."\n";

} catch (ApiException $e) {
    echo 'API Error: '.$e->getMessage()."\n";

}
