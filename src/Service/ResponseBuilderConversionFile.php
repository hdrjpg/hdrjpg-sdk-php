<?php

namespace TinCat\HdrjpgSdkPhp\Service;

use Exception;
use TinCat\HdrjpgSdkPhp\Entity\ConversionFile;
use TinCat\HdrjpgSdkPhp\Exception\ApiResponseException;

class ResponseBuilderConversionFile extends ResponseBuilder
{
    public function buildFromData($data)
    {
        try {

            $conversionFile = new ConversionFile;
            $conversionFile->uuid = $data['uuid'];
            $conversionFile->status =
                [
                    'Pending' => ConversionFile::STATUS_PENDING,
                    'Prepared' => ConversionFile::STATUS_PREPARED,
                    'Running' => ConversionFile::STATUS_RUNNING,
                    'Skipped' => ConversionFile::STATUS_SKIPPED,
                    'Failed' => ConversionFile::STATUS_FAILED,
                    'Completed' => ConversionFile::STATUS_COMPLETED
                ][$data['status']];
            $conversionFile->createdDate = strtotime($data['createdDate'] ?: 0);
            $conversionFile->failedDate = strtotime($data['failedDate'] ?: 0);
            $conversionFile->failDescriptions = $data['failDescriptions'] ?: [];
            $conversionFile->warningDescriptions = $data['warningDescriptions'] ?: [];
            $conversionFile->developerFailDescriptions = $data['developerFailDescriptions'] ?? [];
            $conversionFile->completedDate = strtotime($data['completedDate'] ?: 0);
            $conversionFile->conversionTime = floatval($data['conversionTime']);
            $conversionFile->fileName = $data['fileName'];
            $conversionFile->format = $data['format'];
            $conversionFile->outputImageFileName = $data['outputImageFileName'] ?: [];
            $conversionFile->outputImageFileSize = intval($data['outputImageFileSize']);
            $conversionFile->outputImageWidth = intval($data['outputImageWidth']);
            $conversionFile->outputImageHeight = intval($data['outputImageHeight']);
            $conversionFile->outputImageMegapixels = floatval($data['outputImageMegapixels']);
            $conversionFile->outputImageAspectRatio = floatval($data['outputImageAspectRatio']);
            $conversionFile->outputImageCompressionRatio = floatval($data['outputImageCompressionRatio']);
            $conversionFile->conversionParameters = (new ResponseBuilderConversionParameters)->buildFromData($data['conversionParameters']);

            return $conversionFile;

        } catch (Exception $e) {
            throw new ApiResponseException('Error parsing API response ('.$e->getMessage().')');
        }
    }
}
