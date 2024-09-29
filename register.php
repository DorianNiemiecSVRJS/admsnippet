<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $redirect = null;
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['redirect']) && strlen($_POST['redirect']) > 0 && $_POST['redirect'][0] == "/" && (strlen($_POST['redirect']) == 1 || $_POST['redirect'][1] != "/")) {
    $redirect = $_POST['redirect'];
  } elseif (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0 && $_GET['redirect'][0] == "/" && (strlen($_GET['redirect']) == 1 || $_GET['redirect'][1] != "/")) {
    $redirect = $_GET['redirect'];
  }
  if ($user != -1) {
    header('Location: ' . ($redirect ? $redirect : APP_ROOT));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
  $question = null;
  if (!isset($_SESSION['captcha']) || !isset(CAPTCHA_QUESTIONS[$_SESSION['captcha']])) {
    $question = array_rand(CAPTCHA_QUESTIONS);
    $_SESSION['captcha'] = $question;
  } else {
    $question = $_SESSION['captcha'];
  }
  $answers = CAPTCHA_QUESTIONS[$question];
  $error_message = null;
  $page_title = "Register";
  $page_description = "Register in AdmSnippet to upload snippets and vote.";
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
        $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['user'], $_POST['pass'], $_POST['pass2'], $_POST['captcha']) || !$_POST['user'] || !$_POST['pass'] || !$_POST['pass2'] || !$_POST['captcha']) {
        $error_message = "You need to input username/password and answer the CAPTCHA.";
    } elseif ($_POST['pass'] != $_POST['pass2']) {
        $error_message = "Passwords don't match.";
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $_POST['user'])) {
        $error_message = "Username must consist only of alphanumeric characters.";
    } else {
        $captcha_valid = false;
        if (is_array($answers)) {
          foreach ($answers as $answer) {
            if (strcasecmp($answer, trim($_POST['captcha'])) == 0) {
              $captcha_valid = true;
              break;
            }
          }
        } else {
          if (strcasecmp($answers, trim($_POST['captcha'])) == 0) {
            $captcha_valid = true;
          }
        }
        if ($captcha_valid) {
          $hashed_password = password_hash($_POST['pass'], PASSWORD_DEFAULT);
          $stmt = mysqli_prepare($db, 'SELECT * FROM users WHERE name = ?');
          if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $_POST['user']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $userexists = intval(mysqli_stmt_num_rows($stmt));
            mysqli_stmt_close($stmt);
            if (!$userexists) {
              $stmt2 = mysqli_prepare($db, 'INSERT INTO users (name, password, joined, is_admin) VALUES (?, ?, NOW(), 0);');
              if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, 'ss', $_POST['user'], $hashed_password);
                mysqli_stmt_execute($stmt2);
                $insert_id = mysqli_stmt_insert_id($stmt2);
                mysqli_stmt_close($stmt2);
                if ($insert_id) {
                    $_SESSION['user'] = $insert_id;
                    header('Location: ' . ($redirect ? $redirect : APP_ROOT));
                    http_response_code(302);
                    include("includes/finalize.php");
                    exit;
                } else {
                    $error_message = 'An error has occurred during the registration, possibly due to username being already registered or internal server error.';
                }
              } else {
                $error_message = 'An error has occurred during the registration, possibly due to username being already registered or internal server error.';
              }
            } else {
              // Prevent user enumeration
              $error_message = 'An error has occurred during the registration, possibly due to username being already registered or internal server error.';
            }
          } else {
            $error_message = 'An error has occurred during the registration, possibly due to username being already registered or internal server error.';
          }
        } else {
          // Change the CAPTCHA question
          $question = array_rand(CAPTCHA_QUESTIONS);
          $_SESSION['captcha'] = $question;
          $answers = CAPTCHA_QUESTIONS[$question];
          $error_message = 'Wrong CAPTCHA answer.';
        }
    }
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Register</h1>
        <form action="<?php echo htmlspecialchars(APP_ROOT); ?>register.php" method="post" class="form-visible">
            <div class="form-element">
              <label for="user">Username:</label>
              <input type="text" name="user" id="user" required maxlength="255" pattern="[A-Za-z0-9]+">
            </div>
            <div class="form-element">
              <label for="pass">Password:</label>
              <input type="password" name="pass" id="pass" required>
            </div>
            <div class="form-element">
              <label for="pass2">Confirm password:</label>
              <input type="password" name="pass2" id="pass2" required>
            </div>
            <div class="form-captcha">
              <label for="captcha"><?php echo htmlspecialchars($question); ?></label>
              <input type="text" name="captcha" id="captcha" required>
            </div>
            <?php
              if ($redirect) {
                echo '<input type="hidden" name="redirect" value="' . htmlspecialchars($redirect) . '">';
              }
            ?>
            <?php
              if ($error_message) {
                echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
              }
            ?>
            <p>Already have an account? <a href="<?php echo htmlspecialchars(APP_ROOT); ?>login.php<?php echo htmlspecialchars($redirect ? '?redirect=' . urlencode($redirect) : '') ?>">Log into AdmSnippet.</a></p>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="submit" value="Register" class="button">
        </form>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
