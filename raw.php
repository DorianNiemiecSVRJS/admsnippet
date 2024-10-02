<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $snippetid = 0;
  $badrequest = false;
  $queryerror = false;
  $entry = null;
  $snippet = null;

  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $badrequest = true;
  } else {
    $snippetid = intval($_GET['id']);
    $entrystmt = mysqli_prepare($db, 'SELECT snippet FROM snippets WHERE id = ?');
    if ($entrystmt) {
      mysqli_stmt_bind_param($entrystmt, 'i', $snippetid);
      mysqli_stmt_execute($entrystmt);
      $entryresult = mysqli_stmt_get_result($entrystmt);
      if ($entryresult) {
        $result = mysqli_fetch_assoc($entryresult);
        mysqli_stmt_close($entrystmt);
        if (strlen($result['snippet']) > 2 && $result['snippet'][0] == "#" && $result['snippet'][1] == "!") {
          // Use Unix line endings if snippet begins with "#!" characters (which may indicate shebang line).
          $snippet = str_replace(["\r\n", "\r"], "\n", $result['snippet']);
        } else {
          // Use DOS/Windows line endings otherwise.
          $snippet = str_replace(["\r\n", "\n"], "\r", $result['snippet']);
        }
      } else {
        mysqli_stmt_close($entrystmt);
        $queryerror = true;
      }
    } else {
      $queryerror = true;
    }
  }

  header('Content-Type: text/plain; charset=utf-8');
  if ($badrequest) {
    http_response_code(400);
    echo "Invalid snippet";
  } elseif ($queryerror) {
    http_response_code(500);
    echo "Problem with retrieving the snippet";
  } elseif (!$result) {
    http_response_code(404);
    echo "Snippet not found";
  } else {
    echo $snippet;
  }
?>
<?php include("includes/finalize.php"); ?>
