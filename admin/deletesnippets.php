<?php define("ADMSNIPPET", null); ?>
<?php include("../config.php"); ?>
<?php include("../includes/setup.php"); ?>
<?php include("../includes/admincheck.php"); ?>
<?php
  $error_message = null;
  $page_title = "Delete snippets";
  $page_description = "Delete snippets from AdmSnippet.";

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
        $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $error_message = "Invalid snippet ID";
    } else {
      $stmt = mysqli_prepare($db, 'DELETE FROM snippets WHERE id = ?;');
      $snippetid = intval($_POST['id']);
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $snippetid);
        $isexecsuccess = mysqli_stmt_execute($stmt);
        if (!$isexecsuccess) {
          mysqli_stmt_close($stmt);
          $servererror = true;
        } else {
          $isdeleted = mysqli_stmt_affected_rows($stmt) > 0;
          mysqli_stmt_close($stmt);
          if ($isdeleted) {
            $stmt2 = mysqli_prepare($db, 'DELETE FROM votes WHERE snippet_id = ?;');
            if ($stmt2) {
              mysqli_stmt_bind_param($stmt2, 'i', $snippetid);
              $isexecsuccess2 = mysqli_stmt_execute($stmt2);
              if (!$isexecsuccess2) {
                $error_message = 'An internal server error has occurred during snippet deletion.';
              }
              mysqli_stmt_close($stmt2);
            } else {
              $error_message = 'An internal server error has occurred during snippet deletion.';
            }
          }
        }
      } else {
        $error_message = 'An internal server error has occurred during snippet deletion.';
      }
    }
  }
?>
<?php include("../includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Delete snippets</h1>
        <p><a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/">Return to the administration panel</a></p>
        <?php
          if ($error_message) {
            echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
          }
        ?>
        <table>
          <tr>
            <th class="table-cell-left">Snippet name</th>
            <th class="table-cell-right">Delete</th>
          </tr>
          <?php
            $snippets = mysqli_query($db, 'SELECT id, date, title FROM snippets ORDER BY date DESC;');
            if ($snippets) {
              $entries_present = false;
              while ($snippet = mysqli_fetch_assoc($snippets)) {
                $entries_present = true;
                echo '<tr>
                  <td class="table-cell-left"><a href="' . htmlspecialchars(APP_ROOT) . 'snippet.php?id=' . htmlspecialchars(urlencode($snippet['id'])) . '">' . htmlspecialchars($snippet['title']) . '</a></td>
                  <td class="table-cell-right"><form action="' . htmlspecialchars(APP_ROOT) . 'admin/deletesnippets.php" method="post" class="form-shorthand">
                    <input type="submit" class="button" value="Delete">
                    <input type="hidden" name="id" value="' . htmlspecialchars($snippet['id']) . '">
                    <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
                  </form></td>
                </tr>';
              }
              if (!$entries_present) {
                echo '<tr>
                  <td class="table-cell-left">No snippets.</td>
                  <td class="table-cell-right"></td>
                </tr>';
              }
            } else {
              echo '<tr>
                <td class="table-cell-left">An error has occurred during retrieval of snippets!</td>
                <td class="table-cell-right"></td>
              </tr>';
            }
          ?>
        </table>
        <br>
    </div>
</main>
<?php include("../includes/footer.php"); ?>
<?php include("../includes/finalize.php"); ?>
