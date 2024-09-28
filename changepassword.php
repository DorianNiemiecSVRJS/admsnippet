<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  if ($user == -1) {
    header('Location: ' . (APP_ROOT . 'login.php?redirect=' . urlencode(APP_ROOT . 'changepassword.php')));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
  $error_message = null;
  $page_title = "Change password";
  $page_description = "Change your password on AdmSnippet.";
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
        $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['curpass'], $_POST['pass'], $_POST['pass2']) || !$_POST['curpass'] || !$_POST['pass'] || !$_POST['pass2']) {
        $error_message = "You need to input passwords.";
    } elseif ($_POST['pass'] != $_POST['pass2']) {
        $error_message = "Passwords don't match.";
    } else {
        $result = null;
        $stmt = mysqli_prepare($db, 'SELECT password FROM users WHERE id = ?;');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $user);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                $entry = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                if ($entry && password_verify($_POST['curpass'], $entry['password'])) {
                  $hashed_password = password_hash($_POST['pass'], PASSWORD_DEFAULT);
                  $stmt2 = mysqli_prepare($db, 'UPDATE users SET password = ? WHERE id = ?;');
                  if ($stmt2) {
                    mysqli_stmt_bind_param($stmt2, 'si', $hashed_password, $user);
                    $isexecsuccess = mysqli_stmt_execute($stmt2);
                    if (!$isexecsuccess) {
                      mysqli_stmt_close($stmt2);
                      $error_message = 'An internal server error has occurred during changing the password.';
                    } else {
                      $isupdated = mysqli_stmt_affected_rows($stmt2) > 0;
                      mysqli_stmt_close($stmt2);
                      if ($isupdated) {
                        header('Location: ' . (APP_ROOT . 'user.php?id=' . urlencode($user)));
                        http_response_code(302);
                        include("includes/finalize.php");
                        exit;
                      } else {
                        $error_message = 'An internal server error has occurred during changing the password.';
                      }
                    }
                  } else {
                    $error_message = 'An internal server error has occurred during changing the password.';
                  }
                } else {
                    $error_message = 'Invalid password.';
                }
            } else {
                mysqli_stmt_close($stmt);
                $error_message = 'An internal server error has occurred during changing the password.';
            }
        } else {
            $error_message = 'An internal server error has occurred during changing the password.';
        }
    }
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Change password</h1>
        <form action="<?php echo htmlspecialchars(APP_ROOT); ?>changepassword.php" method="post" class="form-visible">
            <div class="form-element">
              <label for="curpass">Current password:</label>
              <input type="password" type="text" name="curpass" id="curpass" required>
            </div>
            <div class="form-element">
              <label for="pass">Password:</label>
              <input type="password" name="pass" id="pass" required>
            </div>
            <div class="form-element">
              <label for="pass2">Confirm password:</label>
              <input type="password" name="pass2" id="pass2" required>
            </div>
            <?php
              if ($error_message) {
                echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
              }
            ?>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="submit" value="Change password" class="button">
        </form>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
