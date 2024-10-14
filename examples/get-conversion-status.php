<?php

/**
 * Example on how to get the status of a conversion
 */

require_once '../vendor/autoload.php';

use TinCat\HdrjpgSdkPhp\Client;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;

$client = new Client('<your API key>');

try {

    $conversion = $client->getConversion('<your conversion UUID>');

    echo
        "Conversion ".$conversion->uuid." retrieved succesfully.\n".
        "Created on ".date('r', $conversion->createdDate)."\n".
        ($conversion->status === Conversion::STATUS_READY || $conversion->status === Conversion::STATUS_DELIVERED ?
            "Ready to be downloaded."
            : "Not yet ready to be downloaded."
        ).
        "\n";

} catch (ApiException $e) {
    echo 'API Error: '.$e->getMessage()."\n";

}
