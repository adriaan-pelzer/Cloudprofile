<?php
require_once 'libs/database.class.php';

/**
 * Key class:
 * supply id to retrieve
 * supply key to retrieve
 * supply key and create=TRUE to create
 */
class Key extends dbTableEntry {
    var $is = "key";

    function __construct ($id, $key=NULL, $create=FALSE) {
        if ($create) {
            if ($key == NULL) {
                $this->err ("Please supply a key");
                return;
            }
            parent::__construct ($this->get_table_name(), array ('key'=>$key), $create);
            if ($this->error) {
                $this->err ("Cannot create key with key '".$key."'");
                return;
            }
        } else {
            if ($id == NULL) {
                if ($key == NULL) {
                    $this->err ("Please supply a key");
                    return;
                }
                parent::__construct ($this->get_table_name(), array ('key'=>$key));
                if ($this->error) {
                    $this->err ("Cannot retrieve key with key '".$key."'");
                    return;
                }
            } else {
                parent::__construct ($this->get_table_name(), array ('id'=>$id));
                if ($this->error) {
                    $this->err ("Cannot retrieve key with id '".$id."'");
                    return;
                }
            }
        }
    }

    function get_id () {
        return $this->get_entry_value ('id');
    }

    function get_key () {
        return $this->get_entry_value ('key');
    }
}
?>
