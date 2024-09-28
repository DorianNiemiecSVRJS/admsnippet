<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $redirect = null;
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['redirect']) && strlen($_POST['redirect']) > 0 && $_POST['redirect'][0] == "/" && (strlen($_POST['redirect']) == 1 || $_POST['redirect'][1] != "/")) {
    $redirect = $_POST['redirect'];
  }

  if ($user == -1) {
    header('Location: ' . (APP_ROOT . 'login.php?redirect=' . urlencode($redirect ? $redirect : (APP_ROOT . 'snippet.php?id=' . urlencode($_POST['id'])))));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }

  $invalidmethod = true;
  $badrequest = false;
  $servererror = false;
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invalidmethod = false;
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
      $badrequest = true;
    } elseif (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
      $badrequest = true;
    } elseif (!isset($_POST['action'])) {
      $badrequest = true;
    } elseif ($_POST['action'] == 'reset' || $_POST['action'] == 'up' || $_POST['action'] == 'down') {
      $stmt = mysqli_prepare($db, 'DELETE FROM votes WHERE snippet_id = ? AND user_id = ?;');
      $snippetid = intval($_POST['id']);
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $snippetid, $user);
        $isexecsuccess = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if (!$isexecsuccess) {
          $servererror = true;
        } else {
          if ($_POST['action'] == 'up' || $_POST['action'] == 'down') {
            $isdownvote = $_POST['action'] == 'down' ? 1 : 0;
            $stmt2 = mysqli_prepare($db, 'INSERT INTO votes (snippet_id, user_id, is_downvote) VALUES (?, ?, ?);');
            if ($stmt2) {
              mysqli_stmt_bind_param($stmt2, 'iii', $snippetid, $user, $isdownvote);
              $isexecsuccess2 = mysqli_stmt_execute($stmt2);
              mysqli_stmt_close($stmt2);
              if (!$isexecsuccess2) {
                $servererror = true;
              }
            } else {
              $servererror = true;
            }
          }
        }
      } else {
        $servererror = true;
      }
    } else {
      $badrequest = true;
    }
  }
  if ($invalidmethod) {
    http_response_code(405);
    $page_title = "Invalid method";
    $page_description = "Invalid method was used while attempting to vote.";
  } elseif ($servererror) {
    http_response_code(500);
    $page_title = "Error while voting";
    $page_description = "An internal server error has occurred while voting.";
  } elseif ($badrequest) {
    http_response_code(400);
    $page_title = "Invalid vote";
    $page_description = "The request for voting is invalid.";
  } else {
    header('Location: ' . ($redirect ? $redirect : (APP_ROOT . 'snippet.php?id=' . urlencode($_POST['id']))));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <?php
          if ($invalidmethod) {
            echo '<h1>Invalid method</h1>
                  <p>Invalid method was used while attempting to vote.</p>';
          } elseif ($servererror) {
            echo '<h1>Error while voting</h1>
                  <p>An internal server error has occurred while voting.</p>';
          } elseif ($badrequest) {
            echo '<h1>Invalid vote</h1>
                  <p>The request for voting is invalid.</p>';
          }
        ?>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
