<?php
chdir("../../../../");
$files = glob("coverage_data/{$_GET['id']}.*");
require 'vendor/autoload.php';

$coverage = []; //new PHP_CodeCoverage();
foreach($files as $file) {
    $data = unserialize(file_get_contents($file));
    unlink($file);
    foreach($data as $file => $lines) {
        if(!isset($coverage[$file])) {
            $coverage[$file] = $lines;
        } else {
            foreach($lines as $line => $flag) {
                if(!isset($coverage[$file][$line]) || $flag > $coverage[$file][$line]) {
                    $coverage[$file][$line] = $flag;
                }
            }
        }
    }
}

echo serialize($coverage);
