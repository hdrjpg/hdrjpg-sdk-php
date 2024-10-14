<?php

/**
 * Example on how to download the resulting files of a conversion
 */

require_once '../vendor/autoload.php';

use TinCat\HdrjpgSdkPhp\Client;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;

$client = new Client('<your API key>');

try {

    $destinationDirectory = './download';

    if (!file_exists($destinationDirectory)) {
        mkdir($destinationDirectory, 0777, true);
    }

    $filePath = $client->downloadConversionResultZip('<your conversion UUID>', $destinationDirectory);

    echo 'Conversion results file downloaded to '.$filePath."\n";

} catch (ApiException $e) {
    echo 'API Error: '.$e->getMessage()."\n";
}
