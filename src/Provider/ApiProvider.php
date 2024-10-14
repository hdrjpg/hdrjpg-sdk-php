<?php

namespace TinCat\HdrjpgSdkPhp\Provider;

use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7;
use TinCat\HdrjpgSdkPhp\Exception\ApiException;
use TinCat\HdrjpgSdkPhp\Exception\ApiClientException;
use TinCat\HdrjpgSdkPhp\Exception\ApiTimeoutException;
use TinCat\HdrjpgSdkPhp\Exception\ApiResponseException;
use TinCat\HdrjpgSdkPhp\Exception\ApiBadRequestException;
use TinCat\HdrjpgSdkPhp\Exception\ApiConnectionException;
use TinCat\HdrjpgSdkPhp\Exception\ApiUnauthorizedException;

class ApiProvider
{
    private string $apiKey;
    private string $hostIsHttps;
    private string $hostName;
    private int $hostPort;

    function __construct(
        $apiKey,
        $hostIsHttps,
        $hostName,
        $hostPort
    )
    {
        $this->apiKey = $apiKey;
        $this->hostIsHttps = $hostIsHttps;
        $this->hostName = $hostName;
        $this->hostPort = $hostPort;
    }

    private function getEndpointUrl($endpoint): string
    {
        return ($this->hostIsHttps ? 'https' : 'http').'://'.$this->hostName.(!in_array($this->hostPort, [80, 443]) ? ':'.$this->hostPort : null).'/'.$endpoint;
    }

    private function getRequestHeaders(): array
    {
        return
            [
                'api-key' => $this->apiKey
            ];
    }

    private function getRequestBaseOptions(): array
    {
        return
            [
                'headers' => $this->getRequestHeaders(),
                'allow_redirects' => false, // As a security measure in case the domain is ever hijacked
                'connect_timeout' => 10
            ];
    }

    public function query(
        Object $responseHandler,
        string $endpoint,
        string $method = 'GET',
        array $parameters = [],
        array $files = [],
        string $destinationFile = ''
    )
    {
        try {
            $client = new \GuzzleHttp\Client();

            $options = $this->getRequestBaseOptions();

            if ($files) {
                $method = 'POST';

                $options['multipart'] = [];

                foreach ($files as $parameterName => $filePath) {
                    $options['multipart'][] = [
                        'name' => $parameterName,
                        'filename' => basename($filePath),
                        'contents' => Psr7\Utils::tryFopen($filePath, 'r')
                    ];
                }

                if ($parameters) {
                    foreach ($parameters as $key => $value) {
                        $options['multipart'][] = [
                            'name' => $key,
                            'contents' => $value
                        ];
                    }
                }

            } else {
                $options['query'] = $parameters;
            }

            if ($destinationFile) {
                $options['sink'] = $destinationFile;
            }

            $response = $client->request(
                $method,
                $this->getEndpointUrl($endpoint),
                $options
            );


        } catch (BadResponseException $e) {
            $responseData = json_decode($e->getResponse()->getBody()->getContents(), true);
            switch ($e->getCode()) {
                case 401:
                    throw new ApiUnauthorizedException($responseData['errorDescription']);
                case 400:
                    throw new ApiBadRequestException($responseData['errorDescription']);
                case 408:
                    throw new ApiTimeoutException($responseData['errorDescription']);
                case 500:
                    throw new ApiException($responseData['errorDescription']);
            }

        } catch (Exception $e) {
            throw new ApiConnectionException(get_class($e).': '.$this->getEndpointUrl($endpoint).' ('.$e->getMessage().')');
        }

        try {

            return $responseHandler->buildFromResponse($response);

        } catch (Exception $e) {
            throw new ApiResponseException('Could not retrieve API response ('.$e->getMessage().')');
        }
    }
}
