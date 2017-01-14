<?php

if (php_sapi_name() != 'cli') {
  http_response_code(403);
  echo 'This is a CLI script.';
  die();
}

echo "Creating tables...\n";

$db = new SQLite3('users.db');

$db->exec("CREATE TABLE user_ids (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  user_id TEXT UNIQUE NOT NULL,
  date INTEGER NOT NULL
)");
$db->exec("CREATE TABLE usernames (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  username TEXT NOT NULL,
  date INTEGER NOT NULL,
  user INTEGER REFERENCES user_ids(id)
)");
$db->exec("CREATE TABLE first_names (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  first_name TEXT NOT NULL,
  date INTEGER NOT NULL,
  user INTEGER REFERENCES user_ids(id)
)");
$db->exec("CREATE TABLE last_names (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  last_name TEXT NOT NULL,
  date INTEGER NOT NULL,
  user INTEGER REFERENCES user_ids(id)
)");
$db->exec("CREATE TABLE profile_photos (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  file_id TEXT NOT NULL,
  file_content BLOB NOT NULL,
  date INTEGER NOT NULL,
  user INTEGER REFERENCES user_ids(id)
)");

$db->close();

echo "Done.\n";
