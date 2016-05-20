<?php

$body = file_get_contents('php://input');
$body_arr = json_decode($body);
$send_id = $body_arr->entry[0]->messaging[0]->sender->id;
$access_token = 'INPUT TOKEN';
$method = 'POST';
$json = '{recipient: {id: '+$send_id+'},message: \'TEST\'';
$url = 'https://graph.facebook.com/v2.6/me/messages';

try {
watchdog('body', $body);
watchdog('test', $send_id);

$message = '{"recipient":{"id":"'.$send_id.'"},"message":{"text":"Move forward 100"}}';

// Setup cURL
$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token=INPUT TOKEN');
curl_setopt_array($ch, array(
    CURLOPT_POST => TRUE,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Content-Length: '+strlen($message)
    ),
    CURLOPT_POSTFIELDS => $message
));

// Send the request
$response = curl_exec($ch);
watchdog('curl', $response);

// Check for errors
if($response === FALSE){
    die(curl_error($ch));
}

} catch (Exception $e) {
	watchdog('error', $e);
}

// Server response is now stored in $result variable so you can process it



drupal_exit();

?>







