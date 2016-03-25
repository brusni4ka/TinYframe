<?php
namespace HappyCake\Db;

use HappyCake\Db\Db;
use HappyCake\Db\Tables\Users;

/**
 * Created by PhpStorm.
 * User: kate
 * Date: 14.03.16
 * Time: 1:21
 */
class AbstractTable extends DB
{

    // static protected $tableName;
    protected $tableClass;
    protected $table;
    protected static $primaryKey = 'id';

    protected static $tablesMap = array();
    private $params = array();                // attribute name => attribute value


    public function __construct()
    {
        parent::__construct();
        $this->tableClass = static::class;
        $this->table = $this->tableName();
    }

    /**
     * PHP getter magic method.
     * This method is overridden so that AR params can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     * @return $value
     */

    public function __get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } else return null;
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that AR params can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */


    public function __set($name, $value)
    {
        if (isset($this->params[$name])) {
            return;
        } else {
            $this->params[$name] = $value;
        }

    }

    /**
     * PHP isset magic method.
     * @return bool
     */

    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    /**
     * PHP unset magic method.
     */
    public function __unset($name)
    {
        unset($name, $this->params);
    }


    /**
     * @return an instance of tableClass
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$tablesMap[$className])) {
            return self::$tablesMap[$className];
        } else {
            $model = self::$tablesMap[$className] = new $className(null);
            return $model;
        }
    }

    /**
     * Sets primary key
     */


    public function setPrimaryKey($primaryKey)
    {
        static::$primaryKey = $primaryKey;
    }

    /**
     * @return a table Name
     */

    public function tableName()
    {
        $tableName = get_class($this);
        if (($pos = strrpos($tableName, '\\')) !== false)
            $tableName = substr($tableName, $pos + 1);
        return strtolower($tableName);
    }


    public function getPrimaryKey()
    {
        return static::$primaryKey;
    }



    /*
        public function refresh()
        {
            if ($record = $this->findAll()) {
                $this->params;
                for ($i = 0; $i < count($record); $i++) {
                    foreach ($record[$i] as $column => $value) {
                        if (property_exists($this, $column))
                            $this->$column = $value;
                        else {
                            $this->params[$column][] = $value;
                        }

                    }

                }
                return true;
            } else
                return false;

        }

    */

    /**
     * Example: findWhere->(array('id','user_name'),(id=2,sum<200),'and')
     * @return array of objects
     */
    public function findWhere(array $field, array $condition, $delimiter = 'AND')
    {

        $fields = rtrim(implode(',', $field), ',');
        $table = $this->table;
        $fieldHolder = self::getFieldsHolder(array_keys($condition), $delimiter);

        $query = "SELECT $fields FROM  $table  WHERE $fieldHolder";
        $sth = self::bindParams($query, $condition);
        $sth->execute();

        $classArray = [];

        while ($res = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $class = new static;
            foreach ($res as $key => $value) {
                $class->$key = $value;
            }
            array_push($classArray, $class);
        }
//        var_dump($classArray);

        return $classArray;
    }


    /**
     * INSERT new row in database or UPDATE if row exist
     * @return mixed
     */

    public function save()
    {

        $pk = static::$primaryKey;
        $table = $this->table;
        $fields = array_keys($this->params);
        $fieldsHolder = '';
        $valueHolder = '';
        //if row exist
        if (array_key_exists($pk, $this->params) && $this->pkInDb($this->params[$pk])) {

            $fieldsHolder = self::getFieldsHolder($fields, ',');
            $query = 'UPDATE ' . $table . ' SET ' . $fieldsHolder . ' WHERE ' . $pk . '=' . $this->params[$pk];
            $sth = self::bindParams($query, $this->params);

        } else {

            foreach ($fields as $field) {
                $fieldsHolder .= "$field,";
                $valueHolder .= ":$field,";
            }
            $fieldsHolder = rtrim($fieldsHolder, ', ');
            $valueHolder = rtrim($valueHolder, ', ');

            $query = 'INSERT INTO ' . $table . ' (' . $fieldsHolder . ') VALUES (' . $valueHolder . ')';
            $sth = self::bindParams($query, $this->params);
        }
        return $this->execQuery($sth);
    }


    private static function bindParams($query, array $params)
    {
        $sth = self::$dbh->prepare($query);
        foreach ($params as $key => $value) {
            $sth->bindValue(":$key", "$value");
        }
        return $sth;
    }


    private static function getFieldsHolder(array $fields, $delimiter = ',', array $operator = ['='])
    {
        $fieldsHolder = '';

        foreach ($fields as $field) {
            $fieldsHolder .= "$field" . array_pop($operator) . ":$field $delimiter ";

        }
        return rtrim($fieldsHolder, " $delimiter ");
    }


    /**
     * DELETE row in database if id exist
     * @return mixed
     */
    public function delete()
    {
        self::$dbh = DbConnect::getConnection();
        $pk = static::$primaryKey;
        $table = static::$tableName;

        $query = "DELETE FROM $table WHERE $pk = " . $this->params[$pk];
        $sth = self::$dbh->prepare($query);

        return $this->execQuery($sth);
    }


    /**
     * Executes a prepared statement
     * @param $sth
     * @throws DbException
     */
    private static function execQuery($sth)
    {
        try {
            $result = $sth->execute();

            if (!$result) {
                throw new DbException("Can't perform database operations" . $sth->errorInfo());
            }
            return $result;
        } catch (DbException $e) {
            die(" Error " . $e->getMessage() . " Line " . $e->getLine() . " File " . $e->getFile());
        }
    }


    /***
     * Checks if it's already pk value in db exist
     * @return bool
     */

    public function pkInDb($pk)
    {
        $pk = static::$primaryKey;
        $table = $this->table;
        $query = "SELECT * FROM $table WHERE $pk = " . $pk;
        $sth = self::$dbh->prepare($query);
        $sth->execute();
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }
        return true;
    }


    /**
     * Works only after save function
     * @return the last id in db
     */
    public function getLastId()
    {

        echo self::$dbh->lastInsertId();
    }

    public function getQueryBuilder()
    {
        return new QueryBuilder();
    }


}