<?php

/**
 * Description of MySQLWrapper
 *
 * @author Suhmayah Banda
 * @package db
 */

namespace Bandu\Database;

class MySQLWrapper {
    
    protected $server;
    protected $user;
    protected $password;
    protected $db;
    
    protected $extras;
    
    protected $dbConn;
    
    protected $result;
    
    protected $queryLog;
    
    const INSERT = 1;
    const INSERT_IGNORE = 2;
    const REPLACE = 3;
    const SELECT = 4;
    const UPDATE = 5;
    const DELETE = 6;
    
    protected static $OPERATORS = array(
        '_eq' => 'handleEqualToOperator',
        '_ne' => 'handleNotEqualToOperator',
        '_gt' =>  'handleGreaterThanOperator',
        '_gte' => 'handleGreaterThanOrEqualToOperator',
        '_lt' => 'handleLessThanOperator',
        '_lte' => 'handleLessThanOrEqualToOperator',
        '_in' => 'handleInOperator',
        '_nin' => 'handleNotInOperator'
    );

    /**
     * Generic insert query for single and multiple inserts.
     * 
     * @var String 
     */
    protected static $TPL_INSERT_QUERY = "INSERT INTO __TABLE__ (__FIELDS__) VALUES __VALUES__";
    
    /**
     * Generic insert ignore query for single and multiple inserts.
     * 
     * @var String 
     */
    protected static $TPL_INSERT_IGNORE_QUERY = "INSERT IGNORE INTO __TABLE__ (__FIELDS__) VALUES __VALUES__";

    /**
     * Generic replace query for single and multiple inserts.
     * 
     * @var String 
     */
    protected static $TPL_REPLACE_QUERY = "REPLACE INTO __TABLE__ (__FIELDS__) VALUES __VALUES__";

    /**
     * Generic select query for single and inner joins.
     * 
     * @var String
     */
    protected static $TPL_SELECT_QUERY = "SELECT __FIELDS__ FROM __TABLES__ __WHERE__ __ORDER__ __GROUP__ __LIMIT__";
    
    /**
     * Generic update query
     * 
     * @var String 
     */
    protected static $TPL_UPDATE_QUERY = "UPDATE __TABLE__ SET __UPDATE_VALUES__ __WHERE__ __LIMIT__";
    
    /**
     * Generic delete query
     * 
     * @var String 
     */
    protected static $TPL_DELETE_QUERY = "DELETE FROM __TABLE__ __WHERE__ __LIMIT__";
        
    public function __construct($credentials) {
        if (!is_array($credentials)) {
            throw new \Exception("No Database Credentials provided.");
        }
        $this->init($credentials);
    }
    
    protected function init($credentials) {
        foreach (array('server', 'user', 'password', 'db')
                    as $required) {
            if (array_key_exists($required, $credentials)) {
                $this->$required = $credentials[$required];
                unset($credentials[$required]);
            } else {
                throw new \Exception("Missing Required Database Credentials:" . $required);
            }
            if (count($credentials)) {
                $this->extras = $credentials;
            }
        }
        $this->connect()->selectDb();
    }
    
    public function getExtras() {
        if (is_array($this->extras)) {
            return $this->extras;
        }
    }
    
    /**
     *
     * @throws \Exception 
     */
    protected function connect() {
        if (!$this->dbConn = mysql_connect($this->server, $this->user, $this->password)) {
            throw new \Exception(mysql_errno().": ".mysql_error());
        }
        return $this;
    }
    
    /**
     *
     * @throws \Exception 
     */
    protected function selectDb() {
        if ($this->dbConn) {
            if (!mysql_select_db($this->db)) {
                throw new \Exception($this->getMySQLError());
            }
        }
        return $this;
    }
    
    public function execute($sql) {
        echo $sql = trim($sql);
        $this->queryLog[] = $sql;

        if ($this->result = mysql_query($sql, $this->dbConn)) {
            if (strtoupper(substr($sql, 0, 6)) == "INSERT") {
                return mysql_insert_id($this->dbConn);
            }
            return mysql_affected_rows($this->dbConn);
        }
        $this->queryLog[] = $this->getMySQLError();
        throw new \Exception($sql." -- ".$this->getMySQLError());
    }
    
    /**
     * Execute an insert query.
     * 
     * @param string $tableName
     * @param array $values
     * @param int $insertType INSERT, INSERT_IGNORE or REPLACE
     * @return int returns the last insert ID if successfully executed 
     */
    public function insert($tableName, array $values, $insertType = MySQLWrapper::INSERT) {
        $queryComponents = array();
        $queryComponents['__TABLE__'] = $tableName;
        $queryComponents['__FIELDS__'] = implode(", ", array_keys($values));
        $escapedValues = array();
        foreach ($values as $value) {
            $escapedValues[] = '"'.mysql_real_escape_string($value, $this->dbConn).'"';
        }
        $queryComponents['__VALUES__'] = "(".implode(", ", $escapedValues).")";
        return $this->execute($this->buildQuery($insertType, $queryComponents));
    }
    
    /**
     * Execute an insert query.
     * 
     * @param string $tableName
     * @param array $values
     * @return int returns the last insert ID if successfully executed 
     */
    public function insertMultiple($tableName, $fields, array $values, $insertType = MySQLWrapper::INSERT) {
        $queryComponents = array();
        $queryComponents['__TABLE__'] = $tableName;
        $queryComponents['__FIELDS__'] = implode(", ", $fields);
        $queryComponents['__VALUES__'] = $this->buildValueBlocks($values);
        return $this->execute($this->buildQuery($insertType, $queryComponents));
    }
    
    /**
     * Performs a select query on a comma separated list of tables.
     * 
     * @param string $table A comma separated list of tables on which to perform the query
     * @param string $fields A comma separated list of fields to select
     * @param array $where An array mapping fields to values for the where clause
     * @param string $order The ordering instructions, excluding ORDER BY
     * @param string $group The grouping instructions, excluding GROUP BY
     * @param int $limit 
     * @param int $offset
     * @return boolean 
     */
    public function select($table, $fields = "*", array $where = null, $order = null, $group = null, $limit = null, $offset = null) {
        $queryComponents = array();
        $queryComponents['__FIELDS__'] = $fields;
        $queryComponents['__TABLES__'] = $table;
        $queryComponents['__WHERE__'] = $this->buildWhereClause($where);
        $queryComponents['__ORDER__'] = $this->getOrderByString($order);
        $queryComponents['__GROUP__'] = $this->getGroupByString($group);
        $queryComponents['__LIMIT__'] = $this->getLimitString($limit, $offset);
        return $this->execute($this->buildQuery(self::SELECT, $queryComponents));
    }
    
    /**
     * Perform an update query on a table
     * 
     * @param string $table The table name on which to perform the query
     * @param array $values An associative array mapping table fields to updated values
     * @param array $where An array mapping fields to values for the where clause
     * @param int $limit
     * @param int $offset 
     * @return int The number of rows affected
     */
    public function update($table, $values, $where = null, $limit = null, $offset = null) {
        $queryComponents = array();
        $queryComponents['__TABLE__'] = $table;
        $queryComponents['__UPDATE_VALUES__'] = $this->buildSetClauses($values);
        $queryComponents['__WHERE__'] = $this->buildWhereClause($where);
        $queryComponents['__LIMIT__'] = $this->getLimitString($limit, $offset);
        return $this->execute($this->buildQuery(self::UPDATE, $queryComponents));
    }
    
    /**
     * Perform a delete query on a table
     * 
     * @param string $table
     * @param array $where An array mapping fields to values for the where clause
     * @param int $limit
     * @param int $offset 
     */
    public function delete($table, array $where = null, $limit = null, $offset = null) {
        $queryComponents = array();
        $queryComponents['__TABLE__'] = $table;
        $queryComponents['__WHERE__'] = $this->buildWhereClause($where);
        $queryComponents['__LIMIT__'] = $this->getLimitString($limit, $offset);
        return $this->execute($this->buildQuery(self::DELETE, $queryComponents));
    }
    
    /**
     * Returns the number of rows in the result set for the last db query
     * 
     * @return int 
     */
    public function getNumRows() {
        if ($this->result) {
            return mysql_num_rows($this->result);
        }
    }
    
    /**
     * Fetches the first row in the result set of the last mysql query and returns an associative array
     * 
     * @return mixed array or boolean 
     */
    public function fetchRow() {
        if ($this->getNumRows()) {
            return mysql_fetch_assoc($this->result);
        }
        return false;
    }
    
    /**
     * Returns the result set of the last query in a multidemnsional associative array
     * 
     * @return mixed array or boolean if no row to return
     */
    public function fetchAll($index = null) {
        if ($this->getNumRows()) {
            $rows = array();
            while ($row = mysql_fetch_assoc($this->result)) {
                if (!is_null($index) && array_key_exists($index, $row)) {
                    $rows[$row[$index]] = $row;
                } else {
                    $rows[] = $row;
                }
            }
            return $rows;
        }
        return false;
    }
    
    /**
     * Performs a select query and returns the value of the field specified if found.
     * 
     * @see MySQLWrapper::select
     * @param string  $table
     * @param string $field
     * @param array $where
     * @param array $order
     * @param string $group
     * @param int $limit
     * @param int $offset
     * @return mixed or false if no result found
     */
    public function fetchField($table, $field, array $where, $order = null, $group = null, $limit = null, $offset = null) {
        if ($this->select($table, $field, $where, $order, $group, $limit, $offset)) {
            $row = $this->fetchRow();
            return $row[$field];
        }
        return false;
    }
    
    protected function buildWhereClause(array $whereArray = null) {
        $whereClauses = array();
        foreach ($whereArray as $field => $value) {
            if (!is_array($value)) {
                $whereClauses[] = $field . " = '".mysql_real_escape_string($value, $this->dbConn)."'";
            } else {
                $whereClauses[] = $this->getFilter($field, $value);
            }
        }
        return " WHERE " . implode(" AND ", $whereClauses);
    }
    
    protected function getFilter($field, $filter) {
        foreach ($filter as $operator => $value) {
            if (!array_key_exists($operator, self::$OPERATORS)) {
                throw new \Exception('Unknown Operator: '.$operator);
            }
            $filters = array();
            $filterHandler = self::$OPERATORS[$operator];
            $filters[] = $this->$filterHandler($field, $value);
        }
        $combinedFilter = implode(" AND ", $filters);
        return $combinedFilter;
    }
    
    protected function handleEqualToOperator($field, $value) {
        $placeHolders = array(
            $field,
            mysql_real_escape_string($value, $this->dbConn),
        );
        return vsprintf("%s = '%s'", $placeHolders);
    }

    protected function handleNotEqualToOperator($field, $value) {
        $placeHolders = array(
            $field,
            mysql_real_escape_string($value, $this->dbConn),
        );
        return vsprintf("%s != '%s'", $placeHolders);
    }

    protected function handleInOperator($field, array $values) {
        $escapedValues = array();
        foreach ($values as $value) {
            $escapedValues[] = '"'.mysql_real_escape_string($value, $this->dbConn).'"';
        }
        $placeholders = array(
            $field,
            implode(', ', $escapedValues),
        );
        return vsprintf("%s IN (%s)", $placeholders);
    }
    
    protected function handleNotInOperator($field, array $values) {
        $escapedValues = array();
        foreach ($values as $value) {
            $escapedValues[] = '"'.mysql_real_escape_string($value, $this->dbConn).'"';
        }
        $placeholders = array(
            $field,
            implode(', ', $escapedValues),
        );
        return vsprintf("%s NOT IN (%s)", $placeholders);
    }
    
    protected function handleGreaterThanOperator($field, $value) {
        $placeHolders = array(
            $field,
            mysql_real_escape_string($value, $this->dbConn),
        );
        return vsprintf("%s > '%s'", $placeHolders);
    }
    
    protected function handleGreaterThanOrEqualToOperator($field, $value) {
        $placeHolders = array(
            $field,
            mysql_real_escape_string($value, $this->dbConn),
        );
        return vsprintf("%s >= '%s'", $placeHolders);
    }
    
    protected function handleLessThanOperator($field, $value) {
        $placeHolders = array(
            $field,
            mysql_real_escape_string($value, $this->dbConn),
        );
        return vsprintf("%s < '%s'", $placeHolders);
    }
    
    protected function handleLessThanOrEqualToOperator($field, $value) {
        $placeHolders = array(
            $field,
            mysql_real_escape_string($value, $this->dbConn),
        );
        return vsprintf("%s <= '%s'", $placeHolders);
    }
    
    protected function buildSetClauses(array $updateValues) {
        $setClauses = array();
        foreach ($updateValues as $field => $value) {
            $setClauses[] = $field . " = '".mysql_real_escape_string($value, $this->dbConn)."'";
        }
        return implode(", ", $setClauses);
    }
    
    protected function getOrderByString($order = null) {
        if (isset($order)) {
            return "ORDER BY ".$order;
        }
        return "";
    }
    
    protected function getGroupByString($group = null) {
        if (isset($group)) {
            return "GROUP BY ".$group;
        }
        return "";
    }
    
    protected function getLimitString($limit = null, $offset = null) {
        if (isset($limit)) {
            $limitString = "LIMIT ";
            if (isset($offset)) {
                $limitString .= $offset.", ";
            }
            $limitString .= $limit;
            return $limitString;
        }
        return "";
    }
    
    protected function buildQuery($type, array $queryComponents) {
        $queryTpl = $this->getQueryTemplate($type);
        foreach ($queryComponents as $key => $value) {
            $queryTpl = str_replace($key, $value, $queryTpl);
        }
        return $queryTpl;
    }
    
    protected function getQueryTemplate($type) {
        switch ($type) {
            case self::INSERT:
                return $queryTpl = self::$TPL_INSERT_QUERY;
                break;
            case self::INSERT_IGNORE:
                return $queryTpl = self::$TPL_INSERT_IGNORE_QUERY;
                break;
            case self::REPLACE:
                return $queryTpl = self::$TPL_REPLACE_QUERY;
                break;
            case self::SELECT:
                return self::$TPL_SELECT_QUERY;
                break;
            case self::UPDATE:
                return self::$TPL_UPDATE_QUERY;
                break;
            case self::DELETE:
                return self::$TPL_DELETE_QUERY;
                break;
            default :
                throw new \Exception("Unknown Query Type: ".$type);
                break;
        }
    }
    
    public function buildValueBlocks(array $values) {
        $valueBlocks = array();
        foreach ($values as $value) {
            $escapedValues = array();
            foreach ($value as $v) {
                $escapedValues[] = '"'.mysql_real_escape_string($v, $this->dbConn).'"';
            }
            $valueBlocks[] = "(".implode(", ", $escapedValues).")";
        }
        return implode(", ", $valueBlocks);
    }
    
    public function getInsertId() {
        return mysql_insert_id($this->dbConn);
    }
    
    public function getQueryLog() {
        return $this->queryLog;
    }
    
    private function getMySQLError() {
        return mysql_errno($this->dbConn).": ".mysql_error($this->dbConn);
    }
    
    public function reset() {
        return mysql_data_seek($this->result, 0);
    }
    
    protected function close() {
        if (is_resource($this->dbConn)) {
            mysql_close($this->dbConn);
        }
    }
    
    public function __destruct() {
        $this->close($this->dbConn);
    }
    
}

