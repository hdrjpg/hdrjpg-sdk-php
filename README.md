# hdrjpg-sdk-php
The official PHP SDK to convert high dynamic range photos into web-ready HDR files using the [hdrjpg.com](https://hdrjpg.com) API.

## Install

```bash
composer require hdrjpg/hdrjpg-sdk-php
```

## Usage
Each conversion allows for the creation of a custom combination of image sizes, qualities and formats. The supported source HDR image formats are <b>HEIC</b> (Apple), <b>JPG</b> (Android Ultra HDR and other gain map JPEGs) and <b>EXR</b>. Supported HDR output formats are <b>JPEG-XT</b>, <b>JPEG-XL</b>, <b>AVIF</b>, <b>MP4</b> (H.264) and <b>MP4 HEVC</b> (format code `mp4-hevc`). All files retain HDR information after the conversion.

`mp4-hevc` is a 10-bit HEVC (H.265) HDR10 video. It has smoother gradients and more accurate color than `mp4`, but not every browser can play HEVC. Its files end in `.hevc.mp4`, for example `photo_1800xq95.hevc.mp4`. Request both `mp4` and `mp4-hevc` so every browser gets a video it can play.

A free account allows up to 6 variants per conversion. A paid account allows up to 15. See [hdrjpg.com](https://hdrjpg.com) for more information on compatibility and conversion options.

```php
use TinCat\HdrjpgSdkPhp\Client;
$client = new Client('YOUR_API_KEY'); // Replace with your API Key
$client->convert(
    'example.heic', // Complete path for your Source HDR image file
    [ // An array of variants
        [ // Each variant defines the settings for each resulting image
            'width' => 1200,
            'baseQuality' => 98,
            'format' => 'jpeg-xt'
        ],
        [
            'width' => 300,
            'baseQuality' => 98,
            'gainmapQuality' => 95,
            'format' => 'jpeg-xt'
        ],
        [
            'width' => 1200,
            'baseQuality' => 98,
            'format' => 'jpeg-xl'
        ],
        [
            'height' => 300,
            'baseQuality' => 98,
            'fileName' => 'image-converted',
            'format' => 'avif'
        ],
        [
            'height' => 300,
            'baseQuality' => 98,
            'format' => 'mp4'
        ],
        [
            'height' => 300,
            'baseQuality' => 98,
            'format' => 'mp4-hevc'
        ]
    ],
    '/tmp' // Output directory to save resulting files to
);
```

> This method will wait for the conversion to be completed, and can take up to a few minutes depending on the size of the source image and the variants and formats requested.

## Advanced usage
Conversion is internally a three steps process that can be leveraged individually to better suit your implementation workflow. If you need finer control over this process, see the file [examples/advanced-convert.php](examples/advanced-convert.php) for an example on how to perform the conversion steps separately.

## API key
[Create a free HDRJPG account](https://hdrjpg.com/signup) to get your API key. The free plan includes 300 conversions a month, with a watermark; see [pricing](https://hdrjpg.com/pricing) for the paid plan.

## Requirements
- PHP 7.4 or higher
