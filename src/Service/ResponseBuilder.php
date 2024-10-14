<?php

namespace TinCat\HdrjpgSdkPhp\Service;
use GuzzleHttp\Psr7\Response;

abstract class ResponseBuilder
{
    public function buildFromResponse(Response $response)
    {
        return $this->buildFromData(json_decode($response->getBody(), true));
    }

    public function buildFromData(array $data) {}
}
