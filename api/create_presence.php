<?php
// required headers
header("Access-Control-Allow-Origin: http://localhost/mft_api/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'config/core.php';
include_once 'libs/php-jwt-main/src/BeforeValidException.php';
include_once 'libs/php-jwt-main/src/ExpiredException.php';
include_once 'libs/php-jwt-main/src/SignatureInvalidException.php';
include_once 'libs/php-jwt-main/src/JWT.php';

use \Firebase\JWT\JWT;

// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/presence.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// instantiate user object
$presence = new Presence($db);

$data = json_decode(file_get_contents("php://input"));

$jwt = isset($data->jwt) ? $data->jwt : "";

if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, $key, array('HS256'));

        $presence->description = $data->description;
        $presence->who = $data->who;
        $presence->start_time = $data->start_time;
        $presence->end_time = $data->end_time;
        $presence->user_id = (int) $decoded->data->id;

        var_dump($presence);

        if (
            !empty($presence->user_id) &&
            !empty($presence->description) &&
            !empty($presence->who) &&
            !empty($presence->start_time) &&
            !empty($presence->end_time) &&
            $presence->create()
        ) {
            http_response_code(200);

            echo json_encode(array("message" => "Log has been recorded."));
        } else {
            http_response_code(400);

            echo json_encode(array("message" => "Unable to create log."));
        }
    }
    catch (Exception $e) {
        http_response_code(401);

        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
}

