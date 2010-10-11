<?php
include 'service.class.php';
include 'session.class.php';
include 'network.class.php';
include 'account.class.php';
include 'token.class.php';

function ret_and_exit ($return) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode ($return);
    die();
}

function authenticate ($session_id, $auth_hash) {
    $return = array();

    $session = new Session ($session_id);

    if ($session->error) {
        $return["code"] = -1;
        $return["error"] = $session->error;
        return $return;
    }

    if ((time() - strtotime($session->get_time())) > 3600) {
        $return["code"] = -2;
        $return["error"] = "Session has expired";
        return $return;
    }

    $service = new Service ($session->get_sid());

    if ($service->error) {
        $return["code"] = -3;
        $return["error"] = $service->error;
        return $return;
    }

    $service->secret_entered = TRUE;

    if ($auth_hash != md5 ($session->get_challenge().$service->get_secret())) {
        $return["code"] = -4;
        $return["error"] = "Authentication hash does not match service credentials";
        return $return;
    }

    $return["code"] = 0;
    $return["service"] = $service;
    return $return;
}

function check_token ($token, $sid) {
    $return = array();

    $token_object = new Token ($token);

    if ($token_object->error) {
        $return["code"] = -1;
        $return["error"] = $token_object->error;
        return ($return);
    }

    if ($token_object->get_sid() != $sid) {
        $return["code"] = -2;
        $return["error"] = "This token belongs to a different service ID";
        return ($return);
    }

    $account_object = new Account ($token_object->get_aid());

    if ($account_object->error) {
        $return["code"] = -3;
        $return["error"] = $account_object->error;
        return ($return);
    }

    $return["code"] = 0;
    $return["account"] = $account_object;
    $return["token"] = $token_object;
    return ($return);
}

function create_account ($service_id, $user_id, $access_token) {
    $return = array();

    $network = new Network (NULL, "twitter");

    if ($network->error) {
        $return["code"] = -1;
        $return["error"] = $network->error;
        return $return;
    }

    $account = new Account (NULL, $network->get_id(), $user_id);

    if ($account->error) {
        if (!strncmp ($account->error, "Cannot retrieve account", strlen ("Cannot retrieve account"))) {
            $account = new Account (NULL, $network->get_id(), $user_id, TRUE);

            if ($account->error) {
                $return["code"] = -2;
                $return["error"] = $account->error;
                return $return;
            }
        } else {
            $return["code"] = -2;
            $return["error"] = $account->error;
            return $return;
        }
    }

    $token = new Token ($access_token);

    if ($token->error) {
        if (!strncmp ($token->error, "Cannot retrieve token", strlen ("Cannot retrieve token"))) {
            $token = new Token ($access_token, $account->get_id (), $service_id, TRUE);

            if ($token->error) {
                $return["code"] = -3;
                $return["error"] = $token->error;
                return $return;
            }
        } else {
            $return["code"] = -4;
            $return["error"] = $token->error;
            return $return;
        }
    }

    $return["code"] = 0;
    $return["account"] = $account;
    $return["token"] = $token;
    return $return;
}
?>
