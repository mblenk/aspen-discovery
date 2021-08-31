<?php
# ****************************************************************************************************************************
# * Last Edit: May 3, 2021
# * - login check using Aspen API calls
# *
# * 05-03-21: changed how location information is handled and stored - CZ
# * 04-08-21: Base Version - CZ
# ****************************************************************************************************************************

# ****************************************************************************************************************************
# * include the helper file that holds the URL information by client
# ****************************************************************************************************************************
require_once '../bootstrap.php';
require_once '../bootstrap_aspen.php';

# ****************************************************************************************************************************
# * grab the passed location parameter, then find the path
# ****************************************************************************************************************************
$urlPath = 'https://'.$_SERVER['SERVER_NAME'];

# ****************************************************************************************************************************
# * Prep the login information for checking - dummy out something just in case
# ****************************************************************************************************************************
$barcode = "thisisadummybarcodeincaseitisleftblank";
$pin     = 1234567890;

# ****************************************************************************************************************************
# * grab the variables
# ****************************************************************************************************************************
if (! empty($_GET['barcode'])) { $barcode = $_GET['barcode']; }
if (! empty($_GET['pin'])) { $pin = $_GET['pin']; }

# ****************************************************************************************************************************
# * assemble the login API URL
# ****************************************************************************************************************************
$login = $urlPath . '/API/UserAPI?method=validateAccount&username=' . $barcode . '&password=' . $pin;

# ****************************************************************************************************************************
# * attempt to login
# ****************************************************************************************************************************
$jsonData = json_decode(file_get_contents($login), true);

# ****************************************************************************************************************************
# * unsuccessful login
# ****************************************************************************************************************************
if (empty ($jsonData['result']['success']['id'])) {
  $patronInfo = array('ValidLogin'   => 'No');
} else {

# ****************************************************************************************************************************
# * successful login ... other values can be found when 
# ****************************************************************************************************************************
  $patronInfo = array('ValidLogin' => 'Yes', 'Name' => $jsonData['result']['success']['displayName']);
}

# ****************************************************************************************************************************
# * Output to JSON
# ****************************************************************************************************************************
header('Content-Type: application/json');
echo json_encode($patronInfo);
?>