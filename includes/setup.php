<?php
  if (!defined('ADMSNIPPET')) die;

  if (!version_compare(phpversion(), '5.5', '>=')) {
    die("PHP version not supported.");
  }
  
  mysqli_report(MYSQLI_REPORT_OFF);
  $db = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB) or die("Cannot connect to the database!");

  session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
  ]) or die("Can't start session!");

  $csrf_token = "";
  if (isset($_SESSION['csrf'])) {
    $csrf_token = $_SESSION['csrf'];
  } else {
    if (function_exists('random_bytes')) {
      $csrf_token = bin2hex(random_bytes(32));
    } else {
      $csrf_token = '';
      for ($i = 0; $i < 32; $i++) {
        $csrf_token = $csrf_token . bin2hex(rand(0,255));
      }
    }
    $_SESSION['csrf'] = $csrf_token;
  }

  $user = -1;
  if (isset($_SESSION['user'])) {
    $usercheckstmt = mysqli_prepare($db, 'SELECT * FROM users WHERE id = ?;');
    if ($usercheckstmt) {
      mysqli_stmt_bind_param($usercheckstmt, 'i', $_SESSION['user']);
      mysqli_stmt_execute($usercheckstmt);
      mysqli_stmt_store_result($usercheckstmt);
      if (mysqli_stmt_num_rows($usercheckstmt) > 0) {
        $user = $_SESSION['user'];
      }
      mysqli_stmt_close($usercheckstmt);
    }
    unset($usercheckstmt);
  }
  if ($user == -1) {
    unset($_SESSION['user']);
  }

  $highlight_code = false;
?>
