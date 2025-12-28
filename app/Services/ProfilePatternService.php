<?php

namespace App\Services;

class ProfilePatternService
{
    /**
     * Curated color palettes that work well on dark backgrounds.
     * Each palette has primary, secondary, and tertiary colors.
     */
    private const COLOR_PALETTES = [
        // Blues (accent family)
        ['#3b82f6', '#1d4ed8', '#60a5fa'],
        ['#2563eb', '#1e40af', '#93c5fd'],

        // Purples
        ['#8b5cf6', '#6d28d9', '#a78bfa'],
        ['#a855f7', '#7c3aed', '#c4b5fd'],

        // Teals
        ['#14b8a6', '#0d9488', '#5eead4'],
        ['#06b6d4', '#0891b2', '#67e8f9'],

        // Emeralds
        ['#10b981', '#059669', '#6ee7b7'],
        ['#22c55e', '#16a34a', '#86efac'],

        // Warm tones
        ['#f59e0b', '#d97706', '#fcd34d'],
        ['#f97316', '#ea580c', '#fdba74'],

        // Rose/Pink
        ['#ec4899', '#db2777', '#f9a8d4'],
        ['#f43f5e', '#e11d48', '#fda4af'],
    ];

    /**
     * Generate a unique SVG pattern based on the username.
     */
    public function generateSvg(string $username): string
    {
        $data = $this->generatePatternData($username);

        return $this->buildSvg($data);
    }

    /**
     * Generate pattern parameters from username hash.
     */
    private function generatePatternData(string $username): array
    {
        $hash = md5(strtolower(trim($username)));
        $values = $this->hashToValues($hash);

        // Select color palette
        $paletteIndex = (int) floor($values[0] * count(self::COLOR_PALETTES));
        $paletteIndex = min($paletteIndex, count(self::COLOR_PALETTES) - 1);
        $colors = self::COLOR_PALETTES[$paletteIndex];

        return [
            'id' => substr($hash, 0, 8),
            'primary' => $colors[0],
            'secondary' => $colors[1],
            'tertiary' => $colors[2],
            'gradientAngle' => (int) floor($values[1] * 4) * 45,
            'blob1' => [
                'cx' => 10 + ($values[2] * 30),
                'cy' => 20 + ($values[3] * 60),
                'rx' => 80 + ($values[4] * 120),
                'ry' => 60 + ($values[5] * 80),
                'rotation' => $values[6] * 45,
            ],
            'blob2' => [
                'cx' => 50 + ($values[7] * 40),
                'cy' => 30 + ($values[8] * 50),
                'rx' => 60 + ($values[9] * 100),
                'ry' => 50 + ($values[10] * 70),
                'rotation' => -15 + ($values[11] * 60),
            ],
            'blob3' => [
                'cx' => 70 + ($values[12] * 25),
                'cy' => 10 + ($values[13] * 40),
                'rx' => 40 + ($values[14] * 60),
                'ry' => 30 + ($values[15] * 50),
                'rotation' => $values[0] * 30,
            ],
        ];
    }

    /**
     * Convert hash string to array of normalized values (0-1).
     */
    private function hashToValues(string $hash): array
    {
        $segments = str_split($hash, 2);

        return array_map(fn($hex) => hexdec($hex) / 255, $segments);
    }

    /**
     * Build the SVG string from pattern data.
     */
    private function buildSvg(array $data): string
    {
        $id = $data['id'];
        $angle = $data['gradientAngle'];

        // Calculate gradient coordinates based on angle
        $gradientCoords = $this->angleToGradientCoords($angle);

        $svg = <<<SVG
<svg viewBox="0 0 400 128" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
    <defs>
        <filter id="blur-{$id}" x="-50%" y="-50%" width="200%" height="200%">
            <feGaussianBlur in="SourceGraphic" stdDeviation="25" />
        </filter>
        <linearGradient id="bg-{$id}" x1="{$gradientCoords['x1']}%" y1="{$gradientCoords['y1']}%" x2="{$gradientCoords['x2']}%" y2="{$gradientCoords['y2']}%">
            <stop offset="0%" stop-color="{$data['primary']}" />
            <stop offset="50%" stop-color="{$data['secondary']}" />
            <stop offset="100%" stop-color="{$data['primary']}" />
        </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#bg-{$id})" />
SVG;

        // Add blobs
        $svg .= $this->buildBlob($data['blob1'], $data['tertiary'], 0.4, $id);
        $svg .= $this->buildBlob($data['blob2'], $data['secondary'], 0.5, $id);
        $svg .= $this->buildBlob($data['blob3'], $data['primary'], 0.6, $id);

        $svg .= "\n</svg>";

        return $svg;
    }

    /**
     * Build a single blob ellipse element.
     */
    private function buildBlob(array $blob, string $color, float $opacity, string $id): string
    {
        $cx = round($blob['cx'], 1);
        $cy = round($blob['cy'], 1);
        $rx = round($blob['rx'], 1);
        $ry = round($blob['ry'], 1);
        $rotation = round($blob['rotation'], 1);

        // Convert percentage to actual coordinates for rotation center
        $centerX = round($cx * 4, 1);
        $centerY = round($cy * 1.28, 1);

        return <<<SVG

    <ellipse
        cx="{$cx}%"
        cy="{$cy}%"
        rx="{$rx}"
        ry="{$ry}"
        fill="{$color}"
        opacity="{$opacity}"
        filter="url(#blur-{$id})"
        transform="rotate({$rotation} {$centerX} {$centerY})"
    />
SVG;
    }

    /**
     * Convert angle to gradient start/end coordinates.
     */
    private function angleToGradientCoords(int $angle): array
    {
        return match ($angle) {
            0 => ['x1' => 0, 'y1' => 50, 'x2' => 100, 'y2' => 50],    // Left to right
            45 => ['x1' => 0, 'y1' => 0, 'x2' => 100, 'y2' => 100],   // Top-left to bottom-right
            90 => ['x1' => 50, 'y1' => 0, 'x2' => 50, 'y2' => 100],   // Top to bottom
            135 => ['x1' => 100, 'y1' => 0, 'x2' => 0, 'y2' => 100],  // Top-right to bottom-left
            default => ['x1' => 0, 'y1' => 0, 'x2' => 100, 'y2' => 100],
        };
    }
}
