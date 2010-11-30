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
        $this->connection = new mysqli($server, $user, $pass, $database);

        if (mysqli_connect_errno()) {
            $this->error = "Cannot connect to mysql database: ".mysqli_connect_error();
            exit();
        }
    }

    /* Generic Mysql query - populates result resource */
    function query ($query = NULL) {
        //echo $query."<br />\n";
        if ($query != NULL) {
            $this->query = $query;
        } else {
            $this->error = "Query is empty";
            return FALSE;
        }

        if (!($this->result = $this->connection->query ($this->query))) {
            $this->error = "Cannot execute mysql query: ".$this->connection->error;
            $this->result->close();
            return FALSE;
        } else {
            return $this->result;
        }
    }

    function result_to_array () {
        $this->result_arr = array();
        $i = 0;

        while ($row = $this->result->fetch_array (MYSQLI_ASSOC)) {
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
