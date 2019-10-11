<?php
// include shared libraries
require_once 'shared.libs.php';

// start session for autorization
session_start();

// if user not logged
$user = userAuthentication();
if ($user === false) {
   header("Location: login.php", 302);
   exit();
}
?>
<!DOCTYPE html>
<html>

<head>
   <meta charset='utf-8'>
   <meta http-equiv='X-UA-Compatible' content='IE=edge'>
   <title>Default page</title>
   <meta name='viewport' content='width=device-width, initial-scale=1'>
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
   <style>
      li {
         padding: .3em;
      }
   </style>
</head>

<body>

   <div class="container card mt-4">
      <div class="container pt-4">
         <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#"><?php echo $user['username']; ?></a>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
               <div class="navbar-nav">
                  <a class="nav-item nav-link active" href="default.php">Default page <span class="sr-only">(current)</span></a>
                  <a class="nav-item nav-link" href="customer.php">Customer management</a>
                  <a class="nav-item nav-link" href="process_meter_readings.php">Process meter readings</a>
                  <a class="nav-item nav-link" href="logout.php?logout=true">Logout</a>
               </div>
            </div>
         </nav>
      </div>

      <div class="container mt-4">
         <p class="h5">For this server side assignment, you will need to implement the following functionality:</p>
         <ol class="m-3">
            <li>Login to the system for staff</li>
            <li>Process meter readings</li>
            <li>Customer management:
               <ol>
                  <li>Add a new customer</li>
                  <li>Update/Change customer details</li>
               </ol>
         </ol>
      </div>
   </div>

</body>

</html>