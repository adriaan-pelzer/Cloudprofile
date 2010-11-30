<?php
include 'libs/utils.php';
include 'libs/key.class.php';

function process_key_value_set ($sid, $key, $description) {
    $return = array();

    $key_object = new Key (NULL, $sid, $key, 'virgin', $description, TRUE);

    if ($key_object->error) {
        if (strpos ($key_object->error, "Duplicate entry") === false) {
            $return["error"] = "Cannot create new key '".$key."': ".$key_object->error;
            $return["code"] = -1;
            return ($return);
        } else {
            $key_object = new Key (NULL, NULL, $key);

            if ($key_object->error) {
                $return["error"] = "Cannot retrieve key '".$key."': ".$key_object->error;
                $return["code"] = -1;
                return ($return);
            } else {
                switch ($key_object->get_approved()) {
                case 'virgin':
                    $return["error"] = "Key '".$key."' has already been created, and approval is pending";
                    $return["code"] = -1;
                    return ($return);
                    break;
                case 'approved':
                    $return["error"] = "Key '".$key."' has already been created and approved";
                    $return["code"] = -1;
                    return ($return);
                    break;
                case 'rejected':
                    if (!($key_object->set_sid ($sid))) {
                        $return["error"] = "Can't reset sid on key '".$key."': ".$key_object->error;
                        $return["code"] = -1;
                        return ($return);
                    }
                    if (!($key_object->set_approved ('virgin'))) {
                        $return["error"] = "Can't reset approval state on key '".$key."': ".$key_object->error;
                        $return["code"] = -1;
                        return ($return);
                    }
                    if (!($key_object->set_description ($description))) {
                        $return["error"] = "Can't reset description on key '".$key."': ".$key_object->error;
                        $return["code"] = -1;
                        return ($return);
                    }
                    break;
                default:
                    $return["error"] = "Something BIG went wrong - please contact an administrator and say: 'AP5SDX'.".$key_object->approved;
                    $return["code"] = -9;
                    return ($return);
                    break;
                }
            }
        }
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
