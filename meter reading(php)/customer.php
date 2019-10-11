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

// Load Customers XML
$cfile = 'customers.xml';
$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->load($cfile);

// if SAVE data
if(isset($_POST) && count($_POST)) {
   // satinize INPUT
   $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

   // if CREATE
   if (isset($post['method']) && $post['method'] == 'post') {
      // set message for user
      if(false !== ($result = customerCreate($post, $doc, $cfile))) {
         $_SESSION['flash']['message'] = 'Success! Customer successfully created!';
         $_SESSION['flash']['alert'] = 'success';
      }
   }
   // if UPDATE
   elseif(isset($post['method']) && $post['method'] == 'put') {
      if(false !== ($result = customerUpdate($post, $doc, $cfile))) {
         $_SESSION['flash']['message'] = 'Success! Customer successfully changed!';
         $_SESSION['flash']['alert'] = 'success';
      }
   }
   // if ERROR
   if ($result === false) {
         $_SESSION['flash']['message'] = 'Error! An error has occurred while processing your request!';
         $_SESSION['flash']['alert'] = 'danger';
   }
   header("Location: {$_SERVER['PHP_SELF']}", 302);
   exit();
}
?>
<!DOCTYPE html>
<html>

<head>
   <meta charset='utf-8'>
   <meta http-equiv='X-UA-Compatible' content='IE=edge'>
   <title>Customer management</title>
   <meta name='viewport' content='width=device-width, initial-scale=1'>
   <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
   <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>

<div class="container card mt-4">
   <h3 class="text-center mt-2">Customer management</h3>

   <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#"><?php echo $user['username']; ?></a>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
         <div class="navbar-nav">
            <a class="nav-item nav-link" href="default.php">Default page</a>
            <a class="nav-item nav-link active" href="customer.php">Customer management <span class="sr-only">(current)</span></a>
            <a class="nav-item nav-link" href="process_meter_readings.php">Process meter readings</a>
            <a class="nav-item nav-link" href="logout.php?logout=true">Logout</a>
         </div>
      </div>
      <div class="navbar-nav navbar-nav ml-auto">
         <a class="btn btn-outline-success mr-sm-4" href="?add=true" role="button" title="Add new customer">
            <i class="fa fa-user-plus"></i>
         </a>
         <form class="form-inline">
         <input class="form-control mr-sm-0" type="search" name="search" placeholder="Customer search" value="<?php echo htmlspecialchars($_GET['search']??'');?>">
            <div class="input-group-append">
               <button class="btn btn-secondary" type="submit" title="Search customer">
                  <i class="fa fa-search"></i>
               </button>
            </div>
         </form>
      </div>
   </nav>

<?php if (isset($_SESSION['flash'])) : ?>
   <div class="container col-md-6 pt-3">
      <div class="alert alert-<?php echo $_SESSION['flash']['alert'] ?>">
         <?php echo $_SESSION['flash']['message'] ?>
      </div>
   </div>
<?php unset($_SESSION['flash']); endif; ?>


<?php
// define routing
// if SEARCH
if (isset($_GET['search'])) {
   $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
   $result = xpathSearch($search, $doc);
   printTable($result);
}
// if EDIT customer
elseif(isset($_GET['edit']) && is_string($_GET['edit'])) {
   $cid = filter_input(INPUT_GET, 'edit', FILTER_SANITIZE_STRING);
   $customer = customer2array(customerGet($cid, $doc));
   printForm($customer);
}
// if EDIT customer
elseif(isset($_GET['add']) && $_GET['add'] == 'true') {
   printForm();
}
// output list of all customers
else {
   $customers = $doc->documentElement;
   printTable(xml2array($customers)['customer']);
}
?>
</div>
</body>
</html>

<?php
/**
 * XML to Array transformer
 *
 * @return array
 */
function xml2array($root)
{
   $result = [];
   $self = __FUNCTION__;

   if ($root->hasAttributes()) {
      $attrs = $root->attributes;
      foreach ($attrs as $attr) {
         $result[$attr->name] = $attr->value;
      }
   }

   if ($root->hasChildNodes()) {
      $children = $root->childNodes;
      if ($children->length == 1) {
         $child = $children->item(0);
         if ($child->nodeType == XML_TEXT_NODE) {
            $result['_value'] = $child->nodeValue;
            return count($result) == 1 ? $result['_value'] : $result;
         }
      }
      $groups = [];
      foreach ($children as $child) {
         if (!isset($result[$child->nodeName])) {
            $result[$child->nodeName] = $self($child);
         } else {
            if (!isset($groups[$child->nodeName])) {
               $result[$child->nodeName] = array($result[$child->nodeName]);
               $groups[$child->nodeName] = 1;
            }
            $result[$child->nodeName][] = $self($child);
         }
      }
   }

   return $result;
}

/**
 * CREATE new customer
 *
 * @return integer or boolean
 */
function customerCreate($post, $doc, $file)
{
   $xml = $doc->createElement("customer");
   $xml->setAttribute('number', $post['number']);

   $doc->documentElement->appendChild($xml);

   // create <meternumber> node
   $node = $doc->createElement('meternumber', $post['meternumber']);
   $xml->appendChild($node);

   // create <name> node
   $node = $doc->createElement('name');
   $node->setAttribute('title', $post['title']);
   $xml->appendChild($node);
   $cnode = $doc->createElement('first', $post['first']);
   $node->appendChild($cnode);
   $cnode = $doc->createElement('last', $post['last']);
   $node->appendChild($cnode);
   $cnode = $doc->createElement('middle', $post['middle']);
   $node->appendChild($cnode);

   // create <address> node
   $node = $doc->createElement('address');
   $xml->appendChild($node);
   $cnode = $doc->createElement('street', $post['street']);
   $node->appendChild($cnode);
   $cnode = $doc->createElement('suburb', $post['suburb']);
   $node->appendChild($cnode);
   $cnode = $doc->createElement('postcode', $post['postcode']);
   $node->appendChild($cnode);
   $cnode = $doc->createElement('state', $post['state']);
   $node->appendChild($cnode);

   // create <phone> node
   $node = $doc->createElement('phone');
   $xml->appendChild($node);
   foreach ($post['phone'] as $number) {
      $cnode = $doc->createElement('number', $number);
      $node->appendChild($cnode);
   }

   $doc->formatOutput = true;

   return $doc->save($file);
}

/**
 * UPDATE existing customer
 *
 * @return integer or boolean
 */
function customerUpdate($post, $doc, $file)
{
   // get customer object
   $customer = customerGet($post['number'], $doc);

   $customer->getElementsByTagName('name')->item(0)->setAttribute('title', $post['title']);
   $customer->getElementsByTagName('first')->item(0)->nodeValue = $post['first'];
   $customer->getElementsByTagName('last')->item(0)->nodeValue = $post['last'];
   $customer->getElementsByTagName('middle')->item(0)->nodeValue = $post['middle'];
   $customer->getElementsByTagName('street')->item(0)->nodeValue = $post['street'];
   $customer->getElementsByTagName('suburb')->item(0)->nodeValue = $post['suburb'];
   $customer->getElementsByTagName('postcode')->item(0)->nodeValue = $post['postcode'];
   $customer->getElementsByTagName('state')->item(0)->nodeValue = $post['state'];

   // create new <phone> node
   $node = $doc->createElement('phone');
   foreach ($post['phone'] as $number) {
      if (!$number) continue;
      $cnode = $doc->createElement('number', $number);
      $node->appendChild($cnode);
   }
   // replace <phone> node
   $phone = $customer->getElementsByTagName('phone')->item(0);
   $phone->parentNode->replaceChild($node, $phone);

   $doc->formatOutput = true;

   return $doc->save($file);
}

/**
 * get customer by ID
 *
 * @return DOMElement
 */
function customerGet($cid, $doc)
{
   // query for customer number
   $query = "/customers/customer[@number='$cid']";
   $xpath = new DOMXPath($doc);

   return $xpath->query($query)->item(0);
}

/**
 * customer XML object to array transformer
 *
 * @return array
 */
function customer2array($xml)
{
   $carray = xml2array($xml);

   $customer = [
      'number' => $carray['number'],
      'meternumber' => $carray['meternumber'],
      'title' => $carray['name']['title'],
      'first' => $carray['name']['first'],
      'last' => $carray['name']['last'],
      'middle' => is_string($carray['name']['middle']) ? $carray['name']['middle'] : '',
      'street' => $carray['address']['street'],
      'suburb' => $carray['address']['suburb'],
      'postcode' => $carray['address']['postcode'],
      'state' => $carray['address']['state'],
      'phone' => is_string($carray['phone']['number']) ? [$carray['phone']['number']] : $carray['phone']['number'],
   ];

   return $customer;
}

/**
 * search in XML
 *
 * @return array
 */
function xpathSearch($search, $doc)
{
   // query for customer number
   $query = "/customers/customer[@number[contains(.,'$search')]]";
   // query for meternumber
   $query .= "| /customers/customer/meternumber[contains(.,'$search')]/..";
   // query for customer last name
   $query .= "| /customers/customer/name/last[contains(.,'$search')]/../..";

   $xpath = new DOMXPath($doc);
   $customers = $xpath->query($query);

   // create multidimentional array
   $array = [];
   foreach ($customers as $customer) {
      $array[] = xml2array($customer);
   }

   return $array;
}
/**
 * output list of customers
 */
function printTable($customers)
{
if (!count($customers)) : ?>
   <div class="container col-md-6 pt-4">
      <div class="alert alert-primary">
         <strong>Info!</strong> Customers not found!
      </div>
   </div>
<?php endif; ?>
<table class="table table-hover">
   <thead>
      <tr>
      <th scope="col">#</th>
      <th scope="col">Surname</th>
      <th scope="col">Customer number</th>
      <th scope="col">Meter number</th>
      <th scope="col">
         <i class="fa fa fa-cogs"></i>
      </th>
      </tr>
   </thead>
   <tbody>
<?php
   foreach ($customers as $key => $customer) : ?>
      <tr>
         <th scope="row"><?php echo $key; ?></th>
         <td><?php echo htmlspecialchars($customer['name']['last']); ?></td>
         <td><?php echo htmlspecialchars($customer['number']); ?></td>
         <td><?php echo htmlspecialchars($customer['meternumber']); ?></td>
         <td>
            <a class="btn btn-outline-primary" href="?edit=<?php echo htmlspecialchars($customer['number']); ?>" title="Edit customer">
               <i class="fa fa-pencil-square-o"></i>
            </a>
         </td>
      </tr>
<?php endforeach; ?>
   </tbody>
</table>
<?php
}

/**
 * output Form for customer
 */
function printForm($customer = null)
{
   $customer === null ? $edit = false : $edit = true;
?>
<div class="container mb-4 pt-4">
   <form method="post">
      <input type="hidden" name="method" value="<?php echo $customer === null ? 'post' : 'put'; ?>">
      <div class="form-row">
         <div class="col-md-1 mb-3">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" placeholder="Title" name="title" value="<?php echo htmlspecialchars($customer['title']??''); ?>">
         </div>
         <div class="col-md-4 mb-3">
            <label for="fname">First name *</label>
            <input type="text" class="form-control" id="fname" placeholder="First name" name="first"
               value="<?php echo htmlspecialchars($customer['first']??''); ?>" required>
         </div>
         <div class="col-md-4 mb-3">
            <label for="lname">Last name *</label>
            <input type="text" class="form-control" id="lname" placeholder="Last name" name="last"
               value="<?php echo htmlspecialchars($customer['last']??''); ?>" required>
         </div>
         <div class="col-md-3 mb-3">
            <label for="mname">Middle name</label>
            <input type="text" class="form-control" id="mname" placeholder="Middle name" name="middle" value="<?php echo htmlspecialchars($customer['middle']??''); ?>">
         </div>
      </div>

      <div class="form-row">
         <div class="col-md-2 mb-3">
            <label for="customernumber">Customer number *</label>
            <input type="text" class="form-control" id="customernumber" placeholder="Customer number"
               name="number" value="<?php echo htmlspecialchars($customer['number']??''); ?>" <?php echo $edit?'readonly':''?>>
         </div>
         <div class="col-md-2 mb-3">
            <label for="meternumber">Meter number *</label>
            <input type="text" class="form-control" id="meternumber" placeholder="Meter number" name="meternumber"
               value="<?php echo htmlspecialchars($customer['meternumber']??''); ?>" <?php echo $edit?'readonly':''?>>
         </div>
         <div class="col-md-3 mb-3">
            <label for="phone01">Phone</label>
            <input type="text" class="form-control" id="phone01" placeholder="First phone" name="phone[]" value="<?php echo htmlspecialchars($customer['phone'][0]??''); ?>">
         </div>
         <div class="col-md-3 mb-3">
            <label for="phone02">Phone</label>
            <input type="text" class="form-control" id="phone02" placeholder="Second phone" name="phone[]" value="<?php echo htmlspecialchars($customer['phone'][1]??''); ?>">
         </div>
         <div class="col-md-2 mb-3">
            <label for="phone03">Phone</label>
            <input type="text" class="form-control" id="phone03" placeholder="Third phone" name="phone[]" value="<?php echo htmlspecialchars($customer['phone'][2]??''); ?>">
         </div>
      </div>

      <div class="form-row">
         <div class="col-md-5 mb-3">
            <label for="street">Street *</label>
            <input type="text" class="form-control" id="street" name="street" placeholder="Street" value="<?php echo htmlspecialchars($customer['street']??''); ?>" required>
         </div>
         <div class="col-md-3 mb-3">
            <label for="city">City *</label>
            <input type="text" class="form-control" id="city" name="suburb" placeholder="City" value="<?php echo htmlspecialchars($customer['suburb']??''); ?>" required>
         </div>
         <div class="col-md-2 mb-3">
            <label for="state">State *</label>
            <input type="text" class="form-control" id="state" name="state" placeholder="State" value="<?php echo htmlspecialchars($customer['state']??''); ?>" required>
         </div>
         <div class="col-md-2 mb-3">
            <label for="postcode">Postcode *</label>
            <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Postcode" value="<?php echo htmlspecialchars($customer['postcode']??''); ?>" required>
         </div>
      </div>

      <div class="form-row">
         <div class="col-md-8 mb-3">
            <button class="btn btn-primary mx-auto d-block" type="submit">Submit form</button>
         </div>
         <div class="col-md-4 mb-3">
            <a class="btn btn-warning" href="<?php echo $_SERVER['PHP_SELF']; ?>" role="button">Cancel</a>
         </div>
      </div>

   </form>
</div>
<?php
}
