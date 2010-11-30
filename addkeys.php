<?php
include 'libs/utils.php';
include 'libs/key.class.php';
include 'libs/value.class.php';

function process_key_value_set ($sid, $aid, $key, $value) {
    $return = array();

    $key_object = new Key (NULL, $key);

    if ($key_object->error) {
        if (!strncmp ($key_object->error, "Cannot retrieve key", strlen ("Cannot retrieve key"))) {
            $return["error"] = "key '".$key."' does not exist yet. Please create it using the key creation interface.";
            $return["code"] = -1;
            $return["value"] = $value;
            return ($return);
            /*$key_object = new Key (NULL, $key, TRUE);

            if ($key_object->error) {
                $return["value"] = $value;
                $return["code"] = -1;
                $return["error"] = "Cannot create new key '".$key."': ".$key_object->error;
                return ($return);
            }*/
        } else {
            $return["value"] = $value;
            $return["code"] = -2;
            $return["error"] = "Cannot check if key '".$key."' exists: ".$key_object->error;
            return ($return);
        }
    }

    /* Check if key have been approved */
    switch ($key_object->get_approved ()) {
    case 'virgin':
        $return["error"] = "This key has not been approved yet. Please try again later";
        $return["code"] = -7;
        $return["value"] = $value;
        return ($return);
        break;
    case 'rejected':
        $return["error"] = "This key has been rejected. Please contact us if you want to know why.";
        $return["code"] = -8;
        $return["value"] = $value;
        return ($return);
        break;
    case 'approved':
        break;
    default:
        $return["error"] = "Something BIG went wrong - please contact an administrator and say: 'AP5SDX'.".$key_object->approved;
        $return["code"] = -9;
        $return["value"] = $value;
        return ($return);
        break;
    }

    $value_object = new Value (NULL, $aid, NULL, $key_object->get_id());

    if ($value_object->error) {
        if (!strncmp ($value_object->error, "Cannot retrieve value", strlen ("Cannot retrieve value"))) {
            $value_object = new Value (NULL, $aid, $sid, $key_object->get_id(), $value, TRUE);

            if ($value_object->error) {
                $return["kid"] = $key_object->get_id();
                $return["value"] = $value;
                $return["code"] = -3;
                $return["error"] = "Cannot create new value '".$value."': ".$value_object->error;
                return ($return);
            }
        } else {
            $return["kid"] = $key_object->get_id();
            $return["value"] = $value;
            $return["code"] = -4;
            $return["error"] = "Cannot check if value '".$value."' exists: ".$value_object->error;
            return ($return);
        }
    } else {
        /*if ($value_object->get_sid() != $sid) {
            $return["kid"] = $key_object->get_id();
            $return["vid"] = $value_object->get_id();
            $return["value"] = $value_object->get_value();
            $return["code"] = -5;
            $return["error"] = "This value is not writeable by service ID ".$sid;
            return ($return);
        }*/

        if (!($value_object->set_value ($value))) {
            $return["kid"] = $key_object->get_id();
            $return["vid"] = $value_object->get_id();
            $return["value"] = $value_object->get_value();
            $return["code"] = -6;
            $return["error"] = "Cannot update value '".$value."': ".$value_object->error;
            return ($return);
        }
    }

    $return["value"] = $value;
    $return["code"] = 0;
    $return["kid"] = $key_object->get_id();
    $return["vid"] = $value_object->get_id();
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

if (!isset ($_GET['oauth_token'])) {
    $return['code'] = -3;
    $return["error"] = "Please specify an oauth token";
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

$ret = check_token ($_GET['oauth_token'], $service->get_id());

if ($ret["code"] != 0) {
    $return['code'] = -5;
    $return["error"] = $ret["error"];
    ret_and_exit ($return);
} else {
    $account = $ret["account"];
}

$return["keys"] = array();

foreach ($_GET as $key=>$value) {
    if (($key != "session_id") && ($key != "auth_hash") && ($key != "oauth_token")) {
        $return["keys"][$key] = process_key_value_set ($service->get_id(), $account->get_id(), $key, $value);
    }
}

$return["code"] = 0;
ret_and_exit ($return);
?>
