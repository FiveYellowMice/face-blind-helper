<?php

if (!defined('FBH_INIT')) {
  http_response_code(403);
  echo 'Not allowed.';
  die();
}


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
