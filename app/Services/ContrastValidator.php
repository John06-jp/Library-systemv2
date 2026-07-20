<?php

namespace App\Services;

use InvalidArgumentException;

class ContrastValidator
{
    public function relativeLuminance(string $hex): float
    {
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            throw new InvalidArgumentException('Color must be a six-digit hexadecimal value.');
        }

        $channels = array_map(
            static fn (string $channel): float => hexdec($channel) / 255,
            str_split(substr($hex, 1), 2),
        );

        $channels = array_map(
            static fn (float $channel): float => $channel <= 0.04045
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4,
            $channels,
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    public function ratio(string $foreground, string $background): float
    {
        $lighter = max($this->relativeLuminance($foreground), $this->relativeLuminance($background));
        $darker = min($this->relativeLuminance($foreground), $this->relativeLuminance($background));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    public function passesAA(string $foreground, string $background, bool $largeText = false): bool
    {
        return $this->ratio($foreground, $background) >= ($largeText ? 3.0 : 4.5);
    }
}
