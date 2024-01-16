<?php
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

function generateToken($userid, $password)
{
    $key = "panbro_api_key_generate";
    $issuer = "JWT";
    $audience = "for_supplier_panbro";
    $issuedAt = time();
    $expirationTime = $issuedAt + 60; // Token berlaku selama 1 jam
    $notBefore = $issuedAt;

    $tokenPayload = array(
        "iss" => $issuer,
        "aud" => $audience,
        "iat" => $issuedAt,
        "nbf" => $notBefore,
        "exp" => $expirationTime,
        "data" => array(
            "userid" => $userid,
            "password" => $password
        )
    );

    $jwt = JWT::encode($tokenPayload, $key, 'HS256');
    return $jwt;
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(array("message" => "JSON format not valid."));
    exit;
}

if (empty($data->userid) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "JSON data not valid."));
    exit;
}

$con_web_sup = mysqli_connect("localhost", "root", "", "erp_web");

if (!$con_web_sup) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection error."));
    exit;
}

$userid = mysqli_real_escape_string($con_web_sup, $data->userid);
$password = mysqli_real_escape_string($con_web_sup, $data->password);

$query = "SELECT username, fullname FROM userpassword WHERE username='$userid' AND password=MD5('$password')";
$result = $con_web_sup->query($query);

if ($result) {
    $rscek = mysqli_fetch_array($result);

    if ($rscek["username"] != "") {
        $token = generateToken($data->userid, $data->password);

        if ($token !== null) {
            http_response_code(200);
            echo json_encode(array(
                "message" => "success",
                "statusCode" => 200,
                "token" => $token
        ),JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Error generating token."),JSON_PRETTY_PRINT);
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Username or password not valid."),JSON_PRETTY_PRINT);
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Error executing query."),JSON_PRETTY_PRINT);
}

mysqli_close($con_web_sup);
?>
