<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  if ($user == -1) {
    header('Location: ' . (APP_ROOT . 'login.php?redirect=' . urlencode(APP_ROOT . 'deletemyaccount.php')));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
  $error_message = null;
  $page_title = "Delete my account";
  $page_description = "Are you sure to delete your account on AdmSnippet? You will lose all the snippets you have uploaded on AdmSnippet.";
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
        $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['pass']) || !$_POST['pass']) {
        $error_message = "You need to input password.";
    } else {
      $passwordresult = null;
        $passwordstmt = mysqli_prepare($db, 'SELECT password FROM users WHERE id = ?;');
        if ($passwordstmt) {
            mysqli_stmt_bind_param($passwordstmt, 'i', $user);
            mysqli_stmt_execute($passwordstmt);
            $passwordresult = mysqli_stmt_get_result($passwordstmt);
            if ($passwordresult) {
                $passwordentry = mysqli_fetch_assoc($passwordresult);
                mysqli_stmt_close($passwordstmt);
                if ($passwordentry && password_verify($_POST['pass'], $passwordentry['password'])) {

        $stmt = mysqli_prepare($db, 'DELETE FROM users WHERE id = ? AND is_admin = 0;');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $user);
            $isexecsuccess = mysqli_stmt_execute($stmt);
            if ($isexecsuccess) {
              $isdeleted = mysqli_stmt_affected_rows($stmt) > 0;
              mysqli_stmt_close($stmt);
              if ($isdeleted) {
                $stmt2 = mysqli_prepare($db, 'DELETE FROM votes WHERE user_id = ?;');
                if ($stmt2) {
                  mysqli_stmt_bind_param($stmt2, 'i', $user);
                  $isexecsuccess2 = mysqli_stmt_execute($stmt2);
                  mysqli_stmt_close($stmt2);
                  if ($isexecsuccess2) {
                      $stmt3 = mysqli_prepare($db, 'DELETE FROM snippets WHERE user_id = ?;');
                      if ($stmt3) {
                        mysqli_stmt_bind_param($stmt3, 'i', $user);
                        $isexecsuccess3 = mysqli_stmt_execute($stmt3);
                        mysqli_stmt_close($stmt3);
                        if ($isexecsuccess3) {
                            header('Location: ' . APP_ROOT);
                            http_response_code(302);
                            include("includes/finalize.php");
                            exit;
                        } else {
                          $error_message = 'An internal server error has occurred during the account deletion.';
                        }
                      } else {
                        $error_message = 'An internal server error has occurred during the account deletion.';
                      }
                  } else {
                    $error_message = 'An internal server error has occurred during the account deletion.';
                  }
                } else {
                  $error_message = 'An internal server error has occurred during the account deletion.';
                }
              } else {
                $error_message = 'An internal server error has occurred during the account deletion.';
              }
            } else {
              mysqli_stmt_close($stmt);
              $error_message = 'An internal server error has occurred during the account deletion.';
            }
        } else {
            $error_message = 'An internal server error has occurred during the account deletion.';
        }
      } else {
        $error_message = 'Invalid password.';
    }
} else {
    mysqli_stmt_close($passwordstmt);
    $error_message = 'An internal server error has occurred during the account verification.';
}
} else {
$error_message = 'An internal server error has occurred during the account verification.';
}
    }
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Delete my account</h1>
        <p>Are you sure to delete your account on AdmSnippet? You will lose all the snippets you have uploaded on AdmSnippet.</p>
        <form action="<?php echo htmlspecialchars(APP_ROOT); ?>deletemyaccount.php" method="post" class="form-visible">
            <div class="form-element">
              <label for="pass">Password:</label>
              <input type="password" name="pass" id="pass" required>
            </div>
            <?php
              if ($error_message) {
                echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
              }
            ?>
            <input type="submit" value="Delete my account" class="button">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
        </form>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
