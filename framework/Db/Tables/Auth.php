<?php
namespace HappyCake\Db\Tables;

use HappyCake\Db\AbstractTable;

/**
 * Created by PhpStorm.
 * User: kate
 * Date: 01.04.16
 * Time: 21:12
 */


//new instance return a row of table 'users'
//Users::model() give the access to a whole table
class Auth extends AbstractTable
{

    public static $primaryKey = 'user_id';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }


}


