<?php
function readIniFile($file) {
    if (!file_exists($file)) {
        return [];
    }
    return parse_ini_file($file, true, INI_SCANNER_RAW) ?: [];
}

function writeIniFile($file, $data) {
    $content = '';
    foreach ($data as $section => $values) {
        $content .= "[$section]\n";
        foreach ($values as $key => $value) {
            $content .= "$key = \"$value\"\n";
        }
        $content .= "\n";
    }
    
    if (!file_put_contents($file, $content)) {
        error_log("Failed to write to $file", 3, LOG_DIR . 'debug.log');
        return false;
    }
    return true;
}
?>