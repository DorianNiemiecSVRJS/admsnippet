<?php
  // MySQL/MariaDB configuration
  define('MYSQL_HOST', "localhost");
  define('MYSQL_USER', "adminsnippet");
  define('MYSQL_PASS', "adminsnippet");
  define('MYSQL_DB', "adminsnippet");

  // Root URL for AdmSnippet
  define('APP_ROOT', '/');

  // Questions for registration CAPTCHA. Below are the sample CAPTCHA questions
  define('CAPTCHA_QUESTIONS', [
    'What is the capital of France?' => 'Paris'
    'What is the first or the last letter of "AdmSnippet"?' => [
      'A',
      't'
    ]
  ]);
?>
