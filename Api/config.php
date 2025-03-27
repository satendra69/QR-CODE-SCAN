<?php

$_serverName = "";
$_database = "";

$ad_authentication = false;


$DB_username ="";
$DB_password= "";

$connectionInfo = array( "Database"=>$_database, "UID"=>$DB_username, "PWD"=>$DB_password,"CharacterSet" => "UTF-8","TrustServerCertificate"=>true);
$conn = sqlsrv_connect($_serverName, $connectionInfo);
$syn_conn = sqlsrv_connect($_serverName, $connectionInfo);

if( $conn ) {
	$config = array(
        "status" => "success",
        "serverName" => $_serverName,
        "databaseName" => $_database
    );
     //echo "Connection established.<br />";
}else{
     //echo "Connection could not be established.<br />";
     //die( print_r( sqlsrv_errors(), true));
}


?>