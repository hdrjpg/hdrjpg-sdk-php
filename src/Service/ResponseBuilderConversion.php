<?php

namespace TinCat\HdrjpgSdkPhp\Service;

use Exception;
use GuzzleHttp\Psr7\Response;
use TinCat\HdrjpgSdkPhp\Entity\Conversion;
use TinCat\HdrjpgSdkPhp\Exception\ApiResponseException;

class ResponseBuilderConversion extends ResponseBuilder
{
    public function buildFromData(array $data)
    {
        try {

            $conversion = new Conversion;
            $conversion->uuid = $data['uuid'];
            $conversion->status =
                [
                    'Pending' => Conversion::STATUS_PENDING,
                    'Running' => Conversion::STATUS_RUNNING,
                    'Failed' => Conversion::STATUS_FAILED,
                    'Ready' => Conversion::STATUS_READY,
                    'Delivered' => Conversion::STATUS_DELIVERED,
                    'Cancelled' => Conversion::STATUS_CANCELLED,
                    'Deleted' => Conversion::STATUS_DELETED,
                ][$data['status']];
            $conversion->step =
                ([
                    'Priming' => Conversion::STEP_PRIMING,
                    'Preparing' => Conversion::STEP_PREPARING,
                    'Converting' => Conversion::STEP_CONVERTING,
                    'Finalizing' => Conversion::STEP_FINALIZING
                ][$data['step']] ?? null);
            $conversion->createdDate = strtotime($data['createdDate'] ?: 0);
            $conversion->failedDate = strtotime($data['failedDate'] ?: 0);
            $conversion->failDescriptions = $data['failDescriptions'] ?: [];
            $conversion->developerFailDescriptions = $data['developerFailDescriptions'] ?? [];
            $conversion->completedDate = strtotime($data['completedDate'] ?: 0);
            $conversion->deliveredDate = strtotime($data['deliveredDate'] ?: 0);
            $conversion->deliveryExpirationDate = strtotime($data['deliveryExpirationDate'] ?: 0);
            $conversion->sourceImageFileName = $data['sourceImageFileName'];
            $conversion->conversionTime = floatval($data['conversionTime'] ?? 0);
            $conversion->progressPercentage = floatval($data['progressPercentage'] ?? 0);
            $conversion->estimatedRemainingTime = intval($data['estimatedRemainingTime'] ?? 0);
            $conversion->blurHash = $data['blurHash'] ?: null;
            $conversion->averageColor = $data['averageColor'] ?: null;

            if ($data['variants']) {
                foreach ($data['variants'] as $variantData) {
                    $conversion->variants[] = (new ResponseBuilderConversionFile)->buildFromData($variantData);
                }
            }

            return $conversion;

        } catch (Exception $e) {
            throw new ApiResponseException('Error parsing API response ('.$e->getMessage().')');
        }
    }
}
