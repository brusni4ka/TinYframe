<?php
namespace HappyCake\Db;

//https://phpdelusions.net/pdo#identifiers

use HappyCake\Config\Config;

//PDO::ATTR_ERRMODE: Режим сообщений об ошибках.
//PDO::ERRMODE_EXCEPTION: Выбрасывать исключения.
ini_set('display_errors', 1);

//require_once 'Config.php';

class DB
{
    protected static $dbh = null;
    private static $confParams = null;


    protected function __construct()
    {
        self::init();
    }


    private static function init()
    {
        self::$confParams = Config::getParams('db');

        if (self::$dbh === null) {
            echo "dbCoonnect";
            $dsn = 'mysql:host=' . self::$confParams['DB_HOST'] . ';dbname=' . self::$confParams['DB_NAME'];
            self::$dbh = new \PDO(
                $dsn,
                self::$confParams['DB_USER'],
                self::$confParams['DB_PASS'],
                self::$confParams['OPT']
            );
        }
        return self::$dbh;
    }

    /**
     * Устанавливает соединение с базой
     */


    public static function getConnection()
    {
        if (self::$dbh === null) {
            self::init();
        } else {
            return self::$dbh;
        }
    }

    /**
     * Прекращает соединение с базой
     */

    public function stopConnect()
    {
        self::$dbh = null;
    }


    public function checkConnect()
    {
        if (!isset(self::$dbh)) {
            return false;
        }
        return true;
    }


    /**
     * установка CREATE
     * Пример:
     */


    /** insert/update
     * установка INSERT
     *
     * Пример:
     * $this->db->insert('Users',array('id','user_name'), array('11','Sah'));
     */


    public function insert($table, $row = null, $values)
    {
        $dbh = self::$dbh;
        $row = !empty($row) ? '(' . implode(',', $row) . ')' : '';
        $values = '(\'' . implode('\',\'', $values) . '\')';
        $sth = $dbh->prepare("INSERT INTO $table $row VALUES $values");
        try {
            $dbh->beginTransaction();
            $sth->execute();
            $dbh->commit();

        } catch (Exception $e) {
            $dbh->rollBack();
            echo "Error: " . $e->getMessage();
        }

        return $this;
    }

    public function update($table, $values, $whereColumn, $whereValue)
    {
        $dbh = self::$dbh;

        $sth = $dbh->prepare("UPDATE $table SET $values WHERE $whereColumn = $whereValue");
        try {
            $sth->execute();
            if ($sth->rowCount() == 0) {
                return false;
            }
        } catch (Exception $e) {
            $dbh->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }


}