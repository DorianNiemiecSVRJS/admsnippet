<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $userid = 0;
  $result = null;
  $badrequest = false;
  $queryerror = false;
  $entry = null;
  $username = null;
  $joined = null;
  $page_number = 1;
  if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page_number = intval($_GET['page']);
  }

  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $badrequest = true;
  } else {
    $userid = intval($_GET['id']);
    $userstmt = mysqli_prepare($db, 'SELECT name, joined FROM users WHERE id = ?');
    if ($userstmt) {
      mysqli_stmt_bind_param($userstmt, 'i', $userid);
      mysqli_stmt_execute($userstmt);
      $userresult = mysqli_stmt_get_result($userstmt);
      if ($userresult) {
        $result = mysqli_fetch_assoc($userresult);
        mysqli_stmt_close($userstmt);
        if ($result) {
            $username = $result['name'];
            $joined = date('F j, Y', strtotime($result['joined']));
        }
      } else {
        mysqli_stmt_close($userstmt);
        $queryerror = true;
      }
    } else {
      $queryerror = true;
    }
  }

  if ($badrequest) {
    http_response_code(400);
    $page_title = "Invalid user";
    $page_description = "The user URL is invalid.";
  } elseif ($queryerror) {
    http_response_code(500);
    $page_title = "Problem with retrieving the user";
    $page_description = "An error occurred when retrieving the user.";
  } elseif (!$username) {
    http_response_code(404);
    $page_title = "User not found";
    $page_description = "The user doesn't currently exist.";
  } else {
    $page_title = "User: $username";
    $page_description = "View profile for $username.";
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
  <div class="container">
<?php
  if ($badrequest) {
    echo '<h1>Invalid user</h1>
      <p>The user URL is invalid.</p>';
  } elseif ($queryerror) {
    echo '<h1>Problem with retrieving the user</h1>
      <p>An error occurred when retrieving the user.</p>';
  } elseif (!$result) {
    echo '<h1>User not found</h1>
      <p>The user doesn\'t currently exist.</p>';
  } else {
    echo '<h1>User: ' . htmlspecialchars($username) . '</h1>';
    echo '<p>Joined ' . htmlspecialchars($joined) . '</p>';
    if ($userid == $user) {
      echo '<div class="button-row">
        <form action="' . htmlspecialchars(APP_ROOT) . 'changepassword.php">
          <input type="submit" value="Change password" class="button">
        </form><form action="' . htmlspecialchars(APP_ROOT) . 'deletemyaccount.php">
          <input type="submit" value="Delete my account" class="button">
        </form>
      </div>';
    }
    echo '<h2 class="topsnippets-heading">User\'s snippets</h2>';
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
        WHERE snippets.user_id = ?
        ORDER BY snippets.date
        DESC LIMIT ' . strval(max(intval($page_number) - 1, 0) * 10) . ', 10;');
        if ($entrystmt) {
          mysqli_stmt_bind_param($entrystmt, 'iii', $user, $user, $userid);
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
          if ($entrystmt) mysqli_stmt_close($entrystmt);
          echo "<p>An error has occurred during retrieval of user's snippets!</p>";
        }
    echo '</div>';
    $qtystmt = mysqli_prepare($db, 'SELECT * FROM snippets WHERE user_id = ?;');
        if ($qtystmt) {
          mysqli_stmt_bind_param($qtystmt, 'i', $userid);
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
              echo '<a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars($userid) . '&page=' . htmlspecialchars($page_number - 1) . '">&lsaquo;</a>';
            }
            for ($i = 0; $i < 5 && $i < $maxpages; $i++) {
              $curpageno = $page_beg + $i;
              if ($curpageno == $page_number) {
                echo '<span class="pagination-active">' . htmlspecialchars($curpageno) . '</span>';
              } else {
                echo '<a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars($userid) . '&page=' . htmlspecialchars($curpageno) . '">' . htmlspecialchars($curpageno) . '</a>';
              }
            }
            if ($page_number < $maxpages) {
              echo '<a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars($userid) . '&page=' . htmlspecialchars($page_number + 1) . '">&rsaquo;</a>';
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
