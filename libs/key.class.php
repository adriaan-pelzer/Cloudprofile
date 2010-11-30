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

    function __construct ($id, $key=NULL, $approved=NULL, $description=NULL, $create=FALSE) {
        if ($create) {
            if ($key == NULL) {
                $this->err ("Please supply a key");
                return;
            }

            if ($approved == NULL) {
                $approved = 'virgin';
            }

            if (($approved != 'virgin') && ($approved != 'approved') && ($approved != 'rejected')) {
                $this->err ("Not a valid value for 'approved'. Choose 'virgin', 'approved', or 'rejected'.");
                return;
            }

            parent::__construct ($this->get_table_name(), array ('key'=>$key, 'approved'=>$approved, 'description'=>$description), $create);
            if ($this->error) {
                $this->err ("Cannot create key with key '".$key."', approved '".$approved."', description '".$description."'");
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

    function get_approved () {
        return $this->get_entry_value ('approved');
    }

    function set_approved ($approved) {
        if (($approved != 'virgin') && ($approved != 'approved') && ($approved != 'rejected')) {
            $this->err ("Not a valid value for 'approved'. Choose 'virgin', 'approved', or 'rejected'.");
            return;
        }

        return $this->set_entry_value ('approved', $approved);
    }

    function get_description () {
        return $this->get_entry_value ('description');
    }

    function set_description ($description) {
        return $this->set_entry_value ('description', $description);
    }
}
?>
