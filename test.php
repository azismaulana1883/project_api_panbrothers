<?php
require __DIR__ . '/verifyToken.php';

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

$userData = verifyToken($token);

if ($userData !== null) {
    $con_web_sup = mysqli_connect("localhost", "root", "", "erp_web");

    if (!$con_web_sup) {
        http_response_code(500);
        echo json_encode(array("message" => "Database connection error."));
        exit;
    }

    $query = "SELECT * FROM mastervendor2";
    $result = $con_web_sup->query($query);

    if ($result) {
        $dataTabel = array();
        while ($row = $result->fetch_assoc()) {
            $dataTabel[] = $row;
        }

        http_response_code(200);
        echo json_encode($dataTabel);
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Error executing query."));
    }

    mysqli_close($con_web_sup);
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized. Invalid or missing token."));
}
?>
