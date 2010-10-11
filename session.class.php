<?php
require_once 'libs/database.class.php';

/**
 * Session class:
 * supply only id to retrieve session 
 * supply sid and create=TRUE to create service
 */
class Session extends dbTableEntry {
    var $is = "session";

    function __construct ($id, $sid=NULL, $create=FALSE) {
        if ($create) {
            if ($id != NULL) {
                $this->err ("You've specified a session id, with the create flag - that's not right");
                return;
            } else if ($sid == NULL) {
                $this->err ("Please specify a service ID");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('sid'=>$sid, 'challenge'=>md5 (rand().time())), $create);
            if ($this->error) {
                $this->err ("Cannot create new session");
                return;
            }

            return;
        } else {
            if ($id == NULL) {
                $this->err ("Please specify a session id");
                return;
            }

            $where = array('index'=>$id);

            parent::__construct ($this->get_table_name(), $where);

            if ($this->error) {
                $this->err ("Cannot retrieve session");
                return;
            }

            return;
        }
    }

    function get_index () {
        return $this->get_entry_value ('index');
    }

    function get_sid () {
        return $this->get_entry_value ('sid');
    }

    function get_challenge () {
        return $this->get_entry_value ('challenge');
    }

    function get_time () {
        return $this->get_entry_value ('time');
    }
}
?>
