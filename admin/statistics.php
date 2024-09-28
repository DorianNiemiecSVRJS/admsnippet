<?php define("ADMSNIPPET", null); ?>
<?php include("../config.php"); ?>
<?php include("../includes/setup.php"); ?>
<?php include("../includes/admincheck.php"); ?>
<?php
  $page_title = "Statistics";
  $page_description = "View the statistics for AdmSnippet";
?>
<?php include("../includes/header.php"); ?>
<main class="page">
  <div class="container">
    <h1>Statistics</h1>
    <p><a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/">Return to the administration panel</a></p>
    <ul>
    <?php
      $totalusers = mysqli_query($db, 'SELECT COUNT(*) AS "count" FROM users;');
      if ($totalusers) {
        $totalusersrow = mysqli_fetch_assoc($totalusers);
        echo '<li><b>Total users:</b> ' . htmlspecialchars($totalusersrow['count']) . '</li>';
      } else {
        echo '<li><b>Can\'t get the total number of users!</b></li>';
      }
      $totalsnippets = mysqli_query($db, 'SELECT COUNT(*) AS "count" FROM snippets;');
      if ($totalsnippets) {
        $totalsnippetsrow = mysqli_fetch_assoc($totalsnippets);
        echo '<li><b>Total snippets:</b> ' . htmlspecialchars($totalsnippetsrow['count']) . '</li>';
      } else {
        echo '<li><b>Can\'t get the total number of snippets!</b></li>';
      }
    ?>
    </ul>
  </div>
</main>
<?php include("../includes/footer.php"); ?>
<?php include("../includes/finalize.php"); ?>