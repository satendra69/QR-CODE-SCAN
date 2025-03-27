<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header(
    "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
);

require_once('config.php');
$error_message;
$valid = true;


/* Begin the transaction. */
if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

$site_cd = $_REQUEST['site_cd'];

if ($_SERVER["REQUEST_METHOD"] == "GET") {
		
	$sql = "SELECT dft_mst_clo_popup, RowID FROM dft_mst (NOLOCK) WHERE site_cd = '$site_cd'";
					
			$stmt_cf_account = sqlsrv_query( $conn, $sql);

			if( !$stmt_cf_account ) {
				 $error_message = "Error selecting table (dft_mst)";
				 returnError($error_message);
				 die( print_r( sqlsrv_errors(), true));
				 
			}

			$dft_mst = array();

			do {
				 while ($row = sqlsrv_fetch_array($stmt_cf_account, SQLSRV_FETCH_ASSOC)) {		
					$dft_mst[] = $row;	
				
				 }
			} while ( sqlsrv_next_result($stmt_cf_account) );
							
}
				$json_All = array();
	
				if( $dft_mst ) {
					
				 sqlsrv_commit( $conn );
				 sqlsrv_close( $conn);	
				
				 $json_All['DeafultCloseTime'] = $dft_mst;
					
				returnData($json_All);	
				
				}else {
				 sqlsrv_rollback( $conn );
				 $error_message = "Transaction rolled back.<br />";
				 returnError($error_message);
			}

function returnData($json_return){
	//echo $json;
	$returnData = array(												
	'status' => 'SUCCESS',
	'message' => 'Successfully',
	'data'=>$json_return);
	
	echo json_encode($returnData);
}

function returnError($error_message){
	$json = array();
	
	$returnData = array(
	'status' => 'ERROR',
	'message' => $error_message,
	'data' => $json);
	
	echo json_encode($returnData);
		
}


?>