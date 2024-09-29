<?php if (!defined('ADMSNIPPET')) die; ?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(APP_ROOT); ?>css/style.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(APP_ROOT); ?>css/cookieconsent.min.css">
    <title><?php echo htmlspecialchars((isset($page_title) && $page_title) ? "$page_title - AdmSnippet" : "AdmSnippet") ?></title>
    <meta name="description" content="<?php echo htmlspecialchars((isset($page_description) && $page_description) ? $page_description : "AdmSnippet is a database of user-submitted server administration scripts and configuration files, which allows server administrators to easily find scripts for their server administration needs.") ?>">
    <meta name="og:title" content=" <?php echo htmlspecialchars((isset($page_title) && $page_title) ? "$page_title - AdmSnippet" : "AdmSnippet") ?>">
    <meta name="og:description" content="<?php echo htmlspecialchars((isset($page_description) && $page_description) ? $page_description : "AdmSnippet is a database of user-submitted server administration scripts and configuration files, which allows server administrators to easily find scripts for their server administration needs.") ?>">
    <meta name="og:url" content="<?php echo htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'localhost')) . $_SERVER['REQUEST_URI']); ?>">
    <meta name="og:image" content="<?php echo htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'localhost')) . APP_ROOT . 'img/cover.png'); ?>">
    <meta name="og:image:width" content="1920">
    <meta name="og:image:height" content="1080">
    <meta name="og:image:alt" content="<?php echo htmlspecialchars((isset($page_title) && $page_title) ? "$page_title - AdmSnippet" : "AdmSnippet") ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content=" <?php echo htmlspecialchars((isset($page_title) && $page_title) ? "$page_title - AdmSnippet" : "AdmSnippet") ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars((isset($page_description) && $page_description) ? $page_description : "AdmSnippet is a database of user-submitted server administration scripts and configuration files, which allows server administrators to easily find scripts for their server administration needs.") ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'localhost')) . APP_ROOT . 'img/cover.png'); ?>">
    <!--[if lt IE 9]><script src="<?php echo htmlspecialchars(APP_ROOT . 'js/html5shiv.js'); ?>"></script><![endif]-->
    <script src="<?php echo htmlspecialchars(APP_ROOT . 'js/analytics.js'); ?>"></script>
    <?php
      if ($highlight_code) {
        echo '<script src="' . htmlspecialchars(APP_ROOT . 'js/highlight.min.js') . '"></script>
        <link rel="stylesheet" href="' . htmlspecialchars(APP_ROOT) . 'css/highlight.min.css">';
      }
    ?>
</head>
<body>
    <header>
        <div class="container">
          <div class="header-container">
            <span class="header-sitename"><a href="/">AdmSnippet</a></span>
            <nav>
               <ul>
                    <?php
                      echo '<li><a href="' . htmlspecialchars(APP_ROOT) . 'explore.php">Explore</a></li>';
                      if ($user == -1) {
                        echo '<li><a href="' . htmlspecialchars(APP_ROOT) . 'login.php?redirect=' . htmlspecialchars(urlencode($_SERVER['REQUEST_URI'])) . '">Login</a></li>';
                        echo '<li><a href="' . htmlspecialchars(APP_ROOT) . 'register.php?redirect=' . htmlspecialchars(urlencode($_SERVER['REQUEST_URI'])) . '">Register</a></li>';
                      } else {
                        echo '<li><a href="' . htmlspecialchars(APP_ROOT) . 'submit.php">Submit</a></li>';
                        echo '<li><a href="' . htmlspecialchars(APP_ROOT) . 'user.php?id=' . htmlspecialchars(urlencode($user)) . '">Profile</a></li>';
                        echo '<li><form action="' . htmlspecialchars(APP_ROOT) . 'logout.php?redirect=' . htmlspecialchars(urlencode($_SERVER['REQUEST_URI'])) . '" method="post">
                                <input type="hidden" name="csrf" value="' . htmlspecialchars($csrf_token) . '">
                                <input type="submit" value="Logout">
                              </form></li>
                        ';
                      }
                    ?>
               </ul>
            </nav>
          </div>
        </div>
    </header>
