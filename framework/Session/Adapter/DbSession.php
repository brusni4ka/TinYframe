<?php
namespace HappyCake\Session\Adapter;

use HappyCake\Db\Db;


/**
 * Created by PhpStorm.
 * User: kate
 * Date: 01.03.16
 * Time: 2:38
 */
class DbSession implements \SessionHandlerInterface
{

    protected $dbh;

    public function read($session_id)
    {
        $sth = $this->dbh->prepare("SELECT session_data FROM sessions WHERE
session_id = ?");
        $sth->execute(array($session_id));
        $rows = $sth->fetchAll(\PDO::FETCH_NUM);
        if (count($rows) == 0) {
            return '';
        } else {
            return $rows[0][0];
        }
    }

    public function write($session_id, $session_data)
    {
        $sth = $this->dbh->prepare("UPDATE sessions SET session_data = ? WHERE session_id = ?");
        $sth->execute(array($session_data, $session_id));
        if ($sth->rowCount() == 0) {
            $sth2 = $this->dbh->prepare('INSERT INTO sessions (session_id,
        session_data, last_update)
        VALUES (?,?,?)');
            $sth2->execute(array($session_id, $session_data));


            /* $now = time();
             if (!DB::update('sessions', $session_data, 'session_id', $session_id)) {
                 echo "here";
                 Db::insert('sessions',
                     array('session_id', 'session_data', 'last_update'),
                     array($session_id, $session_data, $now));
             }

             /*$sth = $this->dbh->prepare("UPDATE sessions SET session_data = ?,
             last_update = ? WHERE session_id = ?");
             $sth->execute(array($session_data, $now, $session_id));
             if ($sth->rowCount() == 0) {
                 $sth2 = $this->dbh->prepare('INSERT INTO sessions (session_id,
             session_data, last_update)
             VALUES (?,?,?)');
                 $sth2->execute(array($session_id, $session_data, $now));*/
        }
    }

    function open($save_path, $name)
    {
        $this->dbh = DB::getConnection();
        $this->createSessionTable($save_path, NULL, false);
        return true;
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $sth = $this->dbh->prepare("DELETE FROM sessions WHERE session_id = ?");
        $sth->execute(array($session_id));
        return true;
    }

    public function gc($maxlifetime)
    {
        $sth = $this->dbh->prepare("DELETE FROM sessions WHERE last_update < ?");
        $sth->execute(array(time() - $maxlifetime));
        return true;
    }


    public function createSessionTable()
    {
        if (!$this->dbh) {
            $this->dbh = DB::getConnection();
        }
        $sql =
            <<<_SQL_
                    CREATE TABLE sessions (
        session_id VARCHAR(64) NOT NULL,
        session_data MEDIUMTEXT NOT NULL,
        last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (session_id)
        )
_SQL_;
        $this->dbh->exec($sql);
    }
}