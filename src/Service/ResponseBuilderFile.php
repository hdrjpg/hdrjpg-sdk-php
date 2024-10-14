<?php

namespace TinCat\HdrjpgSdkPhp\Service;

use Exception;
use GuzzleHttp\Psr7\Response;
use TinCat\HdrjpgSdkPhp\Exception\ApiResponseException;

class ResponseBuilderFile extends ResponseBuilder
{
    public function buildFromResponse(Response $response)
    {
        try {

            list(, $fileName) = explode('filename=', $response->getHeader('Content-Disposition')[0]);

            return $fileName;

        } catch (Exception $e) {
            throw new ApiResponseException('Error parsing API response ('.$e->getMessage().')');
        }
    }
}
