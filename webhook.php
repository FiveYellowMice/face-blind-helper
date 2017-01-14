<?php

define('FBH_INIT', true);

require 'config.php';
require 'db-controls.php';


function max_photo_width($memo, $value) {
  if ($memo['width'] > $value['width']) {
    return $memo;
  } else {
    return $value;
  }
}

function send_api_request($method, $params) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.API_TOKEN.'/'.$method);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json; charset=utf-8' ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
  if (array_key_exists('http_proxy', $_SERVER)) {
    curl_setopt($ch, CURLOPT_PROXY, $_SERVER['http_proxy']);
  }
  $query = json_decode(curl_exec($ch), true);
  curl_close($ch);

  if (!$query) {
    return false;
  }

  if (!array_key_exists('result', $query)) {
    return false;
  }

  $result = $query['result'];
  return $result;
}

function get_profile_photo_id($user_id) {
  $photos = send_api_request('getUserProfilePhotos', [
    'user_id' => $user_id,
    'limit' => 1,
  ]);
  
  if ($photos['total_count'] == 0) {
    return null;
  }
  
  $photo = $photos['photos'][0];
  
  $largest = array_reduce($photo, 'max_photo_width', [ 'width' => 0 ]);
  
  return $largest['file_id'];
}

function get_profile_photo_content($profile_photo_id) {
  $file = send_api_request('getFile', [
    'file_id' => $profile_photo_id
  ]);

  if (!$file || !array_key_exists('file_path', $file)) {
    return null;
  }

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/file/bot'.API_TOKEN.'/'.$file['file_path']);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  if (array_key_exists('http_proxy', $_SERVER)) {
    curl_setopt($ch, CURLOPT_PROXY, $_SERVER['http_proxy']);
  }
  $content = curl_exec($ch);
  curl_close($ch);

  return $content;
}

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
