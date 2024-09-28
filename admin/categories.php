<?php define("ADMSNIPPET", null); ?>
<?php include("../config.php"); ?>
<?php include("../includes/setup.php"); ?>
<?php include("../includes/admincheck.php"); ?>
<?php
  $error_message = null;
  $page_title = "Manage categories";
  $page_description = "Manage categories in AdmSnippet";

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
      $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['action']) || !$_POST['action']) {
      $error_message = "Invalid action.";
    } elseif ($_POST['action'] == "add") {
      if (!isset($_POST['name']) || !$_POST['name']) {
        $error_message = "You need to input a category name.";
      } else {
        $stmt = mysqli_prepare($db, 'INSERT INTO categories (name) VALUES (?);');
        if ($stmt) {
          mysqli_stmt_bind_param($stmt, 's', $_POST['name']);
          mysqli_stmt_execute($stmt);
          $insert_id = mysqli_stmt_insert_id($stmt);
          mysqli_stmt_close($stmt);
          if (!$insert_id) {
            $error_message = "An internal server error has occurred when adding a category.";
          }
        } else {
          $error_message = "An internal server error has occurred when adding a category.";
        }
      }
    } elseif ($_POST['action'] == "rename") {
      if (!isset($_POST['name']) || !$_POST['name']) {
        $error_message = "You need to input a category name.";
      } elseif (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $error_message = "Invalid category ID.";
      } else {
        $stmt = mysqli_prepare($db, 'UPDATE categories SET name = ? WHERE id = ?;');
        $categoryid = intval($_POST['id']);
        if ($stmt) {
          mysqli_stmt_bind_param($stmt, 'si', $_POST['name'], $categoryid);
          $isexecsuccess = mysqli_stmt_execute($stmt);
          if ($isexecsuccess) {
            $isupdated = mysqli_stmt_affected_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            if (!$isupdated) {
              $error_message = "The category you have requested to rename doesn't exist.";
            }
          } else {
            mysqli_stmt_close($stmt);
            $error_message = "An internal server error has occurred when renaming a category.";
          }
        } else {
          $error_message = "An internal server error has occurred when renaming a category.";
        }
      }
    } elseif ($_POST['action'] == "delete") {
      if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $error_message = "Invalid category ID.";
      } else {
        $stmt = mysqli_prepare($db, 'DELETE FROM categories WHERE id = ?;');
        $categoryid = intval($_POST['id']);
        if ($stmt) {
          mysqli_stmt_bind_param($stmt, 'i', $categoryid);
          $isexecsuccess = mysqli_stmt_execute($stmt);
          if ($isexecsuccess) {
            $isdeleted = mysqli_stmt_affected_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            if ($isdeleted) {
                $stmt2 = mysqli_prepare($db, 'DELETE FROM votes WHERE snippet_id IN (SELECT id FROM snippets WHERE category_id = ?);');
                if ($stmt2) {
                  mysqli_stmt_bind_param($stmt2, 'i', $categoryid);
                  $isexecsuccess2 = mysqli_stmt_execute($stmt2);
                  mysqli_stmt_close($stmt2);
                  if ($isexecsuccess2) {
                      $stmt3 = mysqli_prepare($db, 'DELETE FROM snippets WHERE category_id = ?;');
                      if ($stmt3) {
                        mysqli_stmt_bind_param($stmt3, 'i', $categoryid);
                        $isexecsuccess3 = mysqli_stmt_execute($stmt3);
                        mysqli_stmt_close($stmt3);
                        if (!$isexecsuccess3) {
                          $error_message = 'An internal server error has occurred when deleting a category.';
                        }
                      } else {
                        $error_message = 'An internal server error has occurred when deleting a category.';
                      }
                  } else {
                    $error_message = 'An internal server error has occurred when deleting a category.';
                  }
                } else {
                  $error_message = 'An internal server error has occurred when deleting a category.';
                }
            } else {
              $error_message = "An internal server error has occurred when deleting a category.";
            }
          } else {
            mysqli_stmt_close($stmt);
            $error_message = "An internal server error has occurred when deleting a category.";
          }
        } else {
          $error_message = "An internal server error has occurred when deleting a category.";
        }
      }
    }
  }
?>
<?php include("../includes/header.php"); ?>
<main class="page">
  <div class="container">
    <h1>Administration panel</h1>
    <p><a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/">Return to the administration panel</a></p>
    <?php
      if ($error_message) {
        echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
      }
    ?>
    <h2>Add a category</h2>
    <form action="<?php echo htmlspecialchars(APP_ROOT); ?>admin/categories.php" method="post" class="form-visible">
      <div class="form-element">
        <label for="catname">Category name:</label>
        <input type="text" name="name" id="catname" maxlength="255">
      </div>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <input type="submit" value="Add" class="button">
    </form>
    <h2>Rename a category</h2>
    <form action="<?php echo htmlspecialchars(APP_ROOT); ?>admin/categories.php" method="post" class="form-visible">
      <div class="form-element">
        <label for="catselect">Category:</label>
        <select name="id" id="catselect">
          <?php
            $categories = mysqli_query($db, 'SELECT id, name FROM categories ORDER BY id;');
            if ($categories) {
              while ($category = mysqli_fetch_assoc($categories)) {
                echo '<option value="' . htmlspecialchars($category['id']) . '">' . htmlspecialchars($category['name']) . '</option>';
              }
            }
          ?>
        </select>
      </div>
      <div class="form-element">
        <label for="catname2">New name:</label>
        <input type="text" name="name" id="catname2" maxlength="255">
      </div>
      <input type="hidden" name="action" value="rename">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <input type="submit" value="Rename" class="button">
    </form>
    <h2>Delete a category</h2>
    <form action="<?php echo htmlspecialchars(APP_ROOT); ?>admin/categories.php" method="post" class="form-visible">
      <div class="form-element">
        <label for="catselect">Category:</label>
        <select name="id" id="catselect2">
          <?php
            $categories = mysqli_query($db, 'SELECT id, name FROM categories ORDER BY id;');
            if ($categories) {
              while ($category = mysqli_fetch_assoc($categories)) {
                echo '<option value="' . htmlspecialchars($category['id']) . '">' . htmlspecialchars($category['name']) . '</option>';
              }
            }
          ?>
        </select>
      </div>
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <input type="submit" value="Delete" class="button">
    </form>
  </div>
</main>
<?php include("../includes/footer.php"); ?>
<?php include("../includes/finalize.php"); ?>