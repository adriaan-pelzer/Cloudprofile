<?php
include 'libs/utils.php';
include 'libs/key.class.php';

function process_key_value_set ($sid, $key, $description) {
    $return = array();

    $key_object = new Key (NULL, $sid, $key, 'virgin', $description, TRUE);

    if ($key_object->error) {
        $return["error"] = "Cannot create new key '".$key."': ".$key_object->error;
        $return["code"] = -1;
        return ($return);
    }

    $return["code"] = 0;
    $return["kid"] = $key_object->get_id();
    return ($return);
}

$return = array();

if (!isset ($_GET['session_id'])) {
    $return['code'] = -1;
    $return["error"] = "Please specify a session ID";
    ret_and_exit ($return);
}

if (!isset ($_GET['auth_hash'])) {
    $return['code'] = -2;
    $return["error"] = "Please specify an authentication hash";
    ret_and_exit ($return);
}

$ret = authenticate ($_GET['session_id'], $_GET['auth_hash']);

if ($ret["code"] != 0) {
    $return['code'] = -4;
    $return["error"] = $ret["error"];
    ret_and_exit ($return);
} else {
    $service = $ret["service"];
}

$return["keys"] = array();

foreach ($_GET as $key=>$value) {
    if (($key != "session_id") && ($key != "auth_hash") && ($key != "oauth_token")) {
        $return["keys"][$key] = process_key_value_set ($service->get_id(), $key, $value);
    }
}

$return["code"] = 0;
ret_and_exit ($return);
?>
