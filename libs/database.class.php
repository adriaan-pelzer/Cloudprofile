<?php
/* Adriaan Pelzer
 * 16 hours
 *
 * works with mysql.class.php
 */
require_once 'libs/mysql.class.php';

$mysql = new Mysql();

class dbTable {
    var $is = "dbtable";
    var $name;
    var $fields = array();
    var $index_array = array();

    var $error = NULL;

    function __construct ($name) {
        global $mysql;

        $this->name = $name;

        if (!($mysql->query("DESCRIBE `".$name."`"))) {
            $this->err ("Cannot describe table '".$name."'");
            return;
        } else {
            foreach ($mysql->result_to_array() as $field) {
                $this->fields[$field['Field']] = $field['Type'];
            }

            if (!($mysql->query ("SHOW INDEX FROM `".$this->name."`"))) {
                $this->err ("Cannot get index from table '".$this->name."'");
                return FALSE;
            }

            foreach ($mysql->result_to_array() as $index) {
                $this_index = array();
                if ($index['Non_unique'] == 0) {
                    $this_index['unique'] = TRUE;
                } else {
                    $this_index['unique'] = FALSE;
                }
                $this_index['column'] = $index['Column_name'];
                $this->index_array[$index['Key_name']][$index['Seq_in_index']-1] = $this_index;
            }
        }
    }

    function err ($message) {
        if ((!$this->error) || ($this->error == "")) {
            $this->error = $message;
        } else {
            $this->error = $message.": ".$this->error;
        }
    }

    function build_string ($values, $delim) {
        $string = "";
        foreach ($values as $key=>$val) {
            if ($val && ($val != "")) {
                if ($string != "") {
                    $string .= $delim;
                }
                $string .= "`".$key."` = '".$val."'";
            }
        }
        return $string;
    }

    function build_where ($where) {
        return $this->build_string ($where, " AND ");
    }

    function build_set ($set) {
        return $this->build_string ($set, ", ");
    }

    function insert ($values) {
        global $mysql;

        $key_string = "";
        $val_string = "";
        foreach ($values as $key=>$val) {
            if ($key_string != "") {
                $key_string .= ", ";
            }
            if ($val_string != "") {
                $val_string .= ", ";
            }
            $key_string .= "`".$key."`";
            $val_string .= "'".$val."'";
        }

        if (!($mysql->query ("INSERT INTO `".$this->name."` (".$key_string.") VALUES (".$val_string.")"))) {
            $this->err ("Cannot insert entry: ".$mysql->error);
            return FALSE;
        } else {
            $id = mysql_insert_id ($mysql->connection);
            if ($id == 0) {
                return TRUE;
            }
            return $id;
        }
    }

    function retrieve ($where) {
        global $mysql;

        //echo "SELECT * FROM `".$this->name."` WHERE ".(($where == '1')?"1":$this->build_where($where))."<br />\n";

        if (!($mysql->query ("SELECT * FROM `".$this->name."` WHERE ".(($where == '1')?"1":$this->build_where($where))))) {
            $this->err ("Cannot retrieve entries where ".$where_string." from table '".$this->name."': ".$mysql->error);
            return FALSE;
        }

        if (!($mysql->result_to_array ())) {
            $this->err ("Empty result returned");
            return FALSE;
        }

        //print_r ($mysql->result_arr);
        //echo "<br />\n";

        return $mysql->result_arr;
    }

    function update ($set, $where) {
        global $mysql;

        if (!($mysql->query ("UPDATE `".$this->name."` SET ".$this->build_set($set)." WHERE ".$this->build_where($where)))) {
            $this->err ("Cannot update entry in table '".$this->name."': ".$mysql->error);
            return FALSE;
        }

        return TRUE;
    }

    function exists ($where) {
        global $mysql;

        if (!($this->retrieve ($where))) {
            $this->error = NULL;
            $mysql->error = NULL;
            return FALSE;
        } else {
            if (mysql_num_rows ($mysql->result)) {
                return $mysql->result;
            } else {
                return FALSE;
            }
        }
    }

    function _delete ($where) {
        global $mysql;

        if (!($mysql->query ("DELETE FROM `".$this->name."` WHERE ".$this->build_where($where)))) {
            $this->err ("Cannot delete entry from table '".$this->name."': ".$mysql->error);
            return FALSE;
        }

        if (mysql_affected_rows ($mysql->connection) == 0) {
            $this->err ("Nothing was deleted");
            return FALSE;
        }

        return TRUE;
    }
}

class dbTableEntry extends dbTable {
    var $is = "dbtableentry";
    var $entry;
    var $calling_index = NULL;

    function __construct ($name, $args, $create=FALSE) {
        parent::__construct ($name);

        foreach ($this->index_array as $index_key=>$index) {
            //echo "Checking index: ".$index_key."=>".$index."<br />";
            $match = TRUE;
            if (sizeof ($index) == sizeof ($args)) {
                //echo "It's the same size as args: ".sizeof($args)." vs ".sizeof($index)."<br />";
                foreach ($index as $column) {
                    if ($column['unique'] == 0) {
                        $match = FALSE;
                        break;
                    }
                    //echo "Checking column ".$column['column']."<br />";
                    if (!(array_key_exists ($column['column'], $args))) {
                        //echo "This key does not exist in args: failing<br />";
                        $match = FALSE;
                        break;
                    }
                }
            } else {
                //echo "Not the same size as args: ".sizeof($args)." vs ".sizeof($index)."<br />";
                $match = FALSE;
            }
            if ($match) {
                //echo "This is the one<br />";
                $this->calling_index = $index_key;
                break;
            }
        }

        if ($this->calling_index) {
            if ($this_entry = $this->retrieve ($args)) {
                $this->entry = $this_entry[0];
                return;
            } else {
                if ($create) {
                    $this->error = NULL;

                    if (!($this->insert ($args))) {
                        $this->err ("Cannot insert new entry");
                        return;
                    }

                    if (!($this_entry = $this->retrieve ($args))) {
                        $this->err ("Cannot retrieve inserted entry");
                        return;
                    } else {
                        $this->entry = $this_entry[0];
                        return;
                    }
                } else {
                    $this->err ("Cannot find queried entry");
                    return;
                }
            }
        } else {
            if ($create) {
                if (!($this->insert ($args))) {
                    $this->err ("Cannot insert new entry");
                    return;
                }

                if (!($this_entry = $this->retrieve ($args))) {
                    $this->err ("Cannot retrieve inserted entry");
                    return;
                } else {
                    $this->entry = $this_entry[0];
                    return;
                }
            } else {
                $this->err ("Query parameters do not match any unique index");
                return;
            }
        }
    }

    /* only to be used in derived classes */
    function get_table_name() {
        return $this->is."s";
    }

    function get_entry_value ($key) {
        if (isset ($this->entry[$key])) {
            return $this->entry[$key];
        } else {
            if (isset ($this->fields[$key])) {
                return NULL;
            } else {
                $this->err ("Entry '".$key."' not found");
                return FALSE;
            }
        }
    }

    function set_entry_value ($key, $value) {
        if ((isset ($this->entry[$key])) || (isset ($this->fields[$key]))) {
            $deducted_where = $this->entry;

            unset ($dedcuted_where[$key]);

            if (!($this->update (array ($key=>$value), $deducted_where))) {
                $this->err ("Cannot update value '".$key."'='".$value."'");
                return FALSE;
            }

            $this->entry[$key] = $value;
            return $this->entry[$key];
        } else {
            if (isset ($this->fields[$key])) {
            } else {
                $this->err ("There's no field named '".$key."'");
                return FALSE;
            }
        }
    }

    function delete () {
        return parent::_delete ($this->entry);
    }
}
?>
