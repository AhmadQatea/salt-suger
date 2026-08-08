<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class QrCodeService
{
    public const FORMAT_SVG = 'svg';

    public const FORMAT_PNG = 'png';

    /**
     * Absolute public menu URL derived from the named route and APP_URL.
     */
    public function menuUrl(): string
    {
        return route('menu.index', absolute: true);
    }

    /**
     * Formats that can be generated in the current PHP environment.
     *
     * @return list<string>
     */
    public function supportedFormats(): array
    {
        $formats = [self::FORMAT_SVG];

        if ($this->supportsPng()) {
            $formats[] = self::FORMAT_PNG;
        }

        return $formats;
    }

    public function supportsPng(): bool
    {
        return extension_loaded('gd');
    }

    public function isSupportedFormat(string $format): bool
    {
        return in_array(strtolower($format), $this->supportedFormats(), true);
    }

    /**
     * Generate QR binary/string output for the public menu URL.
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public function generate(?string $format = self::FORMAT_SVG, ?string $data = null): string
    {
        $format = strtolower($format ?: self::FORMAT_SVG);
        $data ??= $this->menuUrl();

        if ($data === '' || ! filter_var($data, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Invalid QR payload.');
        }

        if (! $this->isSupportedFormat($format)) {
            throw new InvalidArgumentException("Unsupported QR format [{$format}].");
        }

        try {
            $result = (new Builder(
                writer: $this->writerFor($format),
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 480,
                margin: 24,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255),
            ))->build();

            $output = $result->getString();
        } catch (InvalidArgumentException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new RuntimeException('تعذر إنشاء رمز QR حالياً.', previous: $exception);
        }

        if ($output === '') {
            throw new RuntimeException('تعذر إنشاء رمز QR حالياً.');
        }

        return $output;
    }

    /**
     * Inline SVG markup safe for embedding in an admin Blade view.
     */
    public function svgMarkup(?string $data = null): string
    {
        return $this->generate(self::FORMAT_SVG, $data);
    }

    public function mimeType(string $format): string
    {
        return match (strtolower($format)) {
            self::FORMAT_PNG => 'image/png',
            self::FORMAT_SVG => 'image/svg+xml',
            default => throw new InvalidArgumentException("Unsupported QR format [{$format}]."),
        };
    }

    public function downloadFilename(string $format): string
    {
        $extension = strtolower($format) === self::FORMAT_PNG ? 'png' : 'svg';

        return 'salt-suger-menu-qr.'.$extension;
    }

    protected function writerFor(string $format): WriterInterface
    {
        return match ($format) {
            self::FORMAT_PNG => new PngWriter,
            self::FORMAT_SVG => new SvgWriter,
            default => throw new InvalidArgumentException("Unsupported QR format [{$format}]."),
        };
    }
}
