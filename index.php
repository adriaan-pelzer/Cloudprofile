<?php
require_once 'libs/session.class.php';
require_once 'libs/service.class.php';

function call_api ($url) {
    $base_url = "http://omnii.wewillraakyou.com";
    $ch = curl_init ($base_url.$url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data_json = curl_exec ($ch);
    $data = json_decode ($data_json);
    return $data;
}

session_start ();

$error = array();

if (isset ($_GET['error'])) {
    $error["general"] = $_GET["error"];
} else if (isset ($_GET['logout'])) {
    unset ($_SESSION['session_id']);
    unset ($_SESSION['auth_hash']);
    session_destroy ();
} else if (isset ($_GET['success'])) {
    $error["success"] = $_GET["success"];
} else if (isset ($_POST['register'])) {
    if (!isset ($_POST['email']) || ($_POST['email'] == "")) {
        $error["email"] = "Please enter an email address";
    }

    if (!isset ($_POST['name']) || ($_POST['name'] == "")) {
        $error["name"] = "Please enter a service name";
    }

    if (sizeof ($error) == 0) {
        $return_arr = call_api (str_replace (" ", "+", "/addservice.php?email=".$_POST['email']."&name=".$_POST['name']));
        if ($return_arr->code != 0) {
            $error["general"] = $return_arr->error;
        } else {
            $error["success"] = $return_arr->message;
        }
    }
} else if (isset ($_POST['login'])) {
    if (!isset ($_POST['service_id'])) {
        $error["service_id"] = "Please enter your service ID";
    } else if (!isset ($_POST['service_secret'])) {
        $error["service_secret"] = "Please enter your secret key";
    } else {
        $return_arr = call_api ("/authorize.php?service_id=".$_POST['service_id']);

        if ($return_arr->code != 0) {
            $error["general"] = $return_arr->error;
        } else {
            $_SESSION['session_id'] = $return_arr->session_id;
            $_SESSION['auth_hash'] = md5 ($return_arr->challenge.$_POST['service_secret']);
        }
    }
}

if (isset ($_SESSION['session_id']) && isset ($_SESSION['auth_hash'])) {
    $session = new Session ($_SESSION['session_id']);

    if ($session->error) {
        $error["general"] = $session->error;
    } else {
        if ((time() - strtotime ($session->get_time ())) > 3600) {
            unset ($_SESSION['session_id']);
            unset ($_SESSION['auth_hash']);
            session_destroy ();
            header ("Location: http://omnii.wewillraakyou.com/index.php?error=Your+session+has+expired");
            die();
        }

        $service = new Service ($session->get_sid());

        if ($service->error) {
            $error['general'] = $service->error;
        }
    }
}

if (isset ($_POST['addkey'])) {
    if (!(isset ($_POST['key_name']))) {
        $error['key_name'] = "Please enter a field name";
    } else {
        $return_arr = call_api ("/addkeys.php?session_id=".$_SESSION['session_id']."&auth_hash=".$_SESSION['auth_hash']."&".$_POST['key_name']."=".$_POST['key_description']);

        foreach ($return_arr->keys as $key) {
            if ($key->code != 0) {
                $error["general"] = $key->error;
            } else {
                $error["success"] = "Your field was requested successfully - you'll be notified when it is approved or rejected";
            }
        }
    }
}

include "header.php";
?>
        <section id="content">
            
            <p>
                Do what you do best and link to the rest. Using Twitter authentication as a basis, OMNII aims to be an open platform for sharing user profile information between web services, putting the user in control. <a href="about.php">Read more</a>.
            </p>
            
            <p>
                OMNII allows you to create a profile for each user, using any existing fields or register new ones you may need. Users can then authenticate &amp; edit their profiles - through you - using Twitter.
                You will be able to read data created by other services too, though.
            </p>
            
            <p>
                Submit your email address and an application name below to register an application. Authentication details will then be emailed to you.
            </p>
<?php
if (isset ($error["success"])) {
?>
            <div class="error"><?php echo $error["success"]; ?></div>
<?php
} else {
    if (isset ($error["general"])) {
?>
            <div class="error"><?php echo $error["general"]; ?></div>
<?php
    }

    if (isset ($_SESSION['session_id']) && isset ($_SESSION['auth_hash'])) {
?>
            <p>
            Welcome, <?php echo $service->get_name(); ?>! <a href="?logout=true">Log Out</a>
            </p>
            <form action="index.php" method="post" name="add_key">
                <input type="text" name="key_name" placeholder="New Field Name" required id="key_name" <?if (isset ($_POST['key_name'])) { echo "value=\"".$_POST['key_name']."\" "; } ?>/>
<?php
    if (isset ($error["key_name"])) {
?>
                <div class="error"><?php echo $error["key_name"]; ?></div>
<?php
    }
?>
                <textarea name="key_description" placeholder="Tell us a bit more ..." required id="key_description"><?if (isset ($_POST['key_description'])) { echo $_POST['key_description']; } ?></textarea>
<?php
    if (isset ($error["key_description"])) {
?>
                <div class="error"><?php echo $error["key_description"]; ?></div>
<?php
    }
?>
                <span id="button_container">
                    Request Field
                    <input name="addkey" type="submit" value="Request Field" id="addkey" />
                </span>
            </form>
<?php
        $key_table = new dbTable ('keys');
        
        if ($key_table->error) {
            $error["keys"] = $key_table->error;
        } else if (!($keys = $key_table->retrieve (1))) {
            $error["keys"] = $key_table->error;
        }

        if (isset ($error["keys"])) {
?>
                <div class="error"><?php echo $error["keys"]; ?></div>
<?php
        } else {
            foreach ($keys as $key) {
?>
    <p class="keys"><span class="keyname"><?php echo $key['key']; ?></span> : <span class="keystatus"><?php echo $key['approved']; ?></span></p>
<?php
                if (isset ($key['description'])) {
?>
                <p class="keys keydescription"><?php echo $key['description']; ?></p>
<?php
                }
            }
        }
    } else {
?>
            <form action="index.php" method="post" name="log_in">
                <input type="text" name="service_id" placeholder="Service ID" required id="service_id" <?if (isset ($_POST['service_id'])) { echo "value=\"".$_POST['service_id']."\" "; } ?>/>
<?php
    if (isset ($error["service_id"])) {
?>
                <div class="error"><?php echo $error["service_id"]; ?></div>
<?php
    }
?>
                <input type="password" name="service_secret" placeholder="Secret Key"  required id="service_secret" <?if (isset ($_POST['service_secret'])) { echo "value=\"".$_POST['service_secret']."\" "; } ?>/>
<?php
    if (isset ($error["service_secret"])) {
?>
                <div class="error"><?php echo $error["service_secret"]; ?></div>
<?php
    }
?>
                <span id="button_container">
                    Login
                    <input name="login" type="submit" value="Login" id="login" />
                </span>
            </form>
            <p>
                Not registered yet? <a href="register.php">Register Here</a>
            </p>
<?php
    }
}
?>
        </section>
<?php
include "footer.php";
?>
