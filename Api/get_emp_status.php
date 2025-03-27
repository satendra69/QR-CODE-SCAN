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

 $site_cd = $_REQUEST['site_cd'];
 $emp_mst_empl_id = $_REQUEST['emp_mst_empl_id'];
	
//$sql= "select emp_mst.emp_mst_empl_id,emp_mst.emp_mst_name from emp_mst where site_cd ='".$site_cd."'";

$sql= "SELECT  emp_mst.emp_mst_empl_id,     		
		emp_mst_title = COALESCE(emp_mst_title,''), 
		emp_mst_att_sts = COALESCE(emp_mst_att_sts,''), 
		emp_mst.emp_mst_name,emp_mst_emg_phone = COALESCE(emp_mst_emg_phone,''),
		emp_det_craft = COALESCE(emp_det_craft+':'+crf_mst_desc,''),
		emp_ls1_charge_rate = COALESCE(emp_ls1.emp_ls1_charge_rate,0)		
From 	emp_mst (NOLOCK)

		LEFT 
		OUTER 
		JOIN	emp_sts (NOLOCK) 
		ON		emp_mst.site_cd = emp_sts.site_cd  
		and     emp_mst.emp_mst_status = emp_sts.emp_sts_status  	
		And		emp_sts.emp_sts_typ_cd = 'ACTIVE'

		LEFT 
		OUTER 
		JOIN	emp_det (NOLOCK) 
		ON		emp_mst.site_cd = emp_det.site_cd		
		And		emp_mst.rowid = emp_det.mst_rowid
		
		LEFT 
		OUTER 
		JOIN	emp_ls1 (NOLOCK) 
		ON		emp_det.mst_rowid = emp_ls1.mst_rowid	
		And		emp_det.site_cd = emp_ls1.site_cd	
		AND		emp_det.emp_det_craft = emp_ls1.emp_ls1_craft
		
		LEFT 
		OUTER 
		JOIN	crf_mst (NOLOCK) 
		ON		emp_det.site_cd = crf_mst.site_cd		
		AND		emp_det.emp_det_craft = crf_mst.crf_mst_crf_cd	
		
		
WHERE	emp_mst.site_cd = '".$site_cd."' 
And emp_mst.emp_mst_empl_id = '".$emp_mst_empl_id."' 
And emp_sts.emp_sts_typ_cd = 'ACTIVE' ORDER BY emp_mst.emp_mst_empl_id ASC";

$stmt = sqlsrv_query( $conn, $sql);



if( !$stmt ) {
     $error_message = "Error selecting table (emp_mst)";
	 returnError($error_message);
     die( print_r( sqlsrv_errors(), true));
	 
}

$json = array();

do {
     while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
		//$row['emp_mst_name'] =  utf8_encode($row['emp_mst_name']);
		 $row['emp_mst_name'] =  mb_convert_encoding($row['emp_mst_name'], 'UTF-8');
		$json[] = $row;	
	
     }
} while ( sqlsrv_next_result($stmt) );



if ($valid) {


	//echo $sql;
	returnData($json);	
	
	
}

function returnData($json){
	//echo $json;
	$returnData = array(
	'status' => 'SUCCESS',
	'message' => 'Successfully',
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
		
}

	sqlsrv_free_stmt( $stmt);

	sqlsrv_close( $conn);	

 
?>