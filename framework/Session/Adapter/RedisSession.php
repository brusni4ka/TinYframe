<?php
namespace HappyCake\Session\Adapter;

use HappyCake\Config\Config;
use Predis;

/**
 * Created by PhpStorm.
 * User: kate
 * Date: 08.03.16
 * Time: 0:58
 */
class RedisSession extends \Predis\Session\Handler
{

    protected $settings;

    public function __construct()
    {

        $this->settings = Config::getParams('redis');

        if (!class_exists('\\Predis\\Client')) {
            throw new ComponentException(
                "Predis library not found. Install Predis library [https://github.com/nrk/predis/wiki]"
            );
        }
        parent::__construct(new Predis\Client('tcp://192.168.0.44:6379'));
    }

}
/*
class RedisSession implements \SessionHandlerInterface{

    public function open($save_path, $session_id)
    {
        // TODO: Implement open() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }


    public function read($session_id)
    {
        // TODO: Implement read() method.
    }

    public function write($session_id, $session_data)
    {
        // TODO: Implement write() method.
    }


    public function destroy($session_id)
    {
        // TODO: Implement destroy() method.
    }

    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
    }

}
*/

/*



}*/