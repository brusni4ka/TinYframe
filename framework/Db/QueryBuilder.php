<?php
namespace HappyCake\Db;

use HappyCake\Db\DB;

/**
 * Created by PhpStorm.
 * User: kate
 * Date: 14.03.16
 * Time: 23:52
 */
class QueryBuilder
{

    protected $sqlParts = array(
        'select' => array(),
        'from' => array(),
        'join' => array(),
        'set' => array(),
        'where' => null,
        'limit' => null,
        'groupBy' => array(),
        'having' => null,
        'orderBy' => array()
    );

    protected $params;

    public function __construct()
    {
    }

    /**
     * установка SELECT
     * Пример:
     * $condition = array('id', 'name', 'address'); или $condition = '*';
     * $this->db->select($condtition)->from('user')->read();
     */

    public function select($column = '*')
    {
        $this->sqlParts['select'] = $column;
        return $this;
    }

    /**
     * установка FROM
     * Пример:
     * $table = 'user';
     * $this->db->select('*')->from($table)->read();
     */

    public function from(array $table)
    {
        $this->sqlParts['from'] = $table;
        return $this;
    }


    /**
     * установка WHERE
     * Пример:
     * $where = array('id >= :id', 'name = :name');
     * $params = array('id' => 2, 'name' => 'Hello');
     * $limit = 10;
     * $this->db->select('*')->from('user')->where($where, $params)->read();
     */


    public function where($where = null, $params = array(), $operator = null)
    {
        if (!empty($where)) {
            //prepared statements
            $this->params = $params;

            if (is_array($where)) {
                if (!empty($operator)) {
                    $where = implode($operator, $where);
                } else {
                    $where = implode(' AND ', $where);
                }
            } else {
                $where = "WHERE " . $where;
            }
        }


        $this->sqlParts['where'] = $where;
        return $this;
    }


    /**
     * установка LIMIT
     * Пример:
     * $this->db->select('*')->from('user')->where($where, $params)->limit(10)->read();
     */

    public function limit($number = null)
    {
        $this->sqlParts = (string)$number;
        return $this;
    }


    /**
     * Возвращает строку запроса
     */

    public function getSql()
    {

        $query = "SELECT " . implode(', ', $this->sqlParts['select']) . " FROM ";
        $query .= implode(', ', $this->sqlParts['from'])
            . ($this->sqlParts['where'] !== null ? " WHERE " . ((string)$this->sqlParts['where']) : "")
            . ($this->sqlParts['groupBy'] ? " GROUP BY " . join(", ", $this->sqlParts['groupBy']) : "")
            . ($this->sqlParts['having'] !== null ? " HAVING " . ((string)$this->sqlParts['having']) : "")
            . ($this->sqlParts['orderBy'] ? " ORDER BY " . join(", ", $this->sqlParts['orderBy']) : "")
            . ($this->sqlParts['limit'] ? " LIMIT " . $this->limit . " OFFSET " . $this->offset : "");
        return $query;
    }


    /**
     * Считывает полученные из запроса данные
     */

    public function exec()
    {

        //массив данных
        $data = array();

        //запрос
        $query = trim($this->getSql());

        //выполнение запроса
        try {
            $sth = DB::getConnection()->prepare($query);
            $sth->execute($this->params);
            while ($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        } catch (PDOException $e) {
            echo 'Ошибка выборки данных :' . $e->getMessage();
        }
    }



//EXAMPLE

    /*$db = new DB();
    $db->instance();
    //$db->select(array('user_name'))->from('Users')->where('id=3');
        $db->insert('Users',array('id','user_name', 'user_sername','password'), array('11','Sah', 'Bloggs','082332'));
    //print_r($db->read());
    */


}