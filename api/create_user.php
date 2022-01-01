<?php
// required headers
header("Access-Control-Allow-Origin: http://localhost/mft_api/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// files needed to connect to database
include_once 'config/database.php';
include_once 'objects/user.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// instantiate user object
$user = new User($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// valid fullname
if (strlen($data->fullname) < 5 || strlen($data->fullname) > 100) {
    http_response_code(400);
    echo json_encode(array("message" => "Name must be greater than 5 and less than 100."));
    return false;
}
// valid email
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("message" => "Email is invalid."));
    return false;
}
// valid password
if (strlen($data->password) < 8) {
    http_response_code(400);
    echo json_encode(array("message" => "Password must be greater than 8 characters."));
    return false;
}

// set user property values
$user->fullname = $data->fullname;
$user->email = $data->email;
$user->password = $data->password;

if ($user->emailExists()) {
    http_response_code(400);
    echo json_encode(array("message" => "User is already exists."));
    return false;
} else {
    if ($user->create()) {
        http_response_code(200);
        echo json_encode(array("message" => "User was created."));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create user."));
    }
}
