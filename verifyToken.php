<?php
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\Key;

function verifyToken()
{
    $key = "panbro_api_key_generate";
    try {
        // Menggunakan fungsi getallheaders() untuk mendapatkan header Authorization
        $headers = getallheaders();
        // var_dump($headers);

        if (!isset($headers['Authorization'])) {
            throw new Exception("Missing Authorization header");
        }

        $authHeader = $headers['Authorization'];

        // Memeriksa apakah $authHeader tidak kosong sebelum menggunakan explode
        if (!empty($authHeader)) {
            // Explode hanya jika $authHeader tidak kosong
            list(, $token) = explode(" ", $authHeader, 2);
        } else {
            throw new Exception("Token is missing");
        }

        $keys = new Key($key, 'HS256');

        // Menyimpan hasil decode ke dalam variabel
        $decoded = JWT::decode($token, $keys);

        $decodedData = (array) $decoded->data;
        // var_dump($decodedData);

        return $decodedData;
    } catch (SignatureInvalidException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        http_response_code(401);
        echo json_encode(array("message" => "Unauthorized. Invalid signature."),JSON_PRETTY_PRINT);
        return null;
    } catch (BeforeValidException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        http_response_code(401);
        echo json_encode(array("message" => "Unauthorized. Token not yet valid."),JSON_PRETTY_PRINT);
        return null;
    } catch (ExpiredException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        http_response_code(401);
        echo json_encode(array("message" => "Unauthorized. Token has expired."),JSON_PRETTY_PRINT);
        return null;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        http_response_code(401);
        echo json_encode(array("message" => "Unauthorized. Invalid or missing token."),JSON_PRETTY_PRINT);
        return null;
    }
}

// Contoh penggunaan
$userData = verifyToken();

if ($userData !== null) {
    // Token valid, lakukan tindakan yang diperlukan

    $con_web_sup = mysqli_connect("localhost", "root", "", "erp_web");

    if (!$con_web_sup) {
        http_response_code(500);
        echo json_encode(array("message" => "Database connection error."),JSON_PRETTY_PRINT);
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
        echo json_encode(array("message" => "Token is valid", "userData" => $userData, "dataTabel" => $dataTabel),JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Error executing query."),JSON_PRETTY_PRINT);
    }

    mysqli_close($con_web_sup);
} else {
    // Token tidak valid
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized. Invalid or missing token."),JSON_PRETTY_PRINT);
}
?>
