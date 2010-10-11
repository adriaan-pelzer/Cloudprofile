<?php
require_once 'libs/database.class.php';

/**
 * Service class:
 * supply only id to retrieve service
 * supply id and secret to verify service
 * supply name, email and create=TRUE to create service
 */
class Service extends dbTableEntry {
    var $is = "service";
    var $secret_entered = FALSE;

    function __construct ($id, $name=NULL, $email=NULL, $secret=NULL, $create=FALSE) {
        if (($secret != NULL) && (strlen($secret) != 32)) {
            $this->err ("Secret has to be an md5 calculated string");
            return;
        }

        if ($create) {
            if ($id != NULL) {
                $this->err ("You've specified a service id, with the create flag - that's not right");
                return;
            } else if ($name == NULL) {
                $this->err ("Please specify a name for the service");
                return;
            } else if ($email == NULL) {
                $this->err ("Please specify your email address");
                return;
            }

            $id = md5 ($name.time());
            $secret = md5 ($id.time());

            parent::__construct ($this->get_table_name(), array ('name'=>$name, 'id'=>$id, 'secret'=>$secret, 'email'=>$email), $create);
            if ($this->error) {
                $this->err ("Cannot create new user");
                return;
            }

            $this->secret_entered = TRUE;

            return;
        } else {
            if ($id == NULL) {
                if (($email == NULL) && ($name == NULL)) {
                    $this->err ("Please specify a service id or email and name");
                    return;
                }
            }

            $where = array();

            if ($id != NULL) {
                $where["id"] = $id;
            }

            if ($name != NULL) {
                $where["name"] = $name;
            }

            if ($email != NULL) {
                $where["email"] = $email;
            }

            if ($secret != NULL) {
                $where["secret"] = $secret;
            }

            parent::__construct ($this->get_table_name(), $where);

            if ($this->error) {
                $this->err ("Cannot retrieve service");
                return;
            }

            if ($secret != NULL) {
                $this->secret_entered = TRUE;
            }

            return;
        }
    }

    function get_name () {
        return $this->get_entry_value ('name');
    }

    function get_description () {
        return $this->get_entry_value ('description');
    }

    function get_id () {
        return $this->get_entry_value ('id');
    }

    function get_secret () {
        if ($this->secret_entered) {
            return $this->get_entry_value ('secret');
        } else {
            $this->err ("You have no access rights to the secret if it wasn't entered in the first place");
            return FALSE;
        }
    }

    function get_email () {
        return $this->get_entry_value ('email');
    }

    function get_status () {
        return $this->get_entry_value ('status');
    }

    function get_redirect_url () {
        return $this->get_entry_value ('redirect_url');
    }

    function set_name ($name) {
        return $this->set_entry_value ('name', $name);
    }

    function set_description ($description) {
        return $this->set_entry_value ('description', $description);
    }

    function set_secret ($newsecret, $oldsecret) {
        if ($this->get_secret() != $oldsecret) {
            $this->err ("old secret does not match");
            return FALSE;
        } else {
            return $this->set_entry_value ('secret', $newsecret);
        }
    }

    function set_email ($email) {
        return $this->set_entry_value ('email', $email);
    }

    function set_status ($status) {
        return $this->set_entry_value ('status', $status);
    }

    function set_redirect_url ($redirect_url) {
        return $this->set_entry_value ('redirect_url', $redirect_url);
    }

    function send_email ($subject, $message) {
        $to = $this->name." <".$this->get_email().">";

        $headers = "To: ".$to."\r\n";
        $headers .= "From: RAAK <adriaan@wewillraakyou.com>\r\n";
        $headers .= "Reply-To: RAAK <adriaan@wewillraakyou.com>\r\n";
        $headers .= "X-Mailer: PHP/".phpversion();

        return mail ($to, $subject, $message, $headers);
    }
}
?>
