<?php
require_once ROOT . '/classes/Db.php';

class Model
{
    //объект БД
    protected $db;

    public function __construct()
    {
        $this->db = new DB();
    }

}