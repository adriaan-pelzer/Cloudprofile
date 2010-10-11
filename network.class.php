<?php
require_once 'libs/database.class.php';

/**
 * Network class:
 * supply id or name to retrieve
 */
class Network extends dbTableEntry {
    var $is = "network";

    function __construct ($id, $name=NULL) {
        if ($id == NULL) {
            if ($name == NULL) {
                $this->err ("Please supply a name");
                return;
            }
            parent::__construct ($this->get_table_name(), array ('name'=>$name));
            if ($this->error) {
                $this->err ("Cannot find network entry with name '".$name."'");
                return;
            }
        } else {
            parent::__construct ($this->get_table_name(), array ('id'=>$id));
            if ($this->error) {
                $this->err ("Cannot find network entry with id '".$id."'");
                return;
            }
        }
    }

    function get_id () {
        return $this->get_entry_value ('id');
    }

    function get_name () {
        return $this->get_entry_value ('name');
    }
}
?>
