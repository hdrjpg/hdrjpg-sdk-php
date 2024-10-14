<?php

namespace TinCat\HdrjpgSdkPhp\Service;

use Exception;
use TinCat\HdrjpgSdkPhp\Exception\ApiResponseException;

class ResponseBuilderSingleConversion extends ResponseBuilder
{
    public function buildFromData($data)
    {
        if (!$data['conversions']) {
            throw new ApiResponseException('conversion key not found in API response data');
        }

        try {

            $conversionData = current($data['conversions']);
            return (new ResponseBuilderConversion)->buildFromData($conversionData);

        } catch (Exception $e) {
            throw new ApiResponseException('Error parsing API response ('.$e->getMessage().')');
        }
    }
}
