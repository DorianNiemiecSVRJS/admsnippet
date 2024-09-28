<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $categoryid = 0;
  $categoryname = null;
  $badrequest = false;
  $queryerror = false;
  $entry = null;
  $page_number = 1;
  if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page_number = intval($_GET['page']);
  }

  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $badrequest = true;
  } else {
    $categoryid = intval($_GET['id']);
    $categorystmt = mysqli_prepare($db, 'SELECT name FROM categories WHERE id = ?');
    if ($categorystmt) {
      mysqli_stmt_bind_param($categorystmt, 'i', $categoryid);
      mysqli_stmt_execute($categorystmt);
      $categoryresult = mysqli_stmt_get_result($categorystmt);
      if ($categoryresult) {
        $result = mysqli_fetch_assoc($categoryresult);
        if ($result) {
            $categoryname = $result['name'];
        }
      } else {
        $queryerror = true;
      }
      mysqli_stmt_close($categorystmt);
    } else {
      $queryerror = true;
    }
  }

  if ($badrequest) {
    http_response_code(400);
    $page_title = "Invalid category";
    $page_description = "The category URL is invalid.";
  } elseif ($queryerror) {
    http_response_code(500);
    $page_title = "Problem with retrieving the category";
    $page_description = "An error occurred when retrieving the category.";
  } elseif (!$categoryname) {
    http_response_code(404);
    $page_title = "Category not found";
    $page_description = "The category doesn't currently exist.";
  } else {
    $page_title = "Category: $categoryname";
    $page_description = "Explore various snippets from $categoryname category on AdmSnippet";
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
  <div class="container">
  <?php
    if ($badrequest) {
      echo '<h1>Invalid category</h1>
        <p>The category URL is invalid.</p>';
    } elseif ($queryerror) {
      echo '<h1>Problem with retrieving the category</h1>
        <p>An error occurred when retrieving the category.</p>';
    } elseif (!$result) {
      echo '<h1>Category not found</h1>
        <p>The category doesn\'t currently exist.</p>';
    } else {
    echo '<h1>Category: ' . htmlentities($categoryname) . '</h1>
    <form class="search-form" action="' . htmlspecialchars(APP_ROOT) . 'search.php">
        <input type="text" name="q" class="search-input">
        <input type="hidden" name="category" value="' . htmlentities($categoryid) . '">
        <input type="submit" value="Search" class="search-button">
      </form>';
    echo '<div class="entries-outside">';
    $entries = null;
        $entrystmt = mysqli_prepare($db, 'SELECT snippets.id AS "id",
        snippets.title AS "name",
        snippets.category_id AS "category_id",
        categories.name AS "category",
        snippets.user_id AS "user_id",
        users.name AS "user",
        snippets.date AS "date",
        snippets.description AS "description",
        (SELECT COUNT(*) FROM votes WHERE votes.snippet_id = snippets.id AND votes.is_downvote = 0)
        - (SELECT COUNT(*) FROM votes WHERE votes.snippet_id = snippets.id AND votes.is_downvote = 1) AS "votes",
        IFNULL((SELECT (is_downvote = 0) FROM votes WHERE votes.snippet_id = snippets.id AND votes.user_id = ? AND votes.user_id <> -1 AND is_downvote = 0), 0) AS "upvoted",
        IFNULL((SELECT (is_downvote = 1) FROM votes WHERE votes.snippet_id = snippets.id AND votes.user_id = ? AND votes.user_id <> -1 AND is_downvote = 1), 0) AS "downvoted"
        FROM snippets
        INNER JOIN categories
        ON snippets.category_id = categories.id
        INNER JOIN users
        ON snippets.user_id = users.id
        WHERE snippets.category_id = ?
        ORDER BY (votes / 5) - ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(snippets.date)) / 86400)
        DESC LIMIT ' . strval((intval($page_number) - 1) * 10) . ', 10;');
        if ($entrystmt) {
          mysqli_stmt_bind_param($entrystmt, 'iii', $user, $user, $categoryid);
          mysqli_stmt_execute($entrystmt);
          $entries = mysqli_stmt_get_result($entrystmt);
        }
        if ($entries) {
          $entries_present = false;
          while ($entry = mysqli_fetch_assoc($entries)) {
            $entries_present = true;
            echo '<div class="entry-outside">
              <div class="entry-votes">
                <form action="' . htmlspecialchars(APP_ROOT) . 'vote.php" method="post">
                  <input type="submit" value="&#9650;" class="entry-vote-button' . ($entry['upvoted'] ? ' entry-vote-active' : '') . '" title="Upvote">
                  <input type="hidden" name="action" value="' . (!$entry['upvoted'] ? 'up' : 'reset') . '">
                  <input type="hidden" name="id" value="' . htmlspecialchars($entry['id']) . '">
                  <input type="hidden" name="redirect" value="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
                  <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
                </form>
                <span class="entry-vote-count">' . htmlspecialchars($entry['votes']) . '</span>
                <form action="' . htmlspecialchars(APP_ROOT) . 'vote.php" method="post">
                  <input type="submit" value="&#9660;" class="entry-vote-button' . ($entry['downvoted'] ? ' entry-vote-active' : '') . '" title="Downvote">
                  <input type="hidden" name="action" value="' . (!$entry['downvoted'] ? 'down' : 'reset') . '">
                  <input type="hidden" name="id" value="' . htmlspecialchars($entry['id']) . '">
                  <input type="hidden" name="redirect" value="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
                  <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
                </form>
              </div>
              <div class="entry-body">
                ' . ($entry['user_id'] == $user ? '<form action="' . htmlspecialchars(APP_ROOT) . 'delete.php" method="post" class="entry-action">
                  <input type="submit" value="Delete" class="button">
                  <input type="hidden" name="id" value="' . htmlspecialchars($entry['id']) . '">
                  <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
                </form><form action="' . htmlspecialchars(APP_ROOT) . 'edit.php" class="entry-action">
                  <input type="submit" value="Edit" class="button">
                  <input type="hidden" name="id" value="' . htmlspecialchars($entry['id']) . '">
                </form>' : '') . '
                <h3><a href="' . htmlspecialchars(APP_ROOT) . 'snippet.php?id=' . htmlspecialchars($entry['id']) . '">' . htmlspecialchars($entry['name']) . '</a></h3>
                <p><a href="' . htmlspecialchars(APP_ROOT) . 'category.php?id=' . htmlspecialchars($entry['category_id']) . '">' . htmlspecialchars($entry['category']) . '</a>&nbsp;|
                by <a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars($entry['user_id']) . '">' . htmlspecialchars($entry['user']) . '</a>&nbsp;|
                submitted in ' . htmlspecialchars(date('F j, Y', strtotime($entry['date']))) . '</p>
                <p>' . htmlspecialchars($entry['description']) . '</p>
              </div>
            </div>';
          }
          if (!$entries_present) echo "<p>No snippets.</p>";
          mysqli_stmt_close($entrystmt);
        } else {
          echo "<p>An error has occurred during retrieval of user's snippets!</p>";
        }
    echo '</div>';
    $qtystmt = mysqli_prepare($db, 'SELECT * FROM snippets WHERE category_id = ?;');
        if ($qtystmt) {
          mysqli_stmt_bind_param($qtystmt, 'i', $categoryid);
          mysqli_stmt_execute($qtystmt);
          mysqli_stmt_store_result($qtystmt);
          $qty = intval(mysqli_stmt_num_rows($qtystmt));
          $maxpages = ceil($qty / 10);

          $page_beg = $page_number - 2;
          $page_end = $page_number + 2;
          if ($page_end > $maxpages) {
            $page_beg -= $page_end - $maxpages;
            $page_end = $maxpages;
          }
          if ($page_beg < 1) {
            $page_end += 1 - $page_beg;
            $page_beg = 1;
          }

          if ($maxpages > 1) {
            echo '<div class="pagination">';
            if ($page_number > 1) {
              echo '<a href="' . htmlspecialchars(APP_ROOT) . 'explore.php?page=' . htmlspecialchars($page_number - 1) . '">&lsaquo;</a>';
            }
            for ($i = 0; $i < 5 && $i < $maxpages; $i++) {
              $curpageno = $page_beg + $i;
              if ($curpageno == $page_number) {
                echo '<span class="pagination-active">' . htmlspecialchars($curpageno) . '</span>';
              } else {
                echo '<a href="' . htmlspecialchars(APP_ROOT) . 'explore.php?page=' . htmlspecialchars($curpageno) . '">' . htmlspecialchars($curpageno) . '</a>';
              }
            }
            if ($page_number < $maxpages) {
              echo '<a href="' . htmlspecialchars(APP_ROOT) . 'explore.php?page=' . htmlspecialchars($page_number + 1) . '">&rsaquo;</a>';
            }
            echo '</div>';
          }
          mysqli_stmt_close($qtystmt);
        }
      }
?>
  </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
