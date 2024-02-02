<?php
namespace PLAYGROUND;

ini_set('error_reporting', E_ALL);
ini_Set('display_errors',  1);

include('vendor/autoload.php');


print "Case 1, check in Provider: \r\n";
/**
 * Ellipsis unpacking for named arguments, same as
 * new MyDataModel( number => 3, number2 => 55)
 */
$myData1 = new MyDataModel(...[
    'number1' => 3, // Max 30
    'number2' => 30 // Min 40, Max 50 --> Should fail
]);
$dataServiceProviderWithCheck = new DataServiceProviderWithCheck();
$dataServiceProviderWithCheck->push($myData1);


print "\r\n\r\nCase 2, check in Model: \r\n";
$myData2 = new MyDataModelWithCheck(...[
    'number1' => 31, // Max 30
    'number2' => 44 // Min 40, Max 50 --> Should fail
]);

// $dataServiceProvider = new DataServiceProvider();
// $dataServiceProvider->push($myData2);
