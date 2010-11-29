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

    function __construct ($id, $key=NULL, $tid=NULL, $description=NULL, $create=FALSE) {
        if ($create) {
            if ($key == NULL) {
                $this->err ("Please supply a key");
                return;
            }

            if ($tid == NULL) {
                $this->err ("Please supply a type id");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('key'=>$key, 'tid'=>$tid, 'description'=>$description), $create);
            if ($this->error) {
                $this->err ("Cannot create key with key '".$key."', tid '".$tid."', description '".$description."'");
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

    function get_tid () {
        return $this->get_entry_value ('tid');
    }

    function get_description () {
        return $this->get_entry_value ('description');
    }

    function set_description ($description) {
        return $this->get_entry_value ('description', $description);
    }
}
?>
