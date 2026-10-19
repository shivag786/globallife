<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Re-encodes an uploaded KYC image before it is stored.
 *
 * Re-drawing the pixels onto a fresh canvas and saving that drops everything
 * else the original carried -- above all the GPS location a phone photo embeds
 * in its EXIF data -- and gives every stored file a predictable size and type.
 *
 * Needs GD. If the server has no GD (or no WebP support) the file is still
 * accepted, in the best format available, rather than failing the upload.
 */
final class KycImage
{
    /** Passport photo: 35 x 45 mm, the standard ID-card ratio. */
    public const PHOTO_RATIO = 35 / 45;

    public const PHOTO_WIDTH = 700;

    /** Longest side for document scans: legible, without a 12 MB phone photo. */
    public const DOCUMENT_MAX = 2000;

    /**
     * @return array{bytes: string, extension: string}
     */
    public static function passportPhoto(UploadedFile $file): array
    {
        return self::process($file, function ($image) {
            $w = imagesx($image);
            $h = imagesy($image);

            // Centre-crop to 35:45.
            if ($w / $h > self::PHOTO_RATIO) {
                $cropW = (int) round($h * self::PHOTO_RATIO);
                $cropH = $h;
                $x = (int) floor(($w - $cropW) / 2);
                $y = 0;
            } else {
                $cropW = $w;
                $cropH = (int) round($w / self::PHOTO_RATIO);
                $x = 0;
                // Faces sit in the upper part of a portrait; bias the crop up.
                $y = (int) floor(($h - $cropH) / 3);
            }

            $outW = min(self::PHOTO_WIDTH, $cropW);
            $outH = (int) round($outW / self::PHOTO_RATIO);

            $out = imagecreatetruecolor($outW, $outH);
            imagecopyresampled($out, $image, 0, 0, $x, $y, $outW, $outH, $cropW, $cropH);

            return $out;
        });
    }

    /**
     * @return array{bytes: string, extension: string}
     */
    public static function document(UploadedFile $file): array
    {
        return self::process($file, function ($image) {
            $w = imagesx($image);
            $h = imagesy($image);
            $scale = min(1, self::DOCUMENT_MAX / max($w, $h));

            $outW = (int) round($w * $scale);
            $outH = (int) round($h * $scale);

            $out = imagecreatetruecolor($outW, $outH);
            // White behind transparent PNG areas, so a scan never turns black.
            imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
            imagecopyresampled($out, $image, 0, 0, 0, 0, $outW, $outH, $w, $h);

            return $out;
        });
    }

    /**
     * @param  callable(\GdImage): \GdImage  $transform
     * @return array{bytes: string, extension: string}
     */
    private static function process(UploadedFile $file, callable $transform): array
    {
        $original = (string) file_get_contents($file->getRealPath());

        if (! function_exists('imagecreatefromstring')) {
            return ['bytes' => $original, 'extension' => $file->guessExtension() ?: 'jpg'];
        }

        $image = @imagecreatefromstring($original);

        if ($image === false) {
            return ['bytes' => $original, 'extension' => $file->guessExtension() ?: 'jpg'];
        }

        $image = self::applyOrientation($image, $file);
        $out = $transform($image);

        ob_start();

        if (function_exists('imagewebp')) {
            imagewebp($out, null, 85);
            $extension = 'webp';
        } else {
            imagejpeg($out, null, 88);
            $extension = 'jpg';
        }

        return ['bytes' => (string) ob_get_clean(), 'extension' => $extension];
    }

    /**
     * Phones store "rotate me" in EXIF rather than rotating the pixels. Apply it
     * now, because the EXIF that carried it is about to be discarded.
     */
    private static function applyOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
