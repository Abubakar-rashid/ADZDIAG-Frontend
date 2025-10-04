<?php
header("Content-Type: text/plain");

if (!isset($_POST['serial'])) {
    echo "⚠️ No serial provided!";
    exit;
}

$serial = trim($_POST['serial']); // already stripped V in JS

$file = __DIR__ . "/ford-1.txt"; // <-- adjust path if needed

if (!file_exists($file)) {
    echo "❌ Database missing!";
    exit;
}

$handle = fopen($file, "r");
$found = false;

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if ($line === "") continue;

    [$s, $code] = array_map("trim", explode("=", $line));
    if ($s === $serial) {
        echo $code;
        $found = true;
        break;
    }
}
fclose($handle);

if (!$found) {
    echo "❌ Code not found for V$serial";
}
