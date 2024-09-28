<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php
  $userid = 0;
  $page_number = 1;
  if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page_number = intval($_GET['page']);
  }

  $query = '';
  if (isset($_GET['q'])) {
    $query = $_GET['q'];
  }

  $categoryid = -1;
  if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $categoryid = intval($_GET['category']);
    if ($categoryid < 0) $categoryid = -1;
  }

  $page_title = $categoryid > -1 ? "Category search" : "Search";
  $page_description = "Search various snippets on AdmSnippet";
?>
<?php include("includes/header.php"); ?>
<main class="page">
  <div class="container">
    <h1><?php echo $categoryid > -1 ? "Category search" : "Search"; ?></h1>
    <form class="search-form" action="<?php echo htmlspecialchars(APP_ROOT); ?>search.php">
        <input type="text" name="q" class="search-input">
        <input type="submit" value="Search" class="search-button">
        <?php
          if ($categoryid > -1) {
            echo '<input type="hidden" name="category" value="' . htmlspecialchars($categoryid) . '">';
          }
        ?>
      </form>
<?php
  if ($query) {
    echo '<div class="entries-outside">';
    $querylike = '%' . str_replace(array('!','%','_','['), array('!!','!%','!_', '!['), $query) . '%';
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
        IFNULL((SELECT (is_downvote = 1) FROM votes WHERE votes.snippet_id = snippets.id AND votes.user_id = ? AND votes.user_id <> -1 AND is_downvote = 1), 0) AS "downvoted",
        MATCH (snippets.title, snippets.description) AGAINST (? IN NATURAL LANGUAGE MODE) AS "score"
        FROM snippets
        INNER JOIN categories
        ON snippets.category_id = categories.id
        INNER JOIN users
        ON snippets.user_id = users.id
        HAVING (score > 0
        OR snippets.title LIKE ?
        OR snippets.description LIKE ?)
        AND (snippets.category_id = ?
        OR ? = -1)
        ORDER BY (votes / 5) - ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(snippets.date)) / 86400)
        DESC, score DESC
        LIMIT ' . strval(max(intval($page_number) - 1, 0) * 10) . ', 10;');
        if ($entrystmt) {
          mysqli_stmt_bind_param($entrystmt, 'iisssii', $user, $user, $query, $querylike, $querylike, $categoryid, $categoryid);
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
          if (!$entries_present) echo "<p>No snippets found matching the <b>&ldquo;" . htmlspecialchars($query) . "&rdquo;</b> query.</p>
            <ul>
              <li>Check your search query</li>
              <li>Try searching different keywords</li>
              <li>Try replacing some keywords with more general ones</li>
              " . ($categoryid > -1 ? '<li>Try using <a href="' . htmlentities(APP_ROOT) . 'search.php?q=' . htmlentities(urlencode($query)) . '">general search.</a></li>': '') . "
            </ul>";
          mysqli_stmt_close($entrystmt);
        } else {
          if ($entrystmt) mysqli_stmt_close($entrystmt);
          echo "<p>An error has occurred when searching snippets!</p>";
        }
    echo '</div>';
    $qtystmt = mysqli_prepare($db, 'SELECT * FROM snippets
    WHERE (MATCH (snippets.title, snippets.description) AGAINST (? IN NATURAL LANGUAGE MODE)
    OR snippets.title LIKE ?
    OR snippets.description LIKE ?)
    AND (snippets.category_id = ?
    OR ? = -1);');
        if ($qtystmt) {
          mysqli_stmt_bind_param($qtystmt, 'sssii', $query, $querylike, $querylike, $categoryid, $categoryid);
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
        }
  } else {
    echo '<br>';
  }
?>
  </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
