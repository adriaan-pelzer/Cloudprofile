<?php
include 'libs/EpiCurl.php';
include 'libs/EpiOAuth.php';
include 'libs/EpiTwitter.php';
include 'libs/utils.php';

$error = NULL;

session_start();

$consumer_key = 'U65NSjk1IEgDJGCXjp1og';
$consumer_secret = 'fYcqAxAsa57z8V8OjZ2gJPikrZffodFCZ7OoaR74ug';

if (isset ($_GET['logout'])) {
	setcookie('oauth_token', '', -3600);
	setcookie('oauth_token_secret', '', -3600);
    unset ($_COOKIE['oauth_token']);
    unset ($_COOKIE['oauth_token_secret']);
    unset ($_SESSION['service_id']);
    unset ($_SESSION['redirect_url']);
    echo "Logged out";
	die();
}

if (isset ($_GET['oauth_token'])) {
    if (isset ($_COOKIE['oauth_token']) && ($_COOKIE['oauth_token'] == $_GET['oauth_token'])) {
        $twitterObj = new EpiTwitter($consumer_key, $consumer_secret, $_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);  
    } else {
        $twitterObj = new EpiTwitter($consumer_key, $consumer_secret);

        $twitterObj->setToken ($_GET['oauth_token']);
        $token = $twitterObj->getAccessToken();  
        $twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);  
        setcookie('oauth_token', $token->oauth_token);  
        setcookie('oauth_token_secret', $token->oauth_token_secret); 

        //$twitterObj = new EpiTwitter($consumer_key, $consumer_secret, $token->oauth_token, $token->oauth_token_secret);  
    }

    try {
        $twitterInf = $twitterObj->get_accountVerify_credentials();
    } catch (EpiTwitterException $e) {
        echo 'We caught an EpiOAuthException'."<br />\n";
        echo $e->getMessage();
    } catch (Exception $e) {
        echo 'We caught an unexpected Exception'."<br />\n";
        echo $e->getMessage();
    }

    //print_r ($twitterInf->response);
    //die();

    $return = create_account ($_SESSION['service_id'], $twitterInf->response["id"], $_GET['oauth_token']);

    if ($return["code"] != 0) {
        echo $return["error"];
        die();
    } else {
        $account = $return["account"];
    }

    header ("Location: ".$_SESSION['redirect_url']."?oauth_token=".$_GET['oauth_token']);
    die();
}

if (!isset ($_GET['auth_hash'])) {
    echo "Please specify an authentication hash";
    die();
}

if (!isset ($_GET['session_id'])) {
    echo "Please specify a session id";
    die();
}

if (!isset ($_GET['redirect_url'])) {
    echo "Please specify a redirect URL";
    die();
}

$_SESSION['redirect_url'] = $_GET['redirect_url'];

$return = authenticate ($_GET['session_id'], $_GET['auth_hash']);

if ($return["code"] != 0) {
    echo $return["error"];
    die();
} else {
    $service = $return["service"];
}

$_SESSION['service_id'] = $service->get_id();

if (!($service->set_redirect_url ($_GET['redirect_url']))) {
    echo $service->error;
    die();
}

if (isset ($_COOKIE['oauth_token']) && isset ($_COOKIE['oauth_token_secret']) && ($_COOKIE['oauth_token'] != '') && ($_COOKIE['oauth_token_secret'] != '')) {
    $twitterObj = new EpiTwitter($consumer_key, $consumer_secret, $_COOKIE['oauth_token'], $_COOKIE['oauth_token_secret']);  

    try {
        $twitterInf = $twitterObj->get_accountVerify_credentials();
    } catch (EpiTwitterException $e) {
        echo 'We caught an EpiOAuthException'."<br />\n";
        echo $e->getMessage();
        //header ("Location: ".$_SERVER['SCRIPT_NAME']."?session_id=".$_GET['session_id']."&auth_hash=".$_GET['auth_hash']);
        //die();
    } catch (Exception $e) {
        echo 'We caught an unexpected Exception'."<br />\n";
        echo $e->getMessage();
    }

    //print_r ($twitterInf->response);
    //die();

    $return = create_account ($_SESSION['service_id'], $twitterInf->response["id"], $_COOKIE['oauth_token']);

    if ($return["code"] != 0) {
        echo $return["error"];
        die();
    } else {
        $account = $return["account"];
    }

    header ("Location: ".$_SESSION['redirect_url']."?oauth_token=".$_COOKIE['oauth_token']);
    die();
} else {
    $twitterObj = new EpiTwitter($consumer_key, $consumer_secret);

    header ("Location: ".$twitterObj->getAuthenticateUrl());
    die();
}

?>
