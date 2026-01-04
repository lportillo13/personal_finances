<?php
header('Content-Type: text/plain');

echo "diag ok\n";
echo 'phpversion: ' . phpversion() . "\n";
echo 'SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
echo 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
