<?php
namespace HappyCake\Config;
ini_set('display_errors', 1);

//PATH - путь до приложения относительно корня сервера.
//URL - абсолютный URL приложения.
//DIR - абсолютный путь до приложения.
//define('PATH', '/' . 'mvc_php');
//define('URL', 'http://' . $_SERVER['HTTP_HOST'] . PATH);
//require_once 'settings.php';

class Config
{
    private static $configFile = array();

    public static function registration($name, $path)
    {
        $newConfigFile = require_once($path);
        self::$configFile[$name] = $newConfigFile;
    }

    public static function getParams($conf, $key = null)
    {
        if (!empty($key)) {
            return self::$configFile[$conf][$key];
        } else {
            return self::$configFile[$conf];
        }

    }
}





