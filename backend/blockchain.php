<?php
require_once __DIR__ . '/file_functions.php';

function loadBlockchain($file) {
    $data = readIniFile($file);
    $blockchain = [];
    
    foreach ($data as $block_id => $block_data) {
        if (strpos($block_id, 'block_') === 0) {
            $blockchain[] = $block_data;
        }
    }
    
    return $blockchain;
}

function saveBlockchain($blockchain, $file) {
    $ini_data = [];
    foreach ($blockchain as $index => $block) {
        $ini_data["block_$index"] = $block;
    }
    writeIniFile($file, $ini_data);
}
?>