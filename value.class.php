<?php
require_once 'libs/database.class.php';

/**
 * Value class:
 * supply id to retrieve
 * supply iid and kid to retrieve
 * supply iid, kid and create=TRUE to create
 */
class Value extends dbTableEntry {
    var $is = "value";

    function __construct ($id, $aid=NULL, $sid=NULL, $kid=NULL, $value=NULL, $create=FALSE) {
        if ($create) {
            if ($aid == NULL) {
                $this->err ("Please supply an account id");
                return;
            } else if ($sid == NULL) {
                $this->err ("Please supply a service id");
                return;
            } else if ($kid == NULL) {
                $this->err ("Please supply a key id");
                return;
            } else if ($value == NULL) {
                $this->err ("Please supply a value");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('aid'=>$aid, 'sid'=>$sid, 'kid'=>$kid, 'value'=>$value), $create);

            if ($this->error) {
                $this->err ("Cannot create value");
                return;
            }
        } else {
            if ($id == NULL) {
                if ($aid == NULL) {
                    $this->err ("Please supply an account id");
                    return;
                } else if ($kid == NULL) {
                    $this->err ("Please supply a network id");
                    return;
                }

                parent::__construct ($this->get_table_name(), array ('aid'=>$aid, 'kid'=>$kid));

                if ($this->error) {
                    $this->err ("Cannot retrieve value with account id '".$aid."' and key id '".$kid."'");
                    return;
                }
            } else {
                parent::__construct ($this->get_table_name(), array ('id'=>$id));

                if ($this->error) {
                    $this->err ("Cannot retrieve value with id '".$id."'");
                    return;
                }
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

    function get_kid () {
        return $this->get_entry_value ('kid');
    }

    function get_value () {
        return $this->get_entry_value ('value');
    }

    function set_value ($value) {
        return $this->set_entry_value ('value', $value);
    }
}
?>
