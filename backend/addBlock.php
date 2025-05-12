<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/blockchain.php';
require_once __DIR__ . '/../src/controllers/blockchain_functions.php';

// Check if data is provided
if (!isset($_GET['data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No data provided']);
    exit;
}

// Parse data (format: speed*location*alcohol_status*sleep_status*mcu_id)
$data = explode('*', $_GET['data']);
if (count($data) !== 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data format']);
    exit;
}

$speed = $data[0];
$location = $data[1];
$alcohol_status = $data[2];
$sleep_status = $data[3];
$mcu_id = $data[4];

// Validate data
if (!is_numeric($speed) || empty($location) || !in_array($alcohol_status, ['Sober', 'Drunk']) || !in_array($sleep_status, ['Awake', 'Sleeping'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data values']);
    exit;
}

// Create block data
$block_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'speed' => $speed,
    'location' => $location,
    'alcohol_status' => $alcohol_status,
    'sleep_status' => $sleep_status,
    'mcu_id' => $mcu_id,
];

// Load existing blockchain
$blockchain = loadBlockchain(BLOCKCHAIN_FILE);

// Calculate previous hash
$previous_hash = !empty($blockchain) ? hashBlock(end($blockchain)) : '0';

// Add new block
$block_data['previous_hash'] = $previous_hash;
$block_data['hash'] = hashBlock($block_data);
$blockchain[] = $block_data;

// Save to blockchain.ini
saveBlockchain($blockchain, BLOCKCHAIN_FILE);

// Optionally, save to PostgreSQL
require_once __DIR__ . '/../src/config/db_connect.php';
if ($pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO accidents (timestamp, speed, location, alcohol_status, sleep_status, mcu_id, hash, previous_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $block_data['timestamp'],
            $block_data['speed'],
            $block_data['location'],
            $block_data['alcohol_status'],
            $block_data['sleep_status'],
            $block_data['mcu_id'],
            $block_data['hash'],
            $block_data['previous_hash']
        ]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage(), 3, LOG_DIR . 'debug.log');
    }
}

// Trigger SMS alert
require_once __DIR__ . '/send_alert.php';
sendTwilioSMS("Accident detected by $mcu_id at $location. Speed: $speed, Alcohol: $alcohol_status, Sleep: $sleep_status");

// Respond to NodeMCU
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Block added']);
?>