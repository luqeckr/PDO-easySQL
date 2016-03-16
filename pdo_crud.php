<?php
/* @PDO-CRUD-PHP-OOP - Luqman Hakim <luqeckr@gmail.com>
 * @Modified from MySQLi-CRUD-PHP-OOP
 * @Original Author Rory Standley <rorystandley@gmail.com>
 * @Version 1.0
 * @Package Database
 */
class Database {
	/* 
	 * Create variables for credentials to MySQL database
	 * The variables have been declared as private. This
	 * means that they will only be available with the 
	 * Database class
	 */
    
	private $db_host = "127.0.0.1";  // Change as required
	private $db_user = "username"; // Change as required
	private $db_pass = "password";  // Change as required
	private $db_name = "database"; // Change as required
	
	/*
	 * Extra variables that are required by other function such as boolean con variable
	 */
	private $con = false; // Check to see if the connection is active
    protected static $myconn = ""; // This will be our mysqli object
	public $result = array(); // Any results from a query will be stored here
    public $myQuery = "";// used for debugging process with SQL return
    private $numResults = "";// used for returning the number of rows
    private $sel_table = ""; // select query 
    public $qBuild = "";

    function __construct(){

        $this->connect();   

    }

	
	// Function to make connection to database
	public function connect(){
        try {
            if(!$this->con) {
                $dsn = "mysql:host={$this->db_host}";
                $this->myconn = new PDO("mysql:host={$this->db_host}; dbname={$this->db_name}", $this->db_user, $this->db_pass);
                
                $this->con = true;
                return true;

            } else {
                return true;
            }
            
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            return false;
        }

	}

	
	// Function to disconnect from the database
    public function disconnect(){
    	// If there is a connection to the database
    	if($this->con){
    		// We have found a connection, try to close it
            $this->myconn = null;
            if ($this->myconn) {
                return false; // connection still not closed
            } else {
                $this->con=false;
                return true; // connection is closed
            }

		}
    }
	
	public function sql($sql){
		$query = $this->myconn->query($sql);
        $this->myQuery = $sql; // Pass back the SQL
		if($query){
			// If the query returns >= 1 assign the number of rows to numResults
			$this->numResults = $this->last_row_count();
			// Loop through the query results by the number of rows returned
			for($i = 0; $i < $this->numResults; $i++){
				$r = $query->fetch(PDO::FETCH_ASSOC);
               	$key = array_keys($r);
               	for($x = 0; $x < count($key); $x++){
               		// Sanitizes keys so only alphavalues are allowed
                   	if(!is_int($key[$x])){
                   		if($this->last_row_count() >= 1){
                   			$this->result[$i][$key[$x]] = $r[$key[$x]];
						}else{
							$this->result = null;
						}
					}
				}
			}
			return true; // Query was successful
		}else{
			array_push($this->result,$this->myconn->error);
			return false; // No rows where returned
		}
	}
	
	// Function to SELECT from the database
	public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null){
		// Create query from the variables passed to the function
		$q = 'SELECT '.$rows.' FROM '.$table;
		if($join != null){
			$q .= ' '.$join; //$q .= ' JOIN '.$join; # for ability to use LEFT/RIGHT/INNER/OUTER JOIN
		}
        if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        if($limit != null){
            $q .= ' LIMIT '.$limit;
        }

        // echo $table;
        $this->myQuery = $q; // Pass back the SQL
        //echo $q;
		// Check to see if the table exists
        if($this->tableExists($table)){
        	// The table exists, run the query
        	$query = $this->myconn->query($q);    
			if($query){
				// If the query returns >= 1 assign the number of rows to numResults
				$this->numResults = $this->last_row_count();
				// Loop through the query results by the number of rows returned
				for($i = 0; $i < $this->numResults; $i++){
					$r = $query->fetch(PDO::FETCH_ASSOC);
                	$key = array_keys($r);
                	for($x = 0; $x < count($key); $x++){
                		// Sanitizes keys so only alphavalues are allowed
                    	if(!is_int($key[$x])){
                    		if($this->last_row_count() >= 1){
                    			$this->result[$i][$key[$x]] = $r[$key[$x]];
							}else{
								$this->result[$i][$key[$x]] = null;
							}
						}
					}
				}
				return true; // Query was successful
			}else{
                array_push($this->result,$this->myconn->errorInfo());
				return false; // No rows where returned
			}
      	}else{
      		return false; // Table does not exist
    	}
    }
	
	// Function to insert into the database
    public function insert($table,$params=array()){
    	// Check to see if the table exists
    	 if($this->tableExists($table)){
    	 	$sql='INSERT INTO `'.$table.'` (`'.implode('`, `',array_keys($params)).'`) VALUES ("' . implode('", "', $params) . '")';
            $this->myQuery = $sql; // Pass back the SQL
            // Make the query to insert to the database
            if($ins = $this->myconn->query($sql)){
            	array_push($this->result,$this->myconn->lastInsertId());
                return true; // The data has been inserted
            }else{
            	array_push($this->result,$this->myconn->errorInfo());
                return false; // The data has not been inserted
            }
        }else{
        	return false; // Table does not exist
        }
    }

    // Function to insert into the database, using prepared statement
    public function insert_prep($table, $params=array()) {
        // Check to see if the table exists
        if($this->tableExists($table)) {
            $fieldnames = array_keys($params);
            $sql = "INSERT INTO $table";
            /*** set the field names ***/
            $fields = '( ' . implode(', ', $fieldnames) . ' )';
            /*** set the placeholders ***/
            $bound = '(:' . implode(', :', $fieldnames) . ' )';
            /*** put the query together ***/
            $sql .= $fields.' VALUES '.$bound;

            $size = sizeof($fieldnames);
            echo $sql;
            /*** prepare and execute ***/
            $stmt = $this->myconn->prepare($sql);

            if ($stmt->execute($params)) {
                array_push($this->result,$this->myconn->lastInsertId());
                return true; // The data has been inserted
            } else{
                array_push($this->result,$this->myconn->errorInfo());
                return false; // The data has not been inserted
            }
        } else {
            return false; // Table does not exist
        }
        
    }
	
	//Function to delete table or row(s) from database
    public function delete($table,$where = null){
    	// Check to see if table exists
    	 if($this->tableExists($table)){
    	 	// The table exists check to see if we are deleting rows or table
    	 	if($where == null){
                $delete = 'DROP TABLE '.$table; // Create query to delete table
            }else{
                $delete = 'DELETE FROM '.$table.' WHERE '.$where; // Create query to delete rows
            }
            // Submit query to database
            $del = $this->myconn->prepare($delete);
            if($del->execute()){
            	array_push($this->result,$del->rowCount());
                $this->myQuery = $delete; // Pass back the SQL
                return true; // The query exectued correctly 
            }else{
            	array_push($this->result,$this->myconn->errorInfo());
               	return false; // The query did not execute correctly
            }
        }else{
            return false; // The table does not exist
        }
    }
	
	// Function to update row in database
    public function update($table,$params=array(),$where){
    	// Check to see if table exists
    	if($this->tableExists($table)){
            $fieldnames = array_keys($params);
    		// Create Array to hold all the columns to update
            $args=array();
            foreach ($fieldnames as $field) {
                $args[]=$field.'=:'.$field;
            }
			
			// Create the query
			$sql='UPDATE '.$table.' SET '.implode(',',$args).' WHERE '.$where;
			// Make query to database
            $this->myQuery = $sql; // Pass back the SQL
            $stmt = $this->myconn->prepare($sql);
            if($stmt->execute($params)){
            	array_push($this->result,$stmt->rowCount());
            	return true; // Update has been successful
            }else{
            	array_push($this->result,$this->myconn->errorInfo());
                return false; // Update has not been successful
            }
        }else{
            return false; // The table does not exist
        }
    }
	
	// Private function to check if table exists for use with queries
	private function tableExists($table){
        $table = explode(' ', $table);
        $table = $table[0];
		$tablesInDb = $this->myconn->query('SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"');
        if($tablesInDb){
        	if($this->last_row_count() == 1){
                return true; // The table exists
            }else{
            	array_push($this->result,$table." does not exist in this database");
                return false; // The table does not exist
            }
        }
    }

    public function last_row_count() {
        return $this->myconn->query("SELECT FOUND_ROWS()")->fetchColumn();
    }
	
	// Public function to return the data to the user
    public function getResult(){
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    //Pass the SQL back for debugging
    public function getSql(){
        $val = $this->myQuery;
        $this->myQuery = array();
        return $val;
    }

    //Pass the number of rows back
    public function numRows(){
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }

    // Escape your string
    public function escapeString($data){
        return $this->myconn->real_escape_string($data);
    }

}


class Builder extends Database {

 
    protected static $myconn;

    public function __construct(Database $db) {
        $this->myconn = $db->myconn;
        $this->init();
    }


    public function init() {
        $this->qBuild = ''; // reset value
        $this->qBuild = new stdClass;
        $this->qBuild->where = array();
        $this->qBuild->join = array();
        $this->qBuild->insertdata = array();
    }

    public function selects($data) {
        if (isset($this->qBuild->select)) {
            $this->qBuild->select.= ', '.$data;
        } else {
            $this->qBuild->select = $data;
        }
    }

    public function setdata($column, $value) {
        $this->qBuild->insertdata[$column] = $value;
        $this->result = $this->qBuild->insertdata;
    }

    public function insert_to($table) {

        $fieldnames = array_keys($this->qBuild->insertdata);
        $sql = "INSERT INTO $table";
        /*** set the field names ***/
        $fields = '( ' . implode(', ', $fieldnames) . ' )';
        /*** set the placeholders ***/
        $bound = '(:' . implode(', :', $fieldnames) . ' )';
        /*** put the query together ***/
        $sql .= $fields.' VALUES '.$bound;

        $size = sizeof($fieldnames);
        $this->myQuery = $sql;
        /*** prepare and execute ***/
        $stmt = $this->myconn->prepare($sql);
        foreach ($this->qBuild->insertdata as $key => $value) {
            $param1 = ':'.$key;
            $stmt->bindValue($param1, $value);
        }
        
        if ($stmt->execute()) {
        //if ($stmt->execute($params)) {
            array_push($this->result,$this->myconn->lastInsertId());
            return true; // The data has been inserted
        } else{
            array_push($this->result,$this->myconn->errorInfo());
            return false; // The data has not been inserted
        }
       
    }

    public function update_to($table) {
        $fieldnames = array_keys($this->qBuild->insertdata);
        $sql = "UPDATE $table SET";
        /*** set the field names ***/
        $fields = implode(', ', $fieldnames);
        /*** set the placeholders ***/
        $bound = ':' . implode(', :', $fieldnames);
        /*** put the query together ***/

        $i=0;
        foreach ($fieldnames as $key) {
            if ($i > 0) { $prepend = ', ' } else { $prepend = ''; }
            $setdata = $prepend . $key.' = :'.$key;
            $i++;
        }

        $sql .= $setdata;
        if (!empty($this->qBuild->where)) {
            $where = implode(' ', $this->qBuild->where);
            $sql.= ' WHERE '.$where;
        }

        $this->myQuery = $sql;
        /*** prepare and execute ***/
        $stmt = $this->myconn->prepare($sql);
        foreach ($this->qBuild->insertdata as $key => $value) {
            $param1 = ':'.$key;
            $stmt->bindValue($param1, $value);
        }
        
        if ($stmt->execute()) {
        //if ($stmt->execute($params)) {
            array_push($this->result,$this->myconn->lastInsertId());
            return true; // The data has been inserted
        } else{
            array_push($this->result,$this->myconn->errorInfo());
            return false; // The data has not been inserted
        }
    }

    // table, join condition, join type
    public function join($data, $condition, $jointype=null) {
        $data = $jointype ? $jointype.' JOIN '.$data : ' JOIN '.$data;
        $data.= $condition ? ' ON '.$condition : '';
        $data = $data . ' ';
        array_push($this->qBuild->join, $data);
    }

    public function where($data, $op=null) {
        $data = $op ? ' '.$op.' '.$data : $data;
        array_push($this->qBuild->where, $data);
    }


    public function get($table) {
        
        if (isset($this->qBuild->select)) { // SELECT Mode
            $columns = $this->qBuild->select;
            $sql = 'SELECT '.$columns.' FROM '. $table;
        }
        
        if (!empty($this->qBuild->join)) {
            $joins = implode(' ', $this->qBuild->join);
            $sql.= ' '.$joins;
        }
        if (!empty($this->qBuild->where)) {
            $where = implode(' ', $this->qBuild->where);
            $sql.= ' WHERE '.$where;
        }
        
        $this->myQuery = $sql; // Pass back the SQL

        $stmt = $this->myconn->prepare($sql);

        if ($g = $stmt->execute()) {
            //$stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->numResults = $this->last_row_count();

            // Loop through the query results by the number of rows returned
            for($i = 0; $i < $this->numResults; $i++){
                $r = $stmt->fetch(PDO::FETCH_ASSOC);

                $key = array_keys($r);
                for($x = 0; $x < count($key); $x++){
                    // Sanitizes keys so only alphavalues are allowed
                    if(!is_int($key[$x])){
                        if($this->last_row_count() >= 1){
                            $this->result[$i][$key[$x]] = $r[$key[$x]];
                        }else{
                            $this->result[$i][$key[$x]] = null;
                        }
                    }
                }
            }
            return true;   
        } else {
            array_push($this->result,$this->myconn->errorInfo());
            return false; // No rows where returned
        } 
        
    }
}