<?php

if (!defined('FBH_INIT')) {
  http_response_code(403);
  echo 'Not allowed.';
  die();
}


function save_user($db, $user_id, $username, $first_name, $last_name, $profile_photo_id, $profile_photo_content) {
  $time = time();

  $stmt = $db->prepare("SELECT id FROM user_ids WHERE user_id = :user_id");
  $stmt->bindvalue(':user_id', (string) $user_id, SQLITE3_TEXT);
  $existing_user = $stmt->execute()->fetchArray();

  if ($existing_user) {
    $id = $existing_user['id'];
  } else {
    $stmt = $db->prepare("INSERT INTO user_ids (user_id, date) VALUES (:user_id, :date)");
    $stmt->bindValue(':user_id', (string) $user_id, SQLITE3_TEXT);
    $stmt->bindValue(':date', $time, SQLITE3_INTEGER);
    $stmt->execute();
    $id = $db->lastInsertRowID();
  }

  if ($username) {
    $stmt = $db->prepare("INSERT INTO usernames (username, date, user) SELECT :username, :date, :user WHERE NOT EXISTS (SELECT 1 FROM usernames WHERE user = :user AND username = :username)");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':date', $time, SQLITE3_INTEGER);
    $stmt->bindValue(':user', $id, SQLITE3_INTEGER);
    $stmt->execute();
  }

  if ($first_name) {
    $stmt = $db->prepare("INSERT INTO first_names (first_name, date, user) SELECT :first_name, :date, :user WHERE NOT EXISTS (SELECT 1 FROM first_names WHERE user = :user AND first_name = :first_name)");
    $stmt->bindValue(':first_name', $first_name, SQLITE3_TEXT);
    $stmt->bindValue(':date', $time, SQLITE3_INTEGER);
    $stmt->bindValue(':user', $id, SQLITE3_TEXT);
    $stmt->execute();
  }

  if ($last_name) {
    $stmt = $db->prepare("INSERT INTO last_names (last_name, date, user) SELECT :last_name, :date, :user WHERE NOT EXISTS (SELECT 1 FROM last_names WHERE user = :user AND last_name = :last_name)");
    $stmt->bindValue(':last_name', $last_name, SQLITE3_TEXT);
    $stmt->bindValue(':date', $time, SQLITE3_INTEGER);
    $stmt->bindValue(':user', $id, SQLITE3_TEXT);
    $stmt->execute();
  }

  if ($profile_photo_id) {
    $stmt = $db->prepare("INSERT INTO profile_photos (file_id, file_content, date, user) SELECT :file_id, :file_content, :date, :user WHERE NOT EXISTS (SELECT 1 FROM profile_photos WHERE user = :user AND file_id = :file_id)");
    $stmt->bindValue(':file_id', $profile_photo_id, SQLITE3_TEXT);
    $stmt->bindValue(':file_content', $profile_photo_content, SQLITE3_BLOB);
    $stmt->bindValue(':date', $time, SQLITE3_INTEGER);
    $stmt->bindValue(':user', $id, SQLITE3_TEXT);
    $stmt->execute();
  }

  return $id;
}
