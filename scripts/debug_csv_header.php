<?php
$csv = "\xEF\xBB\xBFci;first_name;last_name;phone;email;invitations;confirmacion;notes\n12345;Ana;García;123456789;ana@test.com;2;pendiente;Primera fila\n";
$lines = explode("\n", $csv);
$firstLine = $lines[0] . "\n";
$countSemi = substr_count($firstLine, ';');
$countComma = substr_count($firstLine, ',');
$delimiter = $countSemi >= $countComma ? ';' : ',';
echo "Detected delimiter: $delimiter\n";
// Simulate fgetcsv producing single-field header (not likely here)
$firstRow = str_getcsv($firstLine, $delimiter);
$header = array_map(function ($value) {
    $value = preg_replace('/^\xEF\xBB\xBF/u', '', (string) $value);
    $value = strtolower(trim((string) $value));
    return str_replace([';', ','], '', $value);
}, $firstRow);
print_r($header);
