<?php

define('FBH_INIT', true);

require 'config.php';
require 'db-controls.php';
require 'telegram-actions.php';


function process_message($message) {
  if (!array_key_exists('forward_from', $message)) {
    return null;
  }
  
  $user = $message['forward_from'];
  
  $user_id = $user['id'];
  $first_name = $user['first_name'];
  if (array_key_exists('last_name', $user)) {
    $last_name = $user['last_name'];
  } else {
    $last_name = null;
  }
  if (array_key_exists('username', $user)) {
    $username = $user['username'];
  } else {
    $username = null;
  }
  $profile_photo_id = get_profile_photo_id($user_id);
  $profile_photo_content = get_profile_photo_content($profile_photo_id);

  $db = new SQLite3('users.db');
  $rowid = save_user($db, $user_id, $username, $first_name, $last_name, $profile_photo_id, $profile_photo_content);
  $db->close();

  return [
    'method' => 'sendMessage',
    'text' => SERVER_ROOT.'/chart.php?rowid='.$rowid,
  ];
}


if (!(array_key_exists('token', $_GET) && $_GET['token'] == WEBHOOK_TOKEN)) {
  http_response_code(403);
  echo 'Forbidden';
  die();
}

$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
  http_response_code(400);
  echo 'Not receiving a proper JSON.';
  die();
}

if (!array_key_exists('message', $update)) {
  http_response_code(204);
  die();
}

$response = process_message($update['message']);

if (!$response) {
  $response = [
    'method' => 'sendMessage',
    'text' => 'Usage: Forward a message from a user to me.',
  ];
}

$response['chat_id'] = $update['message']['chat']['id'];

/*
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
*/

http_response_code(204);
send_api_request($response['method'], $response);
