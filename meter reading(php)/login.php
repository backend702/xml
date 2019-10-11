<?php
// include shared libraries
require_once 'shared.libs.php';

// start session for autorization
session_start();

// if user already currently logged
$user = userAuthentication();
if ($user !== false) {
   header("Location: default.php", 302);
   exit();
}

// if login process start
if (!empty($_POST)) {
   // satinize input
   $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
   $username = $post['username'] ?? null;
   $password = $post['password'] ?? null;
   $hash = md5($username . $password);

   $auth = userCredentials();

   // the username and password for a staff member must not be the same sequence of character, they should be different.
   if ($username == $password) {
      $message = "Note that the username and password for a staff member must not be the same sequence of character, they should be different.";
   }
   // Login successfull
   elseif (array_key_exists($hash, $auth)) {
      // set session auth token
      $_SESSION['auth_token'] = $hash;
      // redirect to default page
      header("Location: default.php", 302);
      exit();
   }
   // Login error
   else {
      $message = "The username or password is incorrect!";
   }
}

?>
<!DOCTYPE html>
<html>

<head>
   <meta charset='utf-8'>
   <meta http-equiv='X-UA-Compatible' content='IE=edge'>
   <title>Login page</title>
   <meta name='viewport' content='width=device-width, initial-scale=1'>
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>

<body>
   <!-- https://codepen.io/progeja/pen/braRdW -->

   <h1 class="text-center">For Energetic Energy’s staff only.</h1>

   <?php if (isset($message)) : ?>
      <div class="container col-md-4">
         <div class="alert alert-warning">
            <strong>Warning!</strong> <?php echo $message; ?>
         </div>
      </div>
   <?php endif; ?>

   <div class="container pt-3">
      <div class="row justify-content-sm-center">
         <div class="col-sm-6 col-md-4">
            <div class="card border-info text-center">
               <div class="card-header">
                  Sign in to continue
               </div>
               <div class="card-body">
                  <img src="back.jpg" width="128" height="128">
                  <h4 class="text-center">Energetic Energy’s</h4>
                  <form class="form-signin" method="post" action="">
                     <input type="text" name="username" class="form-control mb-2" placeholder="Username" value="" required autofocus>
                     <input type="password" name="password" class="form-control mb-2" placeholder="Password" value="" required>
                     <button class="btn btn-lg btn-primary btn-block mb-1" type="submit">Sign in</button>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>

</body>

</html>