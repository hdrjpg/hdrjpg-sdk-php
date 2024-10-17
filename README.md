# hdrjpg-sdk-php
The official PHP SDK to convert high dynamic range photos into web-ready HDR files using the [hdrjpg.com](https://hdrjpg.com) API.

## Install

```bash
composer require tin-cat/hdrjpg-sdk-php
```

## Usage
Each conversion allows for the creation of a custom combination of image sizes, qualities and formats. The supported source HDR image formats are <b>HEIC</b> and <b>EXR</b>. Supported HDR output formats are <b>JPEG-XT</b>, <b>JPEG-XL</b> and <b>AVIF</b>. All files retain HDR information after the conversion. See [hdrjpg.com](https://hdrjpg.com) for more information on compatibility and conversion options.

```php
use TinCat\HdrjpgSdkPhp\Client;
$client = new Client('iS1nrfLP7KSCkeVlb1hKUL39eyUHsjE+KnLwmh6j'); // Replace with your API Key
$client->convert(
    'image.heic', // Complete path for your Source HDR image file
    [ // An array of variants
        [ // Each variant defines the desired size and quality of the resulting image
            'width' => 1200,
            'baseQuality' => 97,
            'format' => 'jpeg-xt'
        ],
        [
            'width' => 1200,
            'baseQuality' => 97,
            'format' => 'jpeg-xl'
        ],
        [
            'height' => 300,
            'baseQuality' => 95,
            'gainmapQuality' => 80,
            'fileName' => 'image-converted',
            'format' => 'avif'
        ]
    ],
    '/tmp' // Output directory to save resulting files to
);
```

> This method will wait for the conversion to be completed, and can take up to a few minutes depending on the size of the source image and the variants and formats requested.

## Advanced usage
Conversion is internally a three steps process that can be leveraged individually to better suit your implementation workflow. If you need finer control over this process, see the file [examples/advanced-convert.php](examples/advanced-convert.php) for an example on how to perform the conversion steps separately.

## Free testing API Key
Visit [hdrjpg.com/documentation/free-api-key](https://hdrjpg.com/documentation/free-api-key) to get a free testing API Key.

## Requirements
- PHP 7.4 or higher
