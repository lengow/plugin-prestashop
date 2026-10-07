<?php
/**
 * Standalone regression check: php tests/name-normalization.php
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

define('_PS_VERSION_', '8.0.0');

// Only the ASCII string operations used by export-header formatting are needed.
class Tools
{
    public static function substr(string $value, int $start, int $length): string
    {
        return substr($value, $start, $length);
    }

    public static function strtolower(string $value): string
    {
        return strtolower($value);
    }

    public static function strtoupper(string $value): string
    {
        return strtoupper($value);
    }
}

class Address
{
}

require_once dirname(__DIR__) . '/classes/models/LengowMain.php';
require_once dirname(__DIR__) . '/classes/models/LengowFeed.php';
require_once dirname(__DIR__) . '/classes/models/LengowAddress.php';

$failures = [];
$headers = [
    'Œuvre' => 'uvre',
    'Æther' => 'ther',
    'Źebra' => 'oeebra',
    'Ŵool' => 'zool',
    'Ŷarn' => 'aearn',
    'ŵool' => 'yool',
    'ÿarn' => 'warn',
    'Été' => 'ete',
];
foreach ($headers as $name => $expected) {
    foreach ([LengowFeed::FORMAT_CSV, LengowFeed::FORMAT_XML, LengowFeed::FORMAT_JSON, LengowFeed::FORMAT_YAML] as $format) {
        $actual = LengowFeed::formatFields($name, $format);
        if ($actual !== $expected) {
            $failures[] = "$format header $name: expected $expected, got $actual";
        }
    }
    $actual = LengowFeed::formatFields($name, LengowFeed::FORMAT_CSV, true);
    if ($actual !== strtoupper($expected)) {
        $failures[] = "Legacy CSV header $name: expected " . strtoupper($expected) . ", got $actual";
    }
}

$names = [
    'Œuvre Æther Źebra Ŵool Ŷarn ŵool ÿarn' => 'OEuvre AEther Zebra Wool Yarn wool yarn',
    ' Élodie123! ' => 'Elodie',
    'François' => 'Francois',
    '李雷' => '李雷',
    "Anne-Marie O'Neill" => "Anne-Marie O'Neill",
    '123!<>,;?=+()@#"�{}_$%:' => '',
    '' => '',
];
foreach ($names as $name => $expected) {
    $actual = LengowAddress::cleanName($name);
    if ($actual !== $expected) {
        $failures[] = "Customer name $name: expected $expected, got $actual";
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo 'Export-header compatibility and customer-name normalization checks passed.' . PHP_EOL;
