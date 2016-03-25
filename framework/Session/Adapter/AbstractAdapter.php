<?php

/**
 * Created by PhpStorm.
 * User: kate
 * Date: 11.03.16
 * Time: 22:45
 */
abstract class AbstractAdapter
{

    protected $settingMap = array();
    protected $adapter;

    protected function __construct()
    {
        $settingMap = Config::getParams('session');
    }

    public function getAdapterName()
    {
        return $this->adapter;
    }

}