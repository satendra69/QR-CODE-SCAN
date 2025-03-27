<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header(
    "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
);
// get these values from your DB.
require_once('config.php');
$error_message;
$valid = true;

/* Begin the transaction. */
if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

$baseDir = __DIR__; // Get the directory of the current script
$tempDir = $baseDir . DIRECTORY_SEPARATOR . 'temp_';
$thumbnailDir = $tempDir . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR;

// Ensure the temp directory exists
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// Ensure the thumbnail directory exists
if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0777, true);
}

// Define the upload directories
$uploaddir = $tempDir;
$exportdir = $tempDir;
$exportdirtn = $thumbnailDir;


$RowID = $_REQUEST['RowID'];

				$sql="select file_name,type,attachment,RowID from emp_ref where mst_RowID = '".$RowID."' AND type='P'";

				$stmt_wko_mst = sqlsrv_query( $conn, $sql);

				if( !$stmt_wko_mst ) {
					 $error_message = "Error selecting table (wko_ref)";
					 returnError($error_message);
					 die( print_r( sqlsrv_errors(), true));
					 
				}

				$json = array();

				do {
					 while ($row = sqlsrv_fetch_array($stmt_wko_mst, SQLSRV_FETCH_ASSOC)) {	
					 
						if (!empty($row['attachment'])) {
							
							$Photo = $row['attachment'];
							$fileName = $row['file_name'];
							
							//$exportFullPathFileName = $exportdir.$fileName;
							//$exportFullPathTNFileName = $exportdirtn.$fileName;
							$exportFullPathFileName = $exportdir . DIRECTORY_SEPARATOR . $fileName;
							$exportFullPathTNFileName = $exportdirtn . DIRECTORY_SEPARATOR . $fileName;
								 
							
							If ($file = fopen($exportFullPathFileName,"w")) {
								if (fwrite($file, $Photo)==false) {
								echo "Error Write Files";
								}
								fclose($file);
							}	

							If ($file = fopen($exportFullPathTNFileName,"w")) {
								if (fwrite($file, $Photo)==false) {
								echo "Error Write Files";
								}
								fclose($file);
							}
							
							
							$row['attachment'] = '\\temp_\\thumbnail\\' . $fileName;
							$row['filename'] = $fileName;
							$row['ImgStatus'] = "Old_img";
						}					 
						$json[] = $row;	
					
					
					 }
				} while ( sqlsrv_next_result($stmt_wko_mst) );
				
          
				$json_All = array();
				
				$json_All['UserProfileDt'] = $json;	
				
				



if( $stmt_wko_mst) {
		 sqlsrv_commit( $conn );
		 sqlsrv_close( $conn);	
		 returnData($json_All);
	} else {
		 sqlsrv_rollback( $conn );
		 $error_message = "Transaction rolled back.<br />";
		 returnError($error_message);
	} 



function returnData($json_All){
	 $json1 = json_encode($json_All);
	 //echo$json;
	 
	 if(empty(json_decode($json1,1))) {
     $returnData = array(
	'status' => 'SUCCESS',
	'message' => 'No Records found ',	
	'data' => $json_All);
}else{
	$returnData = array(
	'status' => 'SUCCESS',
	'message' => 'Successfully',
	'data' => $json_All);
} 	
	
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