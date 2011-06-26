<?php

class sqlite {

    protected $_db;
    protected $_sql;
    protected $_where = array();
    protected $_params = array();

    public function __construct($database) {
        $this->_db = new PDO('sqlite:'.$database) or die('Aquí ha pasado algo');
    } 

    public function query($query) {

        $this->_sql = filter_var($query, FILTER_SANITIZE_STRING);

        $stmt = $this->_buildQuery();

        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $results = $stmt->fetchAll();
        return $results;
    }

    public function get($tableName, $numRows = NULL, $startingNum = NULL) {
        
        $query = "SELECT * FROM $tableName";
        $this->_sql = filter_var($query, FILTER_SANITIZE_STRING);

        $stmt = $this->_buildQuery($numRows, $startingNum);

        $stmt->execute($this->_params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $results = $stmt->fetchAll();

        return $results;
    }

    public function insert($tableName, $insertData) {

        $keys   = array_keys($insertData);
        $values = array_values($insertData);

        $binds_clausule  = implode(' , :', $keys);
        $keys_clausule   = implode(' , ', $keys);

        $clausule = "( $keys_clausule ) VALUES ( :$binds_clausule )";

        $this->_query = "INSERT into $tableName $clausule";
        //$stmt = $this->_prepareQuery();
        
        foreach($keys as $row) {
            $new_keys[] = ":$row";
        }

        $params = array_combine($new_keys, $values);

        $stmt = $this->_db->prepare($this->_query);
        if($stmt->execute($this->_params)) {
            return true;
        }

    }

    public function update($tableName, $updateData) {

        echo '<pre>';
        print_r($updateData);
        echo '</pre>';

        $keys   = array_keys($updateData);
        $values = array_values($updateData);

        foreach ($keys as $row) {
            $binds[] = ":$row";
        }

        echo '<pre>';
        print_r($data);
        echo '</pre>';
        
        /*foreach($data as $key => $value) {
            $clausule .= "$key*/
        $this->_sql = "UPDATE $tableName SET $clausule";

        $stmt = $this->_buildQuery();

        echo $this->_sql;

        //$stmt->execute($this->_params);

        /*$stmt = $this->_db->prepare($this->_query);
        if($stmt->execute($this->_params)) {
            return true;
        }*/

    }

    protected function _buildQuery ($numRows = NULL, $startingNum = NULL, $tableData = false) {
       
        $params = array();
        $hasTableData = null;

        if(!empty($this->_where)) {
            $counter = 0;
            foreach($this->_where as $row) {
                if ($counter == 0) {
                    $this->_sql .= " WHERE $row[key] $row[operator] :$row[key]";
                } else {
                    $this->_sql .= " AND $row[key] $row[operator] :$row[key]";
                }
                $counter++;
                if($row[operator] == 'LIKE') {
                    $this->_params[$row[key]] = '%'.$row[value].'%';
                } else {
                    $this->_params[$row[key]] = $row[value]; 
                }
            }
        }

        if(gettype($tableData) === 'array') {
            $hasTableData = true;
        }

        if ($numRows && !$startingNum) {
            $this->_sql .= " LIMIT 0, $numRows";
        } elseif ($numRows && $startingNum) {
            $this->_sql .= " LIMIT $startingNum, $numRows";
        }
        //echo $this->_sql;

        $stmt = $this->_prepareQuery();

        return $stmt;

    }

    protected function _prepareQuery() {
        
        if(!$stmt = $this->_db->prepare($this->_sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY))) {
            trigger_error('Problema al preparar la consulta: '. $this->_sql, E_USER_ERROR); 
        }
        return $stmt;
    }

    public function where($whereProp, $whereValue, $operator = NULL) {
        
        if ($operator == NULL) {
            $operator = '=';
        }

        $this->_where[] = array('key' => $whereProp, 'value' => $whereValue, 'operator' => $operator);
        return $this->_where;
    }

    public function __destruct () {
        $this->_db = NULL;
    }

}
