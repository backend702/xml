<?php
/**
 * parse password file and create associative array with key => MD5($userName . $userPasswd)
 * 
 * @return array
 */
function userCredentials()
{
   $file = 'staff.xml';
   $auth = [];

   // read XML file with user credentials
   $doc = new DOMDocument();
   $doc->preserveWhiteSpace = false;
   $doc->load($file);
   $users = $doc->getElementsByTagName('user');
   foreach ($users as $user) {
      $userName = $user->getElementsByTagName('username')->item(0)->nodeValue;
      $userPasswd = $user->getElementsByTagName('password')->item(0)->nodeValue;

      // create associative array with key => MD5($userName . $userPasswd)
      $auth[md5($userName . $userPasswd)] = [
         'username' => $userName,
         'password' => $userPasswd,
      ];
   }
   return $auth;
}

/**
 * authentificate user
 * 
 * @return array or bool
 */
function userAuthentication()
{
   // if exist session Auth Token
   if (isset($_SESSION['auth_token'])) {
      $auth = userCredentials();
      $hash = $_SESSION['auth_token'];
      // if token valid
      if (array_key_exists($hash, $auth)) {
         return $auth[$hash];
      }
   }
   return false;
}
