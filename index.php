<?php
function call_api ($url) {
    $ch = curl_init ($url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data_json = curl_exec ($ch);
    $data = json_decode ($data_json);
    return $data;
}

$error = array();

if (isset ($_POST['register'])) {
    if (!isset ($_POST['email']) || ($_POST['email'] == "")) {
        $error["email"] = "Please enter an email address";
    }

    if (!isset ($_POST['name']) || ($_POST['name'] == "")) {
        $error["name"] = "Please enter a service name";
    }

    if (sizeof ($error) == 0) {
        $return_arr = call_api ("http://omnii.wewillraakyou.com/addservice.php?email=".$_POST['email']."&name=".$_POST['name']);
        if ($return_arr["code"] != 0) {
            $error["general"] = $return_arr["error"];
        } else {
            $error["success"] = $return_arr["message"];
        }
    }
}

include "header.php";
?>
        <section id="content">
            <p>
                When you build Twitter Applications, OMNII allows you to create a profile for each user, using any fields you may need. Once you have created and populated a field for a user, the user will only be able to edit it through you. You wil be able to read data created by other services too, though.
            </p>
            <p>
                Submit your email address and an application name below to register an application. Authentication details will then be emailed to the email address you provide.
            </p>
<?php
if (isset ($error["success"])) {
?>
            <p><?php echo $error["success"]; ?></p>
<?php
} else {
    if (isset ($error["general"])) {
?>
            <div class="error"><?php echo $error["general"]; ?></div>
<?php
    }
?>
            <form action="index.php" method="post" name="shorten">
                <input type="text" name="email" placeholder="email address" required id="email" <?if (isset ($_POST['email'])) { echo "value=\"".$_POST['email']."\" "; } ?>/>
<?php
    if (isset ($error["email"])) {
?>
                <div class="error"><?php echo $error["email"]; ?></div>
<?php
    }
?>
                <input type="text" name="name" placeholder="service name"  required id="name" <?if (isset ($_POST['name'])) { echo "value=\"".$_POST['name']."\" "; } ?>/>
<?php
    if (isset ($error["name"])) {
?>
                <div class="error"><?php echo $error["name"]; ?></div>
<?php
    }
?>
                <span id="button_container">
                    Register
                    <input name="register" type="submit" value="Register" id="register" />
                </span>
            </form>
<?php
}
?>
        </section>
<?php
include "footer.php";
?>
