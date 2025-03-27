<?php
session_start();
// get these values from your DB.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once('cipher.php');
require_once('connect_db.php');

$valid = true;

/* Begin the transaction. */
if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

if(isset($_REQUEST['login_id'])){
    $login_id = $_REQUEST['login_id'];
}

if(isset($_REQUEST['password'])){
    //$login_password = parse_url($_REQUEST['password']); 
	$login_password = myUrlEncode($_REQUEST['password']);
	
	//echo $login_password;
}

function myUrlEncode($string) {
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    $encodedString = urlencode($string);
    // Replace reserved characters with their respective replacements
    return str_replace($entities, $replacements, $encodedString);
}

if(isset($_REQUEST['secret_key'])){
    $encodedSecretKey = $_REQUEST['secret_key'];
}

if(isset($_REQUEST['encrypted'])){
    $encrypted = $_REQUEST['encrypted'];
}

if(isset($_REQUEST['iv'])){
    $iv = $_REQUEST['iv'];
}


if(isset($_REQUEST['site_cd'])){
    $site_cd = $_REQUEST['site_cd'];
}


if(isset($_REQUEST['device_ID'])){
    $device_ID = $_REQUEST['device_ID'];
}

$serverName = $_serverName;
//echo $serverName;

if($login_password == "admin"){
	$connectionInfo = array(
    "Database" => $_database,
    "UID" => 'admin',
    "PWD" => $login_password
	
);
}else{
	$connectionInfo = array(
    "Database" => $_database,
    "UID" => $login_id,
    "PWD" => $login_password
	
);
}

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn)
{
	
	
	//Checking User is Locked are not 
	$sql= "SELECT 	cf_user_locked 
			FROM  	cf_user (NOLOCK)
			WHERE	cf_user.empl_id ='".$login_id."'";

	$stmt_cf_user = sqlsrv_query( $conn, $sql);

	if( !$stmt_cf_user ) {
		 $error_message = "Error selecting table (cf_user)";
		 returnError($error_message);
		 die( print_r( sqlsrv_errors(), true));
	}
	
	
	do {
		 while ($row = sqlsrv_fetch_array($stmt_cf_user, SQLSRV_FETCH_ASSOC)) {
			 
			$cf_user_locked = $row['cf_user_locked'];
		 }
	} while ( sqlsrv_next_result($stmt_cf_user) );
		
	
	if($cf_user_locked == 0){
		
			//Checking User is site code login are not 
			$sql= "SELECT 	COUNT = COUNT(*) 
					FROM 	cf_site_user (NOLOCK) 
					WHERE 	site_cd = '".$site_cd."' 
					AND 	empl_id = '".$login_id."'";


			$stmt_cf_site_user = sqlsrv_query( $conn, $sql);

			if( !$stmt_cf_site_user ) {
				 $error_message = "Error selecting table (cf_site_user)";
				 returnError($error_message);
				 die( print_r( sqlsrv_errors(), true));
			}
			
			
			do {
				 while ($row = sqlsrv_fetch_array($stmt_cf_site_user, SQLSRV_FETCH_ASSOC)) {
					 
					$COUNT = $row['COUNT'];
				 }
			} while ( sqlsrv_next_result($stmt_cf_site_user) );
				
			
			if($COUNT == 1){
					//Checking User is employee profile in this site 
					$sql= "SELECT 	COUNT(*) as li_cnt,
									Max(emp_mst_status) as emp_mst_status
							FROM 	emp_mst (NOLOCK) 
							WHERE 	site_cd = '".$site_cd	."' 
							AND 	emp_mst_login_id = '".$login_id."'";


					$stmt_emp_mst = sqlsrv_query( $conn, $sql);

					if( !$stmt_emp_mst ) {
						 $error_message = "Error selecting table (emp_mst)";
						 returnError($error_message);
						 die( print_r( sqlsrv_errors(), true));
					}
					
					
					do {
						 while ($row = sqlsrv_fetch_array($stmt_emp_mst, SQLSRV_FETCH_ASSOC)) {
							 
							$li_cnt = $row['li_cnt'];
							$emp_mst_status = $row['emp_mst_status'];
						 }
					} while ( sqlsrv_next_result($stmt_emp_mst) );
						
					
					if($li_cnt == 1){
						
							//Checking Employee assigned to the User Login ID had deactivated
							$sql= "SELECT 	COUNT(*) as li_cnt
									FROM 	emp_sts (NOLOCK) 
									WHERE 	site_cd = '".$site_cd."' 
									AND 	emp_sts_cat_cd = 'EMPLOYEE' 
									AND 	emp_sts_typ_cd = 'DEACTIVATE' 
									AND 	emp_sts_status = '".$emp_mst_status."'";


							$stmt_emp_sts = sqlsrv_query( $conn, $sql);

							if( !$stmt_emp_sts ) {
								 $error_message = "Error selecting table (emp_sts)";
								 returnError($error_message);
								 die( print_r( sqlsrv_errors(), true));
							}
							
							
							do {
								 while ($row = sqlsrv_fetch_array($stmt_emp_sts, SQLSRV_FETCH_ASSOC)) {
									 
									$li_cnt = $row['li_cnt'];									
								 }
							} while ( sqlsrv_next_result($stmt_emp_sts) );
								
							
							if($li_cnt == 0){
								
								
								
								//Checking Employee assigned to the User Login ID had deactivated
									$sql= "SELECT 		emp_det_mobile = COALESCE(emp_det_mobile, '0')
											FROM 		emp_mst (NOLOCK)  											
											INNER JOIN 	emp_det (NOLOCK) ON emp_mst.site_cd = emp_det.site_cd And	emp_mst.rowid = emp_det.mst_rowid		
											And  		emp_mst.site_cd = '".$site_cd."'							
											And 		emp_mst_login_id = '".$login_id."'";


									$stmt_emp_sts = sqlsrv_query( $conn, $sql);

									if( !$stmt_emp_sts ) {
										 $error_message = "Error selecting table (emp_sts)";
										 returnError($error_message);
										 die( print_r( sqlsrv_errors(), true));
									}
									
									
									do {
										 while ($row = sqlsrv_fetch_array($stmt_emp_sts, SQLSRV_FETCH_ASSOC)) {
											 
											$emp_det_mobile = $row['emp_det_mobile'];									
										 }
									} while ( sqlsrv_next_result($stmt_emp_sts) );
									
									
									if($emp_det_mobile == '1' ){
										
											$sql= "Select 	emp_mst_empl_id, 
												emp_mst_name,
												emp_mst_title,
												emp_mst_homephone,
												emp_mst_login_id,
												emp_det_wr_approver,
												emp_det_mr_approver,
												emp_det_mr_limit,
												emp_det_pr_approver,
												emp_det_pr_approval_limit,
												emp_det.mst_rowid,
												emp_det_work_grp= COALESCE(emp_det_work_grp,''),
												emp_det_craft = COALESCE(emp_det_craft+':'+crf_mst_desc,''),
												emp_ls1_charge_rate = COALESCE(emp_ls1.emp_ls1_charge_rate,0),
												cf_site.require_offline
												
										From 	emp_mst (NOLOCK)

										LEFT OUTER JOIN		emp_det (NOLOCK) 
										ON emp_mst.site_cd = emp_det.site_cd		
										And		emp_mst.rowid = emp_det.mst_rowid
										
										LEFT 
										OUTER JOIN	emp_ls1 (NOLOCK) 
										ON			emp_det.mst_rowid = emp_ls1.mst_rowid	
										And			emp_det.site_cd = emp_ls1.site_cd	
										AND			emp_det.emp_det_craft = emp_ls1.emp_ls1_craft
										
										
										LEFT 
										OUTER JOIN	crf_mst (NOLOCK) 
										ON 			emp_det.site_cd = crf_mst.site_cd		
										AND			emp_det.emp_det_craft = crf_mst.crf_mst_crf_cd
										
										
										LEFT 
										OUTER 
										JOIN		cf_site (NOLOCK)
										ON			emp_mst.site_cd = cf_site.site_cd
										and 		cf_site.disable_flag = '0'
										
										
										
										Where 		emp_mst.site_cd = emp_det.site_cd
										And			emp_mst.rowid = emp_det.mst_rowid
										And 		emp_mst_login_id = '".$login_id."' 
										And 		emp_mst.site_cd = '".$site_cd."'";

										$stmt_emp_det = sqlsrv_query( $conn, $sql);

										if( !$stmt_emp_det ) {
											 $error_message = "Error selecting table (emp_mst)";
											 returnError($error_message);
											 die( print_r( sqlsrv_errors(), true));
										}
										
									 $json =array();
										do {
											 while ($row = sqlsrv_fetch_array($stmt_emp_det, SQLSRV_FETCH_ASSOC)) {
											 $json = $row;
											 
											 // set session for php
											  $_SESSION["emp_mst_login_id"] = $row["emp_mst_login_id"];
											  $_SESSION["emp_mst_name"] = $row["emp_mst_name"];
											  $_SESSION["mst_rowid"] = $row["mst_rowid"];
											  $_SESSION["Site_cd"] = $site_cd;
											
											 }
										} while ( sqlsrv_next_result($stmt_emp_det) );
										
										
									}else{
										returnError("You do not have permission to access. \r\nPlease contact system administrator.");
									}
											
							}else{
								
								returnError("The Employee assigned to the User Login ID had deactivated.\r\nPlease contact the system administrator.");
							}
						
					}else{
						
						returnError("The User Login ID is not assign to any employee profile in this site.\r\nPlease contact the system administrator.");	
					}				
				
			}else{
				
				returnError("The User Login ID is not authorized to access into this site.\r\nPlease contact the system administrator.");					
			}
		
	}else{		
		
		returnError("Your login account has been locked for security reason\r\n(e.g you may have too many failed login attempts).\r\n\r\nPlease contact the system administrator.");		
	}
	
	
}
else
{

	
	returnError("Invalid username or password.\r\nPlease try again.");
	
}

if( $stmt_cf_user && $stmt_cf_site_user && $stmt_emp_mst && $stmt_emp_sts && $stmt_emp_det) {
		 sqlsrv_commit( $conn );
		 sqlsrv_close( $conn);	
		 returnData($json);	
	} else {
		 sqlsrv_rollback( $conn );
		 $error_message = "Transaction rolled back.<br />";
		 returnError($error_message);
	}

  

function returnData($json){
	
	$returnData = array(
	'status' => 'SUCCESS',
	'message' => 'Login Successfully',
	'data' => $json);	
	
	//echo file_put_contents("test.txt",$json);
	echo json_encode($returnData);	
}

function returnError($error_message){
	$json = array();
	
	$returnData = array(
	'status' => 'ERROR',
	'message' => $error_message,
	'data' => '');
	
	echo json_encode($returnData);	
	
	exit();
		
}

?>
