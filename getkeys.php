<?php
include 'libs/utils.php';
include 'libs/key.class.php';
include 'libs/value.class.php';

function process_key_value_set ($token, $value_id) {
    $return = array();

    $value_object = new Value ($value_id);

    if ($value_object->error) {
        if (!strncmp ($value_object->error, "Cannot retrieve value", strlen ("Cannot retrieve value"))) {
            $return["code"] = -3;
            $return["error"] = "Cannot find value '".$value."': ".$value_object->error;
            return ($return);
        } else {
            $return["code"] = -4;
            $return["error"] = "Cannot check if value '".$value."' exists: ".$value_object->error;
            return ($return);
        }
    /*} else {
        if ($value_object->get_aid() != $token->get_aid()) {
            $return["code"] = -5;
            $return["error"] = "Value account id did not match the one provided";
            return ($return);
        }*/
    }

    $key_object = new Key ($value_object->get_kid());

    if ($key_object->error) {
        $return["code"] = -1;
        $return["error"] = $key_object->error;
        return ($return);
    }

    $return["code"] = 0;
    //$return["kid"] = $key_object->get_id();
    //$return["vid"] = $value_object->get_id();
    $return["key"] = $key_object->get_key();
    $return["value"] = $value_object->get_value();
    $return["writable"] = ($value_object->get_sid() == $token->get_sid())?"1":"0";
    return $return;
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

if (!isset ($_GET['show_all'])) {
    $show_all = FALSE;
} else {
    $show_all = TRUE;
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
    $token = $ret["token"];
}

$return["keys"] = array();

$values_table = new dbTable ("values");

if ($values_table->error) {
    $return["code"] = -6;
    $return["error"] = $values_table->error;
    ret_and_exit ($return);
}

//print_r ($values_table->retrieve (array ('aid'=>$account->get_id())));

$i = 0;

$values = ($values_table->retrieve (($show_all?array ('aid'=>$account->get_id()):'1')));

foreach ($values as $value) {
    print_r ($value);
    echo "<br />";
    $returnval = process_key_value_set ($token, $value["id"]);
    $return["keys"][$returnval["key"]] = $returnval;
}

ret_and_exit ($return);
?>
