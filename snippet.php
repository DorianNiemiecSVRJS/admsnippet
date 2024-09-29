<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $highlight_code = true;
  $snippetid = 0;
  $badrequest = false;
  $queryerror = false;
  $entry = null;
  $result = null;
  
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $badrequest = true;
  } else {
    $snippetid = intval($_GET['id']);
    $entrystmt = mysqli_prepare($db, 'SELECT snippets.id AS "id",
          snippets.title AS "name",
          snippets.category_id AS "category_id",
          categories.name AS "category",
          snippets.user_id AS "user_id",
          users.name AS "user",
          snippets.date AS "date",
          snippets.description AS "description",
          snippets.snippet AS "snippet",
          (SELECT COUNT(*) FROM votes WHERE votes.snippet_id = snippets.id AND votes.is_downvote = 0)
          - (SELECT COUNT(*) FROM votes WHERE votes.snippet_id = snippets.id AND votes.is_downvote = 1) AS "votes",
          IFNULL((SELECT (is_downvote = 0) FROM votes WHERE votes.snippet_id = snippets.id AND votes.user_id = ? AND votes.user_id <> -1 AND is_downvote = 0), 0) AS "upvoted",
          IFNULL((SELECT (is_downvote = 1) FROM votes WHERE votes.snippet_id = snippets.id AND votes.user_id = ? AND votes.user_id <> -1 AND is_downvote = 1), 0) AS "downvoted"
          FROM snippets
          INNER JOIN categories
          ON snippets.category_id = categories.id
          INNER JOIN users
          ON snippets.user_id = users.id
          WHERE snippets.id = ?');
    if ($entrystmt) {
      mysqli_stmt_bind_param($entrystmt, 'iii', $user, $user, $snippetid);
      mysqli_stmt_execute($entrystmt);
      $entryresult = mysqli_stmt_get_result($entrystmt);
      if ($entryresult) {
        $result = mysqli_fetch_assoc($entryresult);
        mysqli_stmt_close($entrystmt);
      } else {
        mysqli_stmt_close($entrystmt);
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
    $page_description = "The snippet doesn't currently exist.";
  } else {
    $page_title = $result['name'];
    $page_description = $result['description'] ? $result['description'] : ("View the " . $result['name'] . " snippet.");
  }
?>
<?php include("includes/header.php"); ?>
<main class="page">
  <div class="container">
<?php
  if ($badrequest) {
    echo '<h1>Invalid snippet</h1>
      <p>The snippet URL is invalid.</p>';
  } elseif ($queryerror) {
    echo '<h1>Problem with retrieving the snippet</h1>
      <p>An error occurred when retrieving the snippet.</p>';
  } elseif (!$result) {
    echo '<h1>Snippet not found</h1>
      <p>The snippet doesn\'t currently exist.</p>';
  } else {
    echo '<div class="entries-inside">
  <div class="entry-outside">
  <div class="entry-votes">
    <form action="' . htmlspecialchars(APP_ROOT) . 'vote.php" method="post">
      <input type="submit" value="&#9650;" class="entry-vote-button' . ($result['upvoted'] ? ' entry-vote-active' : '') . '" title="Upvote">
      <input type="hidden" name="action" value="' . (!$result['upvoted'] ? 'up' : 'reset') . '">
      <input type="hidden" name="id" value="' . htmlspecialchars($result['id']) . '">
      <input type="hidden" name="redirect" value="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
      <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
    </form>
    <span class="entry-vote-count">' . htmlspecialchars($result['votes']) . '</span>
    <form action="' . htmlspecialchars(APP_ROOT) . 'vote.php" method="post">
      <input type="submit" value="&#9660;" class="entry-vote-button' . ($result['downvoted'] ? ' entry-vote-active' : '') . '" title="Downvote">
      <input type="hidden" name="action" value="' . (!$result['downvoted'] ? 'down' : 'reset') . '">
      <input type="hidden" name="id" value="' . htmlspecialchars($result['id']) . '">
      <input type="hidden" name="redirect" value="' . htmlspecialchars($_SERVER['REQUEST_URI']) . '">
      <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
    </form>
  </div>
  <div class="entry-body">
    <h1>' . htmlspecialchars($result['name']) . '</h1>
    <p><a href="' . htmlspecialchars(APP_ROOT) . 'category.php?id=' . htmlspecialchars($result['category_id']) . '">' . htmlspecialchars($result['category']) . '</a>&nbsp;|
    by <a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars($result['user_id']) . '">' . htmlspecialchars($result['user']) . '</a>&nbsp;|
    submitted in ' . htmlspecialchars(date('F j, Y', strtotime($result['date']))) . '</p>
    <p>' . htmlspecialchars($result['description']) . '</p>
    <div class="button-row">
    <a href="' . htmlspecialchars(APP_ROOT) . 'raw.php?id=' . htmlspecialchars($result['id']) . '" class="button">View raw</a>' . 
    ($result['user_id'] == $user ? '<form action="' . htmlspecialchars(APP_ROOT) . 'delete.php" method="post">
      <input type="submit" value="Delete" class="button">
      <input type="hidden" name="id" value="' . htmlspecialchars($result['id']) . '">
      <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
    </form><form action="' . htmlspecialchars(APP_ROOT) . 'edit.php">
      <input type="submit" value="Edit" class="button">
      <input type="hidden" name="id" value="' . htmlspecialchars($result['id']) . '">
    </form>' : "" ) . '
    </div>
  </div>
</div>
</div>
<pre class="entry-contents"><code id="snippet">' . htmlspecialchars($result['snippet']) . '</code></pre>';
  }
?>
  </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
