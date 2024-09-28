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
  if ($user == -1) {
    header('Location: ' . ($redirect ? $redirect : APP_ROOT));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf']) && $_POST['csrf'] == $csrf_token) {
        unset($_SESSION['user']);
    }
    header('Location: ' . ($redirect ? $redirect : APP_ROOT));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
  http_response_code(405);
  $page_title = "Invalid method";
  $page_description = "Invalid method was used while attempting to log out.";
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Invalid method</h1>
        <p>Invalid method was used while attempting to log out.</p>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
