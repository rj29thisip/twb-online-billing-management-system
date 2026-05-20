<?php

namespace App\Services;

/**
 * Generates base64 PNG images using PHP GD (built-in PHP extension).
 * - logoBase64()  → TWB Water Board circular logo
 * - chartBase64() → Line chart of historical water readings
 */
class InvoiceImageGenerator
{
    /**
     * TWB Water Board logo as base64 PNG.
     */
    public static function logoBase64(int $size = 120): string
    {
        $img = imagecreatetruecolor($size, $size);

        // White background
        $white = imagecolorallocate($img, 255, 255, 255);
        $navy  = imagecolorallocate($img, 12,  61,  122);
        $navyM = imagecolorallocate($img, 30,  80,  150);

        imagefill($img, 0, 0, $white);

        $cx = (int)($size / 2);
        $cy = (int)($size / 2);
        $r  = (int)($size * 0.46);
        $r2 = (int)($size * 0.38);

        // Outer filled circle (white) with navy border
        imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $white);
        // Draw thick outer ring (3 px)
        for ($t = 0; $t < 3; $t++) {
            imageellipse($img, $cx, $cy, ($r - $t) * 2, ($r - $t) * 2, $navy);
        }
        // Inner ring (1 px)
        imageellipse($img, $cx, $cy, $r2 * 2, $r2 * 2, $navy);

        // Water wave
        $waveY   = $cy + (int)($size * 0.06);
        $waveAmp = (int)($size * 0.055);
        $steps   = $r2 * 2;
        $startX  = $cx - $r2;
        for ($xi = 0; $xi < $steps - 1; $xi++) {
            $x1 = $startX + $xi;
            $x2 = $startX + $xi + 1;
            $y1 = (int)($waveY + sin($xi / $steps * M_PI * 2) * $waveAmp);
            $y2 = (int)($waveY + sin(($xi + 1) / $steps * M_PI * 2) * $waveAmp);
            imageline($img, $x1, $y1,     $x2, $y2,     $navy);
            imageline($img, $x1, $y1 + 1, $x2, $y2 + 1, $navyM);
        }

        // Text labels (GD built-in font, no TTF)
        $f = 3; // font size 1-5
        $fw = imagefontwidth($f);
        $fh = imagefontheight($f);

        $labels = ['TONGA', 'WATER', 'BOARD'];
        $labelYs = [
            $cy - (int)($size * 0.22) - (int)($fh / 2),
            $cy + (int)($size * 0.18) - (int)($fh / 2),
            $cy + (int)($size * 0.30) - (int)($fh / 2),
        ];
        foreach ($labels as $i => $lbl) {
            $lw = strlen($lbl) * $fw;
            $lx = $cx - (int)($lw / 2);
            imagestring($img, $f, $lx, $labelYs[$i], $lbl, $navy);
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return base64_encode($data);
    }

    /**
     * Line chart PNG for historical water readings.
     *
     * @param  array  $history   [[label_string, float_value], ...]
     *                           value is in thousands of liters ('000)
     * @param  int    $w         image width in pixels
     * @param  int    $h         image height in pixels
     */
    public static function chartBase64(array $history, int $w = 400, int $h = 220): string
    {
        $img = imagecreatetruecolor($w, $h);

        $white  = imagecolorallocate($img, 255, 255, 255);
        $black  = imagecolorallocate($img, 20,  20,  20);
        $gray   = imagecolorallocate($img, 200, 200, 200);
        $dkgray = imagecolorallocate($img, 90,  90,  90);
        $navy   = imagecolorallocate($img, 12,  61,  122);

        imagefill($img, 0, 0, $white);

        // Padding
        $padL = 30;
        $padR = 8;
        $padT = 8;
        $padB = 35;   // room for X labels

        $plotW = $w - $padL - $padR;
        $plotH = $h - $padT - $padB;

        $yMax   = 175;
        $yTicks = [0, 25, 50, 75, 100, 125, 150, 175];

        $fSmall = 1;   // tiny GD font for numbers
        $fw     = imagefontwidth($fSmall);
        $fh     = imagefontheight($fSmall);

        // ── Y-axis grid + labels ──────────────────────────────────────────────
        foreach ($yTicks as $tv) {
            $yp = (int)($padT + $plotH - ($tv / $yMax) * $plotH);
            // Grid line
            imageline($img, $padL, $yp, $padL + $plotW, $yp, $gray);
            // Label right-aligned before axis
            $lbl = (string)$tv;
            $lx  = $padL - strlen($lbl) * $fw - 2;
            imagestring($img, $fSmall, max(0, $lx), $yp - (int)($fh / 2), $lbl, $dkgray);
        }

        // ── Axes ──────────────────────────────────────────────────────────────
        // Y-axis
        imageline($img, $padL, $padT, $padL, $padT + $plotH, $black);
        // Y-axis thick (2px)
        imageline($img, $padL + 1, $padT, $padL + 1, $padT + $plotH, $black);
        // X-axis
        imageline($img, $padL, $padT + $plotH, $padL + $plotW, $padT + $plotH, $black);
        imageline($img, $padL, $padT + $plotH + 1, $padL + $plotW, $padT + $plotH + 1, $black);

        // ── Y-axis title (vertical, character by character) ───────────────────
        $yTitle = "Units Used ('000)";
        $titleX = 1;
        $titleStartY = (int)($padT + $plotH / 2 - strlen($yTitle) * $fh / 2);
        for ($i = 0; $i < strlen($yTitle); $i++) {
            imagestring($img, $fSmall, $titleX, $titleStartY + $i * $fh, $yTitle[$i], $dkgray);
        }

        // ── Data points ───────────────────────────────────────────────────────
        $n = count($history);

        if ($n === 0) {
            // No data — draw "No data" text
            imagestring($img, 2, (int)($padL + $plotW / 2 - 20), (int)($padT + $plotH / 2), 'No data', $dkgray);
            ob_start();
            imagepng($img);
            $data = ob_get_clean();
            imagedestroy($img);
            return base64_encode($data);
        }

        // Calculate pixel coordinates for each data point
        $pts = [];
        for ($i = 0; $i < $n; $i++) {
            [$label, $val] = $history[$i];
            $val = (float)$val;

            // X: spread evenly across plot width
            if ($n === 1) {
                $px = (int)($padL + $plotW / 2);
            } else {
                $px = (int)($padL + ($i / ($n - 1)) * $plotW);
            }

            // Y: 0 at bottom of plot, yMax at top
            $clampedVal = min(max($val, 0), $yMax);
            $py = (int)($padT + $plotH - ($clampedVal / $yMax) * $plotH);

            $pts[] = ['x' => $px, 'y' => $py, 'label' => (string)$label, 'val' => $val];
        }

        // Draw connecting line segments (thick — 2px)
        for ($i = 0; $i < count($pts) - 1; $i++) {
            $x1 = $pts[$i]['x'];
            $y1 = $pts[$i]['y'];
            $x2 = $pts[$i + 1]['x'];
            $y2 = $pts[$i + 1]['y'];

            // Draw 2px thick line
            imageline($img, $x1,     $y1,     $x2,     $y2,     $black);
            imageline($img, $x1,     $y1 + 1, $x2,     $y2 + 1, $black);
            imageline($img, $x1 + 1, $y1,     $x2 + 1, $y2,     $dkgray);
        }

        // Draw filled circles at each data point + X labels
        foreach ($pts as $pt) {
            $px = $pt['x'];
            $py = $pt['y'];
            $lbl = $pt['label'];

            // Filled dot (radius 4)
            imagefilledellipse($img, $px, $py, 8, 8, $black);
            // White center highlight
            imagefilledellipse($img, $px, $py, 3, 3, $white);

            // X-axis label — rotated diagonally (simulate by offsetting chars)
            $lblLen = strlen($lbl);
            $charW  = imagefontwidth($fSmall);
            $charH  = imagefontheight($fSmall);
            $baseX  = $px - (int)($lblLen * $charW / 2);
            $baseY  = $padT + $plotH + 4;

            // Draw each character shifted diagonally
            for ($ci = 0; $ci < $lblLen; $ci++) {
                $cx2 = $baseX + $ci * $charW + (int)($ci * 0.6);
                $cy2 = $baseY + (int)($ci * 1.2);
                imagestring($img, $fSmall, $cx2, $cy2, $lbl[$ci], $dkgray);
            }
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return base64_encode($data);
    }

    /**
     * Generate chart with fallback/dummy data for testing when no readings exist.
     */
    public static function chartBase64WithFallback(array $history, int $w = 400, int $h = 220): string
    {
        if (empty($history)) {
            $history = [
                ['--', 0], ['--', 0], ['--', 0],
                ['--', 0], ['--', 0], ['--', 0],
            ];
        }
        return static::chartBase64($history, $w, $h);
    }

    /**
     * Monthly LINE chart for invoice PDF.
     * $monthlyData = [['Jan 26', 12.5], ['Feb 26', 8.3], ...]  — value in m³ (thousands of liters)
     * Always 6 entries. Zero values are displayed as 0 on the line (flat at bottom).
     * If all values are zero (no history), still renders a flat line at 0.
     */
    public static function monthlyChartBase64(array $monthlyData, int $w = 400, int $h = 220): string
    {
        // Ensure exactly 6 entries, pad with zeros at the front if needed
        while (count($monthlyData) < 6) {
            array_unshift($monthlyData, ['—', 0]);
        }
        $monthlyData = array_slice($monthlyData, -6);

        $img = imagecreatetruecolor($w, $h);

        $white  = imagecolorallocate($img, 255, 255, 255);
        $black  = imagecolorallocate($img, 20,  20,  20);
        $gray   = imagecolorallocate($img, 200, 200, 200);
        $dkgray = imagecolorallocate($img, 90,  90,  90);
        $navy   = imagecolorallocate($img, 12,  61,  122);
        $dotFill = imagecolorallocate($img, 255, 255, 255);  // dot center (white)

        imagefill($img, 0, 0, $white);

        $padL = 34;
        $padR = 10;
        $padT = 14;
        $padB = 32;   // room for month labels below line

        $plotW = $w - $padL - $padR;
        $plotH = $h - $padT - $padB;

        // Y-axis max — round up to a nice number, default 10 if all zero
        $vals   = array_column($monthlyData, 1);
        $maxVal = max($vals);
        $yMax   = $maxVal > 0 ? ceil($maxVal / 5) * 5 : 10;
        $yMax   = max($yMax, 1);

        // Y-axis ticks (5 steps)
        $tickCount = 5;
        $tickStep  = ceil($yMax / $tickCount);
        $yTicks    = [];
        for ($t = 0; $t <= $tickCount; $t++) {
            $yTicks[] = $t * $tickStep;
        }
        $yMax = end($yTicks);

        $fSmall = 1;
        $fw     = imagefontwidth($fSmall);
        $fh     = imagefontheight($fSmall);

        // Grid lines + Y-axis labels
        foreach ($yTicks as $tv) {
            $yp = (int)($padT + $plotH - ($tv / $yMax) * $plotH);
            imageline($img, $padL, $yp, $padL + $plotW, $yp, $gray);
            $lbl = (string) $tv;
            $lx  = $padL - strlen($lbl) * $fw - 3;
            imagestring($img, $fSmall, max(0, $lx), $yp - (int)($fh / 2), $lbl, $dkgray);
        }

        // Axes
        imageline($img, $padL,     $padT,          $padL,             $padT + $plotH, $black);
        imageline($img, $padL + 1, $padT,          $padL + 1,         $padT + $plotH, $black);
        imageline($img, $padL,     $padT + $plotH, $padL + $plotW,    $padT + $plotH, $black);
        imageline($img, $padL,     $padT + $plotH + 1, $padL + $plotW, $padT + $plotH + 1, $black);

        // Y-axis title (vertical, character by character)
        $yTitle      = "Usage(m3)";
        $titleX      = 1;
        $titleStartY = (int)($padT + $plotH / 2 - strlen($yTitle) * $fh / 2);
        for ($ci = 0; $ci < strlen($yTitle); $ci++) {
            imagestring($img, $fSmall, $titleX, $titleStartY + $ci * $fh, $yTitle[$ci], $dkgray);
        }

        // Compute X positions — evenly spaced across plot width
        $n      = count($monthlyData);
        $points = [];
        for ($i = 0; $i < $n; $i++) {
            $val = (float) $monthlyData[$i][1];
            // X: evenly space points from padL+gap/2 to padL+plotW-gap/2
            $gap = $plotW / $n;
            $px  = (int)($padL + ($i + 0.5) * $gap);
            $py  = (int)($padT + $plotH - ($val / $yMax) * $plotH);
            $py  = max($padT + 2, min($padT + $plotH, $py)); // clamp
            $points[] = [$px, $py, $monthlyData[$i][0], $val];
        }

        // Draw line segments (thick: draw 3 parallel lines)
        for ($i = 0; $i < count($points) - 1; $i++) {
            [$x1, $y1] = $points[$i];
            [$x2, $y2] = $points[$i + 1];
            imageline($img, $x1, $y1 - 1, $x2, $y2 - 1, $navy);
            imageline($img, $x1, $y1,     $x2, $y2,     $navy);
            imageline($img, $x1, $y1 + 1, $x2, $y2 + 1, $navy);
        }

        // Draw dots + value labels + x-axis labels
        foreach ($points as [$px, $py, $label, $val]) {
            $r = 4;
            // Filled circle (navy)
            imagefilledellipse($img, $px, $py, $r * 2, $r * 2, $navy);
            // White inner dot
            imagefilledellipse($img, $px, $py, 2, 2, $dotFill);

            // Value label above dot (always show, even if 0)
            $valLbl = number_format((float)$val, 1);
            $vlw    = strlen($valLbl) * $fw;
            $vlx    = $px - (int)($vlw / 2);
            $vly    = max($padT, $py - $r - $fh - 1);
            imagestring($img, $fSmall, $vlx, $vly, $valLbl, $navy);

            // X-axis month label (centered under point)
            $lblW = strlen($label) * $fw;
            $lx2  = $px - (int)($lblW / 2);
            $ly2  = $padT + $plotH + 4;
            imagestring($img, $fSmall, $lx2, $ly2, $label, $dkgray);
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return base64_encode($data);
    }
}
