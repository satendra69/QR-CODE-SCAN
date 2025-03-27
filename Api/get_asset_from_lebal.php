<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header(
    "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
);


 include 'connect_db.php';
$error_message;
$valid = true;

/* Begin the transaction. */
if ( sqlsrv_begin_transaction( $conn ) === false ) {
     die( print_r( sqlsrv_errors(), true ));
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql = "SELECT * FROM cf_label WHERE table_name IN ('ast_mst', 'ast_det') AND language_cd ='DEFAULT'";

    $stmt_cf_label = sqlsrv_query($conn, $sql);

    if (!$stmt_cf_label) {
        $error_message = "Error selecting table (cf_label)";
        returnError($error_message);
        die(print_r(sqlsrv_errors(), true));
    }

    $json_ast_mst = array();
    $json_ast_det = array();

    while ($row = sqlsrv_fetch_array($stmt_cf_label, SQLSRV_FETCH_ASSOC)) {
        if ($row['table_name'] === 'ast_mst') {
            $json_ast_mst[] = $row;
        } elseif ($row['table_name'] === 'ast_det') {
            $json_ast_det[] = $row;
        }
    }

    if (!empty($json_ast_mst) || !empty($json_ast_det)) {
        returnData($json_ast_mst, $json_ast_det);
    } else {
        sqlsrv_rollback($conn);
        $error_message = "Transaction rolled back.<br />";
        returnError($error_message);
    }

    sqlsrv_free_stmt($stmt_cf_label);
    sqlsrv_close($conn);
}

function returnData($json_ast_mst, $json_ast_det)
{
    $returnData = array(
        'status' => 'SUCCESS',
        'message' => 'Successfully',
        'data' => array(
            'ast_mst' => $json_ast_mst,
            'ast_det' => $json_ast_det
        )
    );

    echo json_encode($returnData);
}

function returnError($error_message)
{
    $json = array();

    $returnData = array(
        'status' => 'ERROR',
        'message' => $error_message,
        'data' => $json
    );

    echo json_encode($returnData);
}

?>