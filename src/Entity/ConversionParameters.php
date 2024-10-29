<?php

namespace TinCat\HdrjpgSdkPhp\Entity;

class ConversionParameters
{
    public int $width;
    public int $height;
    public bool $extrapolate;
    public int $quality;
    public int $baseQuality;
    public int $gainmapQuality;
    public string $onSdr;
}
