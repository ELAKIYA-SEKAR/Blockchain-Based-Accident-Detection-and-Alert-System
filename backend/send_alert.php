use Twilio\Rest\Client;

function sendTwilioSMS($message) {
    $account_sid = $_ENV['TWILIO_SID'] ?? '';
    $auth_token = $_ENV['TWILIO_AUTH_TOKEN'] ?? '';
    $twilio_number = $_ENV['TWILIO_NUMBER'] ?? '';
    $recipient_number = $_ENV['EMERGENCY_CONTACT'] ?? '';

    if (empty($account_sid) || empty($auth_token) || empty($twilio_number) || empty($recipient_number)) {
        error_log("Twilio configuration missing", 3, LOG_DIR . 'debug.log');
        return false;
    }

    try {
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $recipient_number,
            [
                'from' => $twilio_number,
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        error_log("Twilio SMS failed: " . $e->getMessage(), 3, LOG_DIR . 'debug.log');
        return false;
    }
}
?>