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
   <title>Process meter readings</title>
   <meta name='viewport' content='width=device-width, initial-scale=1'>
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>

<div class="container card mt-4">

<h3 class="text-center mt-2">Process meter readings.</h3>

<div class="container pt-1">
   <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#"><?php echo $user['username']; ?></a>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
         <div class="navbar-nav">
            <a class="nav-item nav-link" href="default.php">Default page</a>
            <a class="nav-item nav-link" href="customer.php">Customer management</a>
            <a class="nav-item nav-link active" href="process_meter_readings.php">Process meter readings <span class="sr-only">(current)</span></a>
            <a class="nav-item nav-link" href="logout.php?logout=true">Logout</a>
         </div>
      </div>
   </nav>
</div>

<?php
// charge for usage ($0.124 per kWh) and also system access ($0.373 per day).
$chargeUsage = 0.124;
$systemAccess = 0.373;
// collection for removal
$meterreadings4Delete = [];

// Customers XML
$cfile = 'customers.xml';
$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->load($cfile);
$xpath = new DOMXPath($doc);


// Meter_readings XML
$mfile = 'meter_readings.xml';
$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->load($mfile);
$meterreadings = $dom->documentElement;


echo '<div class="container pt-3">';
echo '<p class="text-xl-left">Processed '. $meterreadings->childNodes->length . ' rows:</p>';
echo '</div>';
echo '<ul>';

// processing <meterreading>
foreach ($meterreadings->childNodes as $meterreading)
{
   $meterreadingDate = $meterreading->getAttribute('date');
   $meterreadingNumber = $meterreading->getElementsByTagName("number")->item(0)->nodeValue;
   $meterreadingReading = $meterreading->getElementsByTagName("reading")->item(0)->nodeValue;

   // echo "<li>$meterreadingDate - $meterreadingNumber - $meterreadingReading";
   echo "<li>Meter number - $meterreadingNumber";

   // Customers
   // search <customer> by <meternumber>
   $query = "/customers/customer[meternumber='$meterreadingNumber']";
   $customer = $xpath->query($query);
   echo '<ul><li>Customer number - ' . $customer->item(0)->getAttribute('number');

   // find Last payment date
   $payments = [];
   foreach ($customer->item(0)->getElementsByTagName('payment') as $payment) {
      $date = $payment->getAttribute('date');
      $date = date_create_from_format('d/m/Y', $date);
      $payments[date_timestamp_get($date)] = $payment;
   }
   // get last payment date
   $max = max(array_keys($payments));

   // get payment object with last last payment date
   $nodePayment = $payments[$max];

   // Calculation of amount due

   // get last payment date
   $date = $nodePayment->getAttribute('date');
   echo '<ul><li>Last payment date - ' . $date . '</li>';

   echo '<li>Meterreading date - ' . $meterreadingDate . '</li>';

   // get reading
   $reading = $nodePayment->getElementsByTagName('reading')->item(0)->nodeValue;

   // calculate days
   $interval = date_diff(date_create_from_format('d/m/Y', $date), date_create_from_format('d/m/Y', $meterreadingDate));
   $days = $interval->format('%a');
   echo '<li>' . $days . ' days</li>';

   if ($days == 0) {
      echo '</ul></li></ul></li>';
      continue;
   }

   $read = $meterreadingReading - $reading;
   echo '<li>' . $read . ' kWh</li><li>';

   $charge = $chargeUsage * $read;
   $access = $days * $systemAccess;
   $amountdue = $access + $charge;
   // In addition a 10% GST charge is added to the total of the bill
   $amountdue *= 1.10;
   $amountdue = round($amountdue, 2);

   echo '$' . $amountdue;
   echo '</li></ul>';
   echo '</li></ul>';
   // /customers

   echo "</li>";

   // Create <payment> node
   $payment = $doc->createElement("payment");
   $payment->setAttribute('date', $meterreadingDate);
   // add <payment> to <customer>
   $customer->item(0)->appendChild($payment);
   $pnode = $doc->createElement('reading', $read);
   $payment->appendChild($pnode);
   $pnode = $doc->createElement('amountdue', $amountdue);
   $payment->appendChild($pnode);
   //

   // collect node for deleting
   $meterreadings4Delete[] = $meterreading;
}

// save customers.xml
$doc->formatOutput = true;
$doc->save($cfile);

// When the readings in meter_readings.xml have been processed, the readings should be deleted before saving the xml file.
foreach($meterreadings4Delete as $meterreading)
   $meterreadings->removeChild($meterreading);

// save meter_readings.xml
$dom->formatOutput = true;
$dom->save($mfile);

?>
</ul>

</div>

</body>
</html>