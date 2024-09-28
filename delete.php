<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $invalidmethod = true;
  $badrequest = false;
  $servererror = false;
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invalidmethod = false;
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
      $badrequest = true;
    } elseif (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
      $badrequest = true;
    } else {
      $stmt = mysqli_prepare($db, 'DELETE FROM snippets WHERE id = ? AND user_id = ?;');
      $snippetid = intval($_POST['id']);
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $snippetid, $user);
        $isexecsuccess = mysqli_stmt_execute($stmt);
        if (!$isexecsuccess) {
          mysqli_stmt_close($stmt);
          $servererror = true;
        } else {
          $isdeleted = mysqli_stmt_affected_rows($stmt) > 0;
          mysqli_stmt_close($stmt);
          if ($isdeleted) {
            // The previous prepared statement adds a user ID condition.
            // If the snippet gets deleted (affected rows are greater than 0), and the error didn't occur (the -1 value),
            // also delete votes for a non-existent snippet.
            $stmt2 = mysqli_prepare($db, 'DELETE FROM votes WHERE snippet_id = ?;');
            if ($stmt2) {
              mysqli_stmt_bind_param($stmt2, 'i', $snippetid);
              $isexecsuccess2 = mysqli_stmt_execute($stmt2);
              if (!$isexecsuccess2) {
                $servererror = true;
              }
              mysqli_stmt_close($stmt2);
            } else {
              $servererror = true;
            }
          }
        }
      } else {
        $servererror = true;
      }
    }
  }
  if ($invalidmethod) {
    http_response_code(405);
    $page_title = "Invalid method";
    $page_description = "Invalid method was used while attempting to delete a snippet.";
  } elseif ($servererror) {
    http_response_code(500);
    $page_title = "Error while deleting a snippet";
    $page_description = "An internal server error has occurred while deleting a snippet.";
  } elseif ($badrequest) {
    http_response_code(400);
    $page_title = "Invalid deletion request";
    $page_description = "The request for snippet deletion is invalid.";
  } else {
    header('Location: ' . (APP_ROOT . 'user.php?id=' . urlencode($user)));
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
                  <p>Invalid method was used while attempting to delete a snippet.</p>';
          } elseif ($servererror) {
            echo '<h1>Error while deleting a snippet</h1>
                  <p>An internal server error has occurred while deleting a snippet.</p>';
          } elseif ($badrequest) {
            echo '<h1>Invalid deletion request</h1>
                  <p>The request for snippet deletion is invalid.</p>';
          }
        ?>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
