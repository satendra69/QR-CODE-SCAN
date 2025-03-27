<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Content-Type: application/json');

    // Include the database connection file
    include 'connect_db.php';


$error_message;
$valid = true;

if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

$baseDir = dirname(__FILE__);
$tempDir = $baseDir . DIRECTORY_SEPARATOR . 'temp_';
$thumbnailDir = $tempDir . DIRECTORY_SEPARATOR . 'thumbnail';

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


    // Check if asset_no is received via POST
    if (isset($_POST['asset_no'])) {
        $asset_no = $_POST['asset_no']; 
		$site_cd = $_REQUEST['site_cd'];

       
            // Prepare and execute SQL query
			$sql="select ast_mst_asset_no,ast_mst_asset_shortdesc,ast_mst_cost_center, descs as cost_center_desc, ast_mst_work_area,mst_war_desc as work_area_desc,ast_mst_ast_lvl,
				ast_lvl_desc,ast_mst_asset_locn, ast_loc_desc from ast_mst (NOLOCK)  
			LEFT 
							OUTER 
							JOIN			cf_cost_center (NOLOCK)
							ON				ast_mst.site_cd = cf_cost_center.site_cd
							AND				ast_mst.ast_mst_cost_center = cf_cost_center.costcenter
							
							LEFT 
							OUTER 
							JOIN			mst_war (NOLOCK)
							ON				ast_mst.site_cd = mst_war.site_cd
							AND				ast_mst.ast_mst_work_area = mst_war.mst_war_work_area
							
							LEFT 
							OUTER 
							JOIN			ast_lvl (NOLOCK)
							ON				ast_mst.site_cd = ast_lvl.site_cd
							AND				ast_mst.ast_mst_ast_lvl = ast_lvl.ast_lvl_ast_lvl
							
							LEFT 
							OUTER 
							JOIN			ast_loc (NOLOCK)
							ON				ast_mst.site_cd = ast_loc.site_cd
							AND				ast_mst.ast_mst_asset_locn = ast_loc.ast_loc_ast_loc
					
							WHERE ast_mst.site_cd = ? 
							AND ast_mst.ast_mst_asset_no = ?";
							
					$params = array($site_cd,$asset_no);
					$stmt_asset_list = sqlsrv_query($conn, $sql, $params);
			 

				if( !$stmt_asset_list ) {
					 $error_message = "Error selecting table (emp_mst)";
					 returnError($error_message);
					 die( print_r( sqlsrv_errors(), true));
					 
				}

				$json = array();

				do {
					 while ($row = sqlsrv_fetch_array($stmt_asset_list, SQLSRV_FETCH_ASSOC)) {		
						$json[] = $row;	
					
					 }
				} while ( sqlsrv_next_result($stmt_asset_list) );
				
			$ROW_ID = "";	
			//STEP-02
			$sql2 = "SELECT Rowid From ast_mst  WHERE site_cd = '".$site_cd."'  AND ast_mst_asset_no = '".$asset_no."'";		
			$stmt = sqlsrv_query( $conn, $sql2);			
			if( !$stmt ) {
				$error_message = "Error select table (ast_mst)";
				returnError($error_message);
				die( print_r( sqlsrv_errors(), true));
			}
			do {
				 while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				 
				 $ROW_ID = $row['Rowid'];		
					 
			   }
			} while ( sqlsrv_next_result($stmt));
			sqlsrv_free_stmt( $stmt);
			
	// get img path
		$sql="SELECT TOP 1 file_name,type,attachment,RowID from ast_ref where mst_RowID = '".$ROW_ID."' AND type='P' ORDER BY audit_date DESC";
			$stmt_wko_mst = sqlsrv_query( $conn, $sql);

			if( !$stmt_wko_mst ) {
				 $error_message = "Error selecting table (ast_ref)";
				 returnError($error_message);
				 die( print_r( sqlsrv_errors(), true));
				 
			}
			$json_img = array();
			
			do {
				 while ($row = sqlsrv_fetch_array($stmt_wko_mst, SQLSRV_FETCH_ASSOC)) {	

						if (!empty($row['attachment'])) {
						
						$Photo = $row['attachment'];
						$fileName = $row['file_name'];
						
						// Generate a unique file name
						$fileNameParts = pathinfo($fileName);
						$uniqueSuffix = time() . "_" . rand(1000, 9999);
						$newFileName = $fileNameParts['filename'] . "_" . $uniqueSuffix . "." . $fileNameParts['extension'];
	
						$exportFullPathFileName = $exportdir . DIRECTORY_SEPARATOR . $newFileName ;
						$exportFullPathTNFileName = $exportdirtn . DIRECTORY_SEPARATOR . $newFileName ;
						
						If ($file = fopen($exportFullPathFileName,"w")) {
							if (fwrite($file, $Photo)==false) {
							echo "Error Write Files";
							}
							fclose($file);
						}	
							// Write file to thumbnail directory
						If ($file = fopen($exportFullPathTNFileName,"w")) {
							if (fwrite($file, $Photo)==false) {
							echo "Error Write Files";
							}
							fclose($file);
						}


						$row['attachment'] = 'Api\temp_\\thumbnail\\' . $newFileName;
						$row['filename'] = $newFileName;
					}					 
					$json_img[] = $row;	
				
				
				 }
			} while ( sqlsrv_next_result($stmt_wko_mst) );	
			
				$json_All = array();
				
				$json_All['AllData'] = $json;	
				$json_All['AllRef'] = $json_img;	
	}
	if( $stmt_asset_list) {
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
		 
		$returnData = array(
		'status' => 'SUCCESS',
		'message' => 'Successfully',
		
		'data' => $json_All);
		
		
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
