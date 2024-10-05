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
  $error_message = null;
  $page_title = "Log in";
  $page_description = "Log into AdmSnippet to upload snippets and vote.";
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
        $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['user'], $_POST['pass']) || !$_POST['user'] || !$_POST['pass']) {
        $error_message = "You need to input username/password.";
    } else {
        $result = null;
        $stmt = mysqli_prepare($db, 'SELECT id, password FROM users WHERE LOWER(name) = LOWER(?);');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $_POST['user']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                $entry = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                if ($entry && password_verify($_POST['pass'], $entry['password'])) {
                    $_SESSION['user'] = $entry['id'];
                    header('Location: ' . ($redirect ? $redirect : APP_ROOT));
                    http_response_code(302);
                    include("includes/finalize.php");
                    exit;
                } else {
                    $error_message = 'Invalid username/password.';
                }
            } else {
                mysqli_stmt_close($stmt);
                $error_message = 'An internal server error has occurred during logging in.';
            }
        } else {
            $error_message = 'An internal server error has occurred during logging in.';
        }
    }
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Log in</h1>
        <form action="<?php echo htmlspecialchars(APP_ROOT); ?>login.php" method="post" class="form-visible">
            <div class="form-element">
              <label for="user">Username:</label>
              <input type="text" name="user" id="user" required maxlength="255">
            </div>
            <div class="form-element">
              <label for="pass">Password:</label>
              <input type="password" name="pass" id="pass" required>
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
            <p>Don't have an account? <a href="<?php echo htmlspecialchars(APP_ROOT); ?>register.php<?php echo htmlspecialchars($redirect ? '?redirect=' . urlencode($redirect) : '') ?>">Register on AdmSnippet.</a></p>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="submit" value="Log in" class="button">
        </form>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
