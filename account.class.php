<?php
require_once 'libs/database.class.php';

/**
 * Account class:
 * supply id to retrieve
 * supply iid and nid to retrieve
 * supply iid, nid and create=TRUE to create
 */
class Account extends dbTableEntry {
    var $is = "account";

    function __construct ($id, $nid=NULL, $uid=NULL, $create=FALSE) {
        if ($create) {
            if ($nid == NULL) {
                $this->err ("Please supply a network id");
                return;
            } else if ($uid == NULL) {
                $this->err ("Please supply a user id");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('nid'=>$nid, 'uid'=>$uid), $create);

            if ($this->error) {
                $this->err ("Cannot create new account");
                return;
            }
        } else {
            if ($id == NULL) {
                if ($uid == NULL) {
                    $this->err ("Please supply a user id");
                    return;
                } else if ($nid == NULL) {
                    $this->err ("Please supply a network id");
                    return;
                }
                parent::__construct ($this->get_table_name(), array ('uid'=>$uid, 'nid'=>$nid));
                if ($this->error) {
                    $this->err ("Cannot retrieve account with uid '".$uid."' and nid '".$nid."'");
                    return;
                }
            } else {
                parent::__construct ($this->get_table_name(), array ('id'=>$id));
                if ($this->error) {
                    $this->err ("Cannot retrieve account with id '".$id."'");
                    return;
                }
            }
        }
    }

    function get_id () {
        return $this->get_entry_value ('id');
    }

    function get_nid () {
        return $this->get_entry_value ('nid');
    }

    function get_uid () {
        return $this->get_entry_value ('uid');
    }
}
?>
