<?php
include 'libs/utils.php';

$base_url = "http://omnii.wewillraakyou.com";
$return = array();

if (!isset ($_GET['email'])) {
    $return["code"] = -1;
    $return["error"] = "Please supply an email address";
    ret_and_exit ($return);
} else if (!isset ($_GET['name'])) {
    $return["code"] = -2;
    $return["error"] = "Please supply a service name";
    ret_and_exit ($return);
}

$service = new Service (NULL, $_GET['name'], $_GET['email']);

if ($service->error) {
    if (!strncmp ($service->error, "Cannot retrieve service", strlen ("Cannot retrieve service"))) {
        $service = new Service (NULL, $_GET['name'], $_GET['email'], NULL, TRUE);

        if ($service->error) {
            $return["code"] = -3;
            $return["error"] = "Cannot create service: ".$service->error;
            ret_and_exit ($return);
        }
    } else {
        $return["code"] = -4;
        $return["error"] = "Error retrieving service: ".$service->error;
        ret_and_exit ($return);
    }
} else {
    $service->secret_entered = TRUE;
}

$confirmation_url = $base_url."/confirmservice.php?nonce=".md5 ($service->get_name().$service->get_secret());
$confirmation_url .= "&id=".$service->get_id();

$subject = "New OmniI Service: ".$service->get_name();
$message = "You've registered a new OmniI service: ".$service->get_name()."\n\n";
$message .= "Visit the following url to confirm your registration: ".$confirmation_url."\n\n";
$message .= "Service ID: ".$service->get_id()."\n";
$message .= "Service Secret: ".$service->get_secret()."\n";

if (!$service->send_email ($subject, $message)) {
    $return["code"] = -5;
    $return["error"] = "Cannot send email";
    ret_and_exit ($return);
}

if (isset ($_GET['description'])) {
    if (!$service->set_description ($_GET['description'])) {
        $return["code"] = -6;
        $return["error"] = "Cannot set service description: ".$service->error;
        ret_and_exit ($return);
    }
}

$return["code"] = 0;
$return["message"] = "Please check your email to confirm registration";
ret_and_exit ($return);
?>
