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
  $snippetid = null;
  $badrequest = false;
  $queryerror = false;
  $result = null;
  $snippet_categoryid = null;
  $snippet_title = null;
  $snippet_description = null;
  $snippet_snippet = null;

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
      $badrequest = true;
    } else {
      $snippetid = intval($_POST['id']);
    }
  } else {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $badrequest = true;
    } else {
      $snippetid = intval($_GET['id']);
    }
  }

  if (!$badrequest) {
    $snippetstmt = mysqli_prepare($db, 'SELECT category_id, title, description, snippet FROM snippets WHERE id = ? AND user_id = ?;');
    if ($snippetstmt) {
      mysqli_stmt_bind_param($snippetstmt, 'ii', $snippetid, $user);
      mysqli_stmt_execute($snippetstmt);
      $snippetresult = mysqli_stmt_get_result($snippetstmt);
      if ($snippetresult) {
        $result = mysqli_fetch_assoc($snippetresult);
        mysqli_stmt_close($snippetstmt);
        if ($result) {
            $snippet_categoryid = $result['category_id'];
            $snippet_title = $result['title'];
            $snippet_description = $result['description'];
            $snippet_snippet = $result['snippet'];
        }
      } else {
        mysqli_stmt_close($snippetstmt);
        $queryerror = true;
      }
    } else {
      $queryerror = true;
    }
  }

  if ($badrequest) {
    http_response_code(400);
    $page_title = "Invalid snippet";
    $page_description = "The client tried to access an invalid snippet.";
  } elseif ($queryerror) {
    http_response_code(500);
    $page_title = "Problem with retrieving the snippet";
    $page_description = "An error occurred when retrieving the snippet.";
  } elseif (!$result) {
    http_response_code(404);
    $page_title = "Snippet not found";
    $page_description = "The snippet either doesn't currently exist or you don\'t have a permission to edit the snippet.";
  } else {
    $page_title = "Edit";
    $page_description = "Edit your snippet on AdmSnippet.";

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
            $stmt = mysqli_prepare($db, 'UPDATE snippets SET category_id = ?, title = ?, description = ?, snippet = ? WHERE id = ? AND user_id = ?;');
            if ($stmt) {
              mysqli_stmt_bind_param($stmt, 'isssii', $categoryid, $_POST['title'], $_POST['desc'], $_POST['content'], $snippetid, $user);
              $isexecsuccess = mysqli_stmt_execute($stmt);
              if (!$isexecsuccess) {
                mysqli_stmt_close($stmt);
                $error_message = 'An internal server error has occurred during the submission.';
              } else {
                $isupdated = mysqli_stmt_affected_rows($stmt) > 0;
                mysqli_stmt_close($stmt);
                if ($isupdated) {
                  header('Location: ' . (APP_ROOT . 'snippet.php?id=' . urlencode($snippetid)));
                  http_response_code(302);
                  include("includes/finalize.php");
                  exit;
                } else {
                  $error_message = 'An internal server error has occurred during the submission.';
                }
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
  }

?>
<?php include("includes/header.php"); ?>
<main class="page">
    <div class="container">
      <?php
      if ($badrequest) {
        echo '<h1>Invalid snippet</h1>
          <p>The snippet editing URL is invalid.</p>';
      } elseif ($queryerror) {
        echo '<h1>Problem with retrieving the snippet</h1>
          <p>An error occurred when retrieving the snippet.</p>';
      } elseif (!$result) {
        echo '<h1>Snippet not found</h1>
          <p>The snippet either doesn\'t currently exist or you don\'t have a permission to edit the snippet.</p>';
      } else {
        echo '<h1>Edit</h1>
        <form action="' . htmlspecialchars(APP_ROOT) .'edit.php" method="post" class="form-visible">
            <div class="form-element">
              <label for="title">Title:</label>
              <input type="text" name="title" id="title" value="' . htmlspecialchars($snippet_title) . '" required maxlength="255">
            </div>
            <div class="form-element">
              <label for="category">Category:</label>
              <select name="category" id="category">';
                  $categories = mysqli_query($db, 'SELECT id, name FROM categories ORDER BY id;');
                  if ($categories) {
                    while ($category = mysqli_fetch_assoc($categories)) {
                      echo '<option value="' . htmlspecialchars($category['id']) . '"' . ($category['id'] == $snippet_categoryid ? ' selected' : '') . '>' . htmlspecialchars($category['name']) . '</option>';
                    }
                  }
        echo '</select>
            </div>
            <div class="form-element form-full">
              <label for="desc">Description:</label>
              <input type="text" name="desc" id="desc" maxlength="8191" value="' . htmlspecialchars($snippet_description) . '">
            </div>
            <div class="form-element">
              <label for="content">Content:</label>
              <textarea name="content" id="content" class="form-code-textbox" required>' . htmlspecialchars($snippet_snippet) . '</textarea>
            </div>';
        if ($error_message) {
          echo '<p class="form-error">' . htmlspecialchars($error_message) . '</p>';
        }
        echo '<input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
              <input type="hidden" name="id" value="' . htmlspecialchars($snippetid) . '">
            <input type="submit" value="Submit" class="button">
        </form>';
      }
      ?>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
