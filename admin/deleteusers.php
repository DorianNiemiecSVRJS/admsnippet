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
        $error_message = "Invalid user ID";
    } else {
      $stmt = mysqli_prepare($db, 'DELETE FROM users WHERE id = ? AND id <> ?;');
      $userid = intval($_POST['id']);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $userid, $user);
            $isexecsuccess = mysqli_stmt_execute($stmt);
            if ($isexecsuccess) {
              $isdeleted = mysqli_stmt_affected_rows($stmt) > 0;
              mysqli_stmt_close($stmt);
              if ($isdeleted) {
                $stmt2 = mysqli_prepare($db, 'DELETE FROM votes WHERE user_id = ?;');
                if ($stmt2) {
                  mysqli_stmt_bind_param($stmt2, 'i', $userid);
                  $isexecsuccess2 = mysqli_stmt_execute($stmt2);
                  mysqli_stmt_close($stmt2);
                  if ($isexecsuccess2) {
                      $stmt3 = mysqli_prepare($db, 'DELETE FROM snippets WHERE user_id = ?;');
                      if ($stmt3) {
                        mysqli_stmt_bind_param($stmt3, 'i', $userid);
                        $isexecsuccess3 = mysqli_stmt_execute($stmt3);
                        mysqli_stmt_close($stmt3);
                        if (!$isexecsuccess3) {
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
    }
  }
?>
<?php include("../includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Delete users</h1>
        <p><a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/">Return to the administration panel</a></p>
        <?php
          if ($error_message) {
            echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
          }
        ?>
        <table>
          <tr>
            <th class="table-cell-left">Username</th>
            <th class="table-cell-right">Delete</th>
          </tr>
          <?php
            $userresult = mysqli_query($db, 'SELECT id, name FROM users ORDER BY id DESC;');
            if ($userresult) {
              $entries_present = false;
              while ($userresultrow = mysqli_fetch_assoc($userresult)) {
                $entries_present = true;
                echo '<tr>
                  <td class="table-cell-left"><a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars(urlencode($userresultrow['id'])) . '">' . htmlspecialchars($userresultrow['name']) . '</a></td>
                  <td class="table-cell-right">' . ($userresultrow['id'] != $user ? '<form action="' . htmlspecialchars(APP_ROOT) . 'admin/deleteusers.php" method="post" class="form-shorthand">
                    <input type="submit" class="button" value="Delete">
                    <input type="hidden" name="id" value="' . htmlspecialchars($userresultrow['id']) . '">
                    <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
                  </form>' : '') . '</td>
                </tr>';
              }
              if (!$entries_present) {
                echo '<tr>
                  <td class="table-cell-left">No users.</td>
                  <td class="table-cell-right"></td>
                </tr>';
              }
            } else {
              echo '<tr>
                <td class="table-cell-left">An error has occurred during retrieval of users!</td>
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
