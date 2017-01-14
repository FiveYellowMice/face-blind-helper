<?php

define('FBH_INIT', true);

require 'config.php';


function error_404_page($description) {
  http_response_code(404);
  ?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERROR</title>
  </head>
  <body>
    <h1>ERROR</h1>
    <p><?= $description ?></p>
  </body>
</html>
<?php
  die();
}

function run_sql($db, $sql, $params) {
  $stmt = $db->prepare($sql);

  foreach ($params as $param) {
    $stmt->bindValue($param[0], $param[1], $param[2]);
  }

  $query = $stmt->execute();

  $results = [];
  while ($result = $query->fetchArray(SQLITE3_ASSOC)) {
    $results[] = $result;
  }

  return $results;
}


if (!array_key_exists('rowid', $_GET)) {
  error_404_page('No rowid given.');
}

$rowid = intval($_GET['rowid']);

if ($rowid == 0) {
  error_404_page('Rowid is not a number.');
}


$db = new SQLite3('users.db');

$user_id_rows = run_sql($db, "SELECT user_id, date FROM user_ids WHERE id = :id", [[':id', $rowid, SQLITE3_INTEGER]]);
if (count($user_id_rows) == 0) {
  error_404_page('User not found.');
}

$username_rows = run_sql($db, "SELECT username, date FROM usernames WHERE user = :user", [[':user', $rowid, SQLITE3_INTEGER]]);

$first_name_rows = run_sql($db, "SELECT first_name, date FROM first_names WHERE user = :user", [[':user', $rowid, SQLITE3_INTEGER]]);

$last_name_rows = run_sql($db, "SELECT last_name, date FROM last_names WHERE user = :user", [[':user', $rowid, SQLITE3_INTEGER]]);

$profile_photo_rows = run_sql($db, "SELECT id, file_id, date FROM profile_photos WHERE user = :user", [[':user', $rowid, SQLITE3_INTEGER]]);

$db->close();


$max_data_count = max([count($user_id_rows), count($username_rows), count($first_name_rows), count($last_name_rows), count($profile_photo_rows)]);

function format_date($timestamp) {
  return date('r', $timestamp);
}


?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <title>User <?= htmlspecialchars($user_id_rows[0]['user_id']) ?></title>
    <style>
      html {
        font-family: sans-serif;
      }
      table, th, td {
        border: 1px solid black;
      }
      .profile-photo {
        width: 64px;
        height: 64px;
      }
    </style>
  </head>
  <body>
    <table>
      <thead>
        <tr>
          <th>User ID on Telegram</th>
          <th>Username</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Profile Photo</th>
        </tr>
      </thead>
      <tbody>
      <?php for ($i = 0; $i < $max_data_count; $i++) { ?>
        <tr>
          <td>
            <?php if (array_key_exists($i, $user_id_rows)) { ?>
            <span title="<?= format_date($user_id_rows[$i]['date']) ?>">
              <?= htmlspecialchars($user_id_rows[$i]['user_id']) ?>
            </span>
            <?php } ?>
          </td>
          <td>
            <?php if (array_key_exists($i, $username_rows)) { ?>
            <span title="<?= format_date($username_rows[$i]['date']) ?>">
              <?= htmlspecialchars($username_rows[$i]['username']) ?>
            </span>
            <?php } ?>
          </td>
          <td>
            <?php if (array_key_exists($i, $first_name_rows)) { ?>
            <span title="<?= format_date($first_name_rows[$i]['date']) ?>">
              <?= htmlspecialchars($first_name_rows[$i]['first_name']) ?>
            </span>
            <?php } ?>
          </td>
          <td>
            <?php if (array_key_exists($i, $last_name_rows)) { ?>
            <span title="<?= format_date($last_name_rows[$i]['date']) ?>">
              <?= htmlspecialchars($last_name_rows[$i]['last_name']) ?>
            </span>
            <?php } ?>
          </td>
          <td>
            <?php if (array_key_exists($i, $profile_photo_rows)) { ?>
            <img
              class="profile-photo"
              title="<?= format_date($profile_photo_rows[$i]['date']) ?>"
              alt="<?= htmlspecialchars($profile_photo_rows[$i]['file_id']) ?>"
              src="<?= SERVER_ROOT.'/profile-photo.php?id='.$profile_photo_rows[$i]['id'] ?>"
            >
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </body>
</html>
