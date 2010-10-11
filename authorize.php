<?php
include 'libs/utils.php';

$return = array();

if (!isset ($_GET['service_id'])) {
    $return['code'] = -1;
    $return['error'] = "Please specify a service ID";
    ret_and_exit ($return);
} else {
    $service = new Service ($_GET['service_id']);

    if ($service->error) {
        $return['code'] = -2;
        $return["error"] = "No service with that service ID: ".$service->error;
        ret_and_exit ($return);
    }

    if ($service->get_status() == "unconfirmed") {
        $return["code"] = -3;
        $return["error"] = "Please confirm this service first";
        ret_and_exit ($return);
    }

    if ($service->get_status() == "suspended") {
        $return["code"] = -4;
        $return["error"] = "This service have been suspended";
        ret_and_exit ($return);
    }

    $session = new Session (NULL, $_GET['service_id'], TRUE);

    if ($session->error) {
        $return['code'] = -5;
        $return["error"] = "Cannot create new session: ".$session->error;
        ret_and_exit ($return);
    }

    $return["code"] = 0;
    $return["session_id"] = $session->get_index();
    $return["challenge"] = $session->get_challenge();
    $service->secret_entered = TRUE;
    $return["auth_hash_to_be_removed"] = md5 ($session->get_challenge().$service->get_secret());
    ret_and_exit ($return);
}
?>
