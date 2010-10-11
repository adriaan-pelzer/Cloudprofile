<?php
require_once 'libs/mysql.conf.php';

class Mysql {
    var $is = "mysql";
    var $connection;
    var $query;
    var $result;
    var $result_arr;
    var $error = NULL;

    /* Constructor */
    function __construct ($server=DB_HOST, $user=DB_USER, $pass=DB_PASS, $database=DB_BASE) {
        if (!($this->connection = mysql_connect ($server, $user, $pass))) {
            $this->error = "Cannot connect to mysql database: ".mysql_error($this->connection);
            return;
        }

        if (!mysql_select_db ($database, $this->connection)) {
            $this->error = "Cannot select mysql database: ".mysql_error($this->connection);
            return;
        }
    }

    /* Generic Mysql query - populates result resource */
    function query ($query = NULL) {
        //echo $query."<br />\n";
        if ($query != NULL) {
            $this->query = $query;
        }

        if (!($this->result = mysql_query ($this->query, $this->connection))) {
            $this->error = "Cannot execute mysql query: ".mysql_error($this->connection);
            return FALSE;
        } else {
            if ($this->error = mysql_error($this->connection)) {
                $this->error = "Cannot execute mysql query: ".$this->error;
                return FALSE;
            }
            return $this->result;
        }
    }

    function result_to_array () {
        $this->result_arr = array();
        $i = 0;

        while ($row = mysql_fetch_array ($this->result)) {
            $this_entry = array();

            foreach ($row as $key=>$value) {
                if ($key != $i) {
                    $this_entry[$key] = $value;
                } else {
                    $i++;
                }
            }

            array_push ($this->result_arr, $this_entry);
        }

        return $this->result_arr;
    }
}
?>
