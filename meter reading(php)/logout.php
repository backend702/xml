<?php
session_start();

if( isset($_GET['logout']) && $_GET['logout'] == 'true' ) {
   session_unset();
}
// redirect to defaut login page anyway
header("Location: login.php", 302);
