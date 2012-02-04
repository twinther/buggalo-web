<?
// $Header: /var/lib/cvs/morsmad/libs/lib_mysql.php,v 1.2 2011-08-08 17:44:40 tommy Exp $

define('DB_NOCONNECTION', -1000);

/**
 * This class handles the database connection as well as the execution of the queries.
 * The class mostly wraps php mysql_* functions, but extends them in certain cases.
 * For example it simulates the ocibindbyname functionality.
 *
 * @author	tommy
 * @version	$Revision: 1.2 $
 */
class mysql_connection {
	var $connection;
	var $database;
	var $server;
	var $username;
	var $password;
	var $last_query;
	var $session_errors; // Any errors this session?

	// error handling
	var $error_handler;
	var $last_error;

	// for statistical purposes only
	var $number_of_queries;
	var $total_time_used;

	function mysql_connection($database, $server, $username, $password) {
		$this->database = $database;
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
		
		$this->session_errors = false;
		$this->number_of_queries = 0;
		$this->total_time_used = 0;
		
		$this->get_instance();
	}

	function &get_instance() {
		static $instance;
		if(!isset($instance))
			$instance = $this;
		return $instance;
	}

	function open() {
		$this->connection =  @mysql_pconnect($this->server, $this->username, $this->password);
		if($this->connection) {
			mysql_select_db($this->database, $this->connection);
			return true;
		} else {
			if(is_callable($this->error_handler))
				call_user_func($this->error_handler, $this, DB_NOCONNECTION);
			return false;
		}
	}

	function close() {
		mysql_close($this->connection);
	}

	function fetch($query, $bind = NULL, $res_type = MYSQL_ASSOC) {
		$this->number_of_queries++;

		$query = $this->_bind($query, $bind);
		$this->last_query = $query;

		$start_time = $this->_microtime();
		$res = mysql_query($query, $this->connection);
		$this->total_time_used += $this->_microtime() - $start_time;
		
		if($res) {
			$rows = array();
			while( $row =  mysql_fetch_array($res, $res_type) )
				$rows[] = $row;
			mysql_free_result($res);
		} else {
			$rows = false;
		}
		
		$this->session_errors |= (bool) $this->is_error();
		return $rows;
	}

	function fetch_row($query, $bind = NULL, $res_type = MYSQL_ASSOC) {
		$this->number_of_queries++;

		$query = $this->_bind($query, $bind);
		$this->last_query = $query;

		$start_time = $this->_microtime();
		$res = mysql_query($query, $this->connection);
		$this->total_time_used += $this->_microtime() - $start_time;
		
		if($res) {
			$row = mysql_fetch_array($res, $res_type);
			mysql_free_result($res);
		} else {
			$row = false;
		}

		$this->session_errors |= (bool) $this->is_error();
		return $row;
	}

	function execute($query, $bind = NULL) {
		$this->number_of_queries++;

		$query = $this->_bind($query, $bind);
		$this->last_query = $query;

		$start_time = $this->_microtime();
		$res = mysql_query($query, $this->connection);
		$this->total_time_used += $this->_microtime() - $start_time;
		
		$affected = mysql_affected_rows();

		$this->session_errors |= (bool) $this->is_error();
		return ($affected > 0);
	}

	function _prepare_string($string) {
		return is_null($string) ? 'NULL' : "'".mysql_real_escape_string(stripslashes($string), $this->connection)."'";
	}

	function _prepare_numberic($number) {
		return is_null($number) ? 'NULL' : mysql_real_escape_string($number, $this->connection);
	}

	function _bind($sql, $bind) {
		if(isset($bind)) {
			foreach($bind as $key => $value) {
				$prepared_value = is_numeric($value) ? $this->_prepare_numberic($value) : $this->_prepare_string($value);
				$sql = str_replace(':'.$key, $prepared_value, $sql);
			}
		}

		return $sql;
	}

	function _microtime() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $usec + (float) $sec;
	}

	function get_auto_increment_value() {
		return mysql_insert_id($this->connection);
	}

	function is_error() {
		if(is_callable($this->error_handler) && mysql_error($this->connection)) {
			$this->last_error = mysql_error($this->connection);
			call_user_func($this->error_handler, $this, mysql_errno($this->connection), mysql_error($this->connection));
			return true;
		} else {
			return false;
		}
	}

	function is_session_errors() {
		return $this->session_errors;
	}

	function get_last_query() {
		return $this->last_query;
	}

	function get_last_error() {
		return $this->last_error;
	}

	function get_number_of_queries() {
		return $this->number_of_queries;
	}

	function get_total_time_used() {
		return $this->total_time_used;
	}

	function set_error_handler($error_handler) {
		$this->error_handler = $error_handler;
	}
}
?>
