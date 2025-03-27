<?php
// get these values from your DB.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once('config.php');
$error_message;
$valid = true;


if ($_SERVER["REQUEST_METHOD"] == "GET")
{
	
	 $username = $_GET['UserName'];
	
   
$sql= "select default_site = default_site + ' : ' + site_name from  cf_user (NOLOCK),cf_site (NOLOCK) Where
cf_user.default_site = cf_site.site_cd
and cf_user.empl_id ='".$username."'";

$stmt = sqlsrv_query( $conn, $sql);

if( !$stmt ) {
     $error_message = "Error selecting table (cf_user)";
	 returnError($error_message);
     die( print_r( sqlsrv_errors(), true));
}

$json = array();

do {
     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
     $json[] = $row;
     }
} while ( sqlsrv_next_result($stmt) );
	
}


if ($valid) {
			
	returnData($json);	
	sqlsrv_free_stmt( $stmt);
	sqlsrv_close( $conn);
}

function returnData($json){
	$returnData = array(
	'status' => 'SUCCESS',
	//'message' => 'URL is correct',
	'data' => $json);
	
	echo json_encode($returnData);
}

function returnError($error_message){
	$json = array();
	
	$returnData = array(
	'status' => 'ERROR',
	'message' => $error_message,
	'data' => $json);
	
	echo json_encode($returnData);
	
		
	exit();
}

 
?>