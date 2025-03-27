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
	
	//$site_cd = $_REQUEST['site_cd'];
	
$sql= "select * from cf_site (NOLOCK) where disable_flag = '0'";

$stmt = sqlsrv_query( $conn, $sql);

if( !$stmt ) {
     $error_message = "Error selecting table (cf_site)";
	 returnError($error_message);
     die( print_r( sqlsrv_errors(), true));
}

$json = array();
$jsonempID = array();
$jsonDBInfo = array();

do {
     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
     $json[] = $row;
     }
} while ( sqlsrv_next_result($stmt) );
	
// second query for emp_login_id
$sqlEmp = "select empl_id from cf_user";
$stmt2 = sqlsrv_query( $conn, $sqlEmp);

if( !$stmt2 ) {
     $error_message = "Error selecting table (cf_user)";
	 returnError($error_message);
     die( print_r( sqlsrv_errors(), true));
}
do {
     while ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
     $jsonempID[] = $row2;
     }
} while ( sqlsrv_next_result($stmt2));

}

if ($config['status'] === 'success') {
	
	 $jsonDBInfo = array(
        "serverName" => $config['serverName'],
        "databaseName" => $config['databaseName']
    );
   
} else {
    echo "Error: " . $config['message'];
}
if ($valid) {
			
	returnData($json,$jsonempID,$jsonDBInfo);	
	sqlsrv_free_stmt( $stmt);
	sqlsrv_free_stmt( $stmt2);
	sqlsrv_close( $conn);
}

function returnData($json,$jsonempID,$jsonDBInfo){
	$returnData = array(
	'status' => 'SUCCESS',
	//'message' => 'URL is correct',
	'data' => $json,
	'serverinfo' => $jsonDBInfo,
	'dataEmp' => $jsonempID);
	
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