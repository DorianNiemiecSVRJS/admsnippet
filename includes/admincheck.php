<?php
  if (!defined('ADMSNIPPET')) die;
  $admin = false;
  if ($user > -1) {
    $admincheckstmt = mysqli_prepare($db, 'SELECT * FROM users WHERE id = ? AND is_admin = 1');
    if ($admincheckstmt) {
      mysqli_stmt_bind_param($admincheckstmt, 'i', $user);
      mysqli_stmt_execute($admincheckstmt);
      mysqli_stmt_store_result($admincheckstmt);
      if (mysqli_stmt_num_rows($admincheckstmt) > 0) {
        $admin = true;
      }
      mysqli_stmt_close($admincheckstmt);
    }
    unset($admincheckstmt);
  }
  if (!$admin) {
    header('Location: ' . (APP_ROOT));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
?>