<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  if ($user == -1) {
    header('Location: ' . (APP_ROOT . 'login.php?redirect=' . urlencode(APP_ROOT . 'submit.php')));
    http_response_code(302);
    include("includes/finalize.php");
    exit;
  }
  $error_message = null;
  $page_title = "Submit";
  $page_description = "Submit your snippet to AdmSnippet to help server administrators.";
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] != $csrf_token) {
        $error_message = "Potential CSRF attack detected.";
    } elseif (!isset($_POST['title'], $_POST['desc'], $_POST['content'], $_POST['category']) || !$_POST['title'] || !$_POST['desc'] || !$_POST['content'] || !is_numeric($_POST['category'])) {
        $error_message = "You need to input the title, description and contents, and select the category.";
    } else {
      $categoryid = intval($_POST['category']);
      $categorystmt = mysqli_prepare($db, 'SELECT * FROM categories WHERE id = ?;');
      if ($categorystmt) {
        mysqli_stmt_bind_param($categorystmt, 'i', $categoryid);
        mysqli_stmt_execute($categorystmt);
        mysqli_stmt_store_result($categorystmt);
        $categoryexists = intval(mysqli_stmt_num_rows($categorystmt)) > 0;
        mysqli_stmt_close($categorystmt);
        if ($categoryexists) {
          $stmt = mysqli_prepare($db, 'INSERT INTO snippets (category_id, user_id, title, date, description, snippet) VALUES (?, ?, ?, NOW(), ?, ?);');
          if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iisss', $categoryid, $user, $_POST['title'], $_POST['desc'], $_POST['content']);
            mysqli_stmt_execute($stmt);
            $insert_id = mysqli_stmt_insert_id($stmt);
            mysqli_stmt_close($stmt);
            if ($insert_id) {
                header('Location: ' . (APP_ROOT . 'snippet.php?id=' . urlencode($insert_id)));
                http_response_code(302);
                include("includes/finalize.php");
                exit;
            } else {
                $error_message = 'An internal server error has occurred during the submission.';
            }
          } else {
            $error_message = 'An internal server error has occurred during the submission.';
          }
        } else {
          $error_message = 'The category for the snippet doesn\'t exist.';
        }
      } else {
        $error_message = 'An internal server error has occurred during the submission.';
      }
    }
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
        <h1>Submit</h1>
        <form action="<?php echo htmlspecialchars(APP_ROOT); ?>submit.php" method="post" class="form-visible">
            <div class="form-element">
              <label for="title">Title:</label>
              <input type="text" name="title" id="title" required maxlength="255">
            </div>
            <div class="form-element">
              <label for="category">Category:</label>
              <select name="category" id="category">
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
            <div class="form-element form-full">
              <label for="desc">Description:</label>
              <input type="text" name="desc" id="desc" maxlength="8191">
            </div>
            <div class="form-element">
              <label for="content">Content:</label>
              <textarea name="content" id="content" class="form-code-textbox" required></textarea>
            </div>
            <?php
              if ($error_message) {
                echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
              }
            ?>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="submit" value="Submit" class="button">
        </form>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
