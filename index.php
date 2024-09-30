<?php define("ADMSNIPPET", null); ?>
<?php include("config.php"); ?>
<?php include("includes/setup.php"); ?>
<?php include("includes/header.php"); ?>
<section class="hero">
  <div class="container">
    <h1>Find server administration scripts and configurations</h1>
    <p>AdmSnippet is a database of user-submitted server administration scripts and configuratin files, which allows server administrators to easily find scripts for their server administration needs.</p>
    <!-- Remove the paragraph below after launching AdmSnippet on product discovery platforms -->
    <p class="hero-launches">
      <a href="https://www.producthunt.com/posts/admsnippet?embed=true&utm_source=badge-featured&utm_medium=badge&utm_souce=badge-admsnippet" target="_blank"><img src="https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=492792&theme=light" alt="AdmSnippet - a&#0032;database&#0032;of&#0032;user&#0045;submitted&#0032;server&#0032;administration&#0032;scripts | Product Hunt" width="250" height="54" /></a>
    </p>
  </div>
</section>
<main>
    <div class="container">
      <form class="search-form" action="<?php echo htmlspecialchars(APP_ROOT); ?>search.php">
        <input type="text" name="q" class="search-input">
        <input type="submit" value="Search" class="search-button">
      </form>
      <h2 class="topsnippets-heading">Top 10 snippets</h2>
      <div class="entries-outside">
      <?php
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
        ORDER BY (votes / 5) - ((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(snippets.date)) / 86400)
        DESC LIMIT 10;');
        if ($entrystmt) {
          mysqli_stmt_bind_param($entrystmt, 'ii', $user, $user);
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
          echo "<p>An error has occurred during retrieval of top snippets!</p>";
        }
      ?>
      </div>
    </div>
</main>
<?php include("includes/footer.php"); ?>
<?php include("includes/finalize.php"); ?>
