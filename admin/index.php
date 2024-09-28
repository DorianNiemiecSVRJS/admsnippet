<?php define("ADMSNIPPET", null); ?>
<?php include("../config.php"); ?>
<?php include("../includes/setup.php"); ?>
<?php include("../includes/admincheck.php"); ?>
<?php
  $page_title = "Administration panel";
  $page_description = "The administration panel for AdmSnippet";
?>
<?php include("../includes/header.php"); ?>
<main class="page">
  <div class="container">
    <h1>Administration panel</h1>
    <p>Welcome to the administration panel of AdmSnippet! Below you can go to specific administration panel sections.</p>
    <div class="button-row">
      <a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/admins.php" class="button">Manage administrators</a>
      <a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/categories.php" class="button">Manage categories</a>
      <a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/deletesnippets.php" class="button">Delete snippets</a>
      <a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/deleteusers.php" class="button">Delete users</a>
      <a href="<?php echo htmlspecialchars(APP_ROOT); ?>admin/statistics.php" class="button">View statistics</a>
    </div>
    <br>
  </div>
</main>
<?php include("../includes/footer.php"); ?>
<?php include("../includes/finalize.php"); ?>