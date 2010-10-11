<?php
include 'libs/service.class.php';

function ret_and_exit ($return) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode ($return);
    die();
}

$return = array();

if (!isset ($_GET['nonce'])) {
    $return["code"] = -1;
    $return["error"] = "Please supply a nonce";
    ret_and_exit ($return);
} else if (!isset ($_GET['id'])) {
    $return["code"] = -2;
    $return["error"] = "Please supply a service ID";
    ret_and_exit ($return);
}

$service = new Service ($_GET['id']);

if ($service->error) {
    $return["code"] = -3;
    $return["error"] = "No such service: ".$service->error;
    ret_and_exit ($return);
}

$service->secret_entered = TRUE;

if ($_GET['nonce'] != md5 ($service->get_name().$service->get_secret())) {
    $return["code"] = -4;
    $return["error"] = "Nonce does not match service credentials";
    ret_and_exit ($return);
}

if (!($service->set_status ("confirmed"))) {
    $return["code"] = -5;
    $return["error"] = "Cannot confirm service: ".$service->error;
    ret_and_exit ($return);
}

$return["code"] = 0;
$return["message"] = "Service confirmed successfully";
ret_and_exit ($return);
?>
