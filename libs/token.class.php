<?php
require_once 'libs/database.class.php';

/**
 * Token class:
 * supply token to retrieve
 * supply aid, sid, token and create=TRUE to create
 */
class Token extends dbTableEntry {
    var $is = "token";

    function __construct ($token, $aid=NULL, $sid=NULL, $create=FALSE) {
        if ($create) {
            if ($aid == NULL) {
                $this->err ("Please supply an account id");
                return;
            } else if ($sid == NULL) {
                $this->err ("Please supply a service id");
                return;
            } else if ($token == NULL) {
                $this->err ("Please supply a token");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('aid'=>$aid, 'sid'=>$sid, 'token'=>$token), $create);

            if ($this->error) {
                $this->err ("Cannot create new token");
                return;
            }
        } else {
            if ($token == NULL) {
                $this->err ("Please supply a token");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('token'=>$token));

            if ($this->error) {
                $this->err ("Cannot retrieve token");
                return;
            }
        }
    }

    function get_id () {
        return $this->get_entry_value ('id');
    }

    function get_aid () {
        return $this->get_entry_value ('aid');
    }

    function get_sid () {
        return $this->get_entry_value ('sid');
    }

    function set_sid ($sid) {
        return $this->set_entry_value ('sid', $sid);
    }

    function get_token () {
        return $this->get_entry_value ('token');
    }

    function set_token ($token) {
        return $this->set_entry_value ('token', $token);
    }
}
?>
