<?php

if (!array_key_exists('id', $_GET)) {
  http_response_code(404);
  die();
}

$id = intval($_GET['id']);

if ($id == 0) {
  http_response_code(404);
  die();
}

$db = new SQLite3('users.db');
$query = $db->query("SELECT file_content FROM profile_photos WHERE id = $id");

$result = $query->fetchArray(SQLITE3_ASSOC);

if (!$result) {
  http_response_code(404);
  die();
}

$file_content = $result['file_content'];

header('Content-Type: image/jpeg');
echo $file_content;
