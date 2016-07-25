<?php
namespace HappyCake\Session;

use HappyCake\Config\Config as Config;


class Session
{
    //way to save sessions Db|files|redis
    private $adapter;
    //PHPSESSID by default;
    private $name;

    private $ttl = 160;


    const SESSION_STARTED = TRUE;
    const SESSION_NOT_STARTED = FALSE;
    const ADAPTER_NAMESPACE_PREF = '\\HappyCake\\Session\\Adapter\\';

    // The state of the session
    private $sessionState = self::SESSION_NOT_STARTED;
    private static $instance;
    private static $settings;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     *    Returns THE instance of 'Session'.
     *    The session is automatically initialized if it wasn't.
     *
     * @return    object
     **/

    public static function getSession()
    {
        if (!self::$instance) {
            self::$settings = Config::getParams('session', 'settings');
            self::$instance = new static();
        }
        //возвращаем $instance
        return self::$instance;
    }

    /**
     *    Stores data in the session.
     *    Example: $instance->foo = 'bar';
     *
     * @param    name
     * @param    value
     * @return    void
     **/

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     *    Gets data from the session.
     *    Example: echo $instance->foo;
     *
     * @param    name
     * @return    mixed
     **/

    public function __get($name)
    {
        echo "hre";
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return null;
    }


    public function start()
    {
        if ($this->sessionExists()) {
            return;
        }

        $this->initAdapter();
        $this->sessionState = self::SESSION_STARTED;
        session_start();
        return $this;
    }


    public function setAdapter($saveHandler)
    {
        if ($this->sessionExists()) {
            throw new SessionException(
                'Session have already started'
            );
        }
        $this->adapter = $saveHandler;
        return $this;
    }


    public function sessionExists()
    {
        return $this->sessionState;
    }


    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }

    public function __unset($name)
    {
        unset($_SESSION[$name]);
    }


    public function setName($name)
    {
        if ($this->sessionExists()) {
            throw new SessionException(
                'Session have already started'
            );
        }
        $this->name = $name;
        session_name($name);
        return $this;
    }

    protected function initAdapter()
    {
        echo $this->adapter;
        if (is_null($this->adapter)) {
            $this->adapter = Config::getParams('session', 'adapter');
            if ($this->getAdapter() == 'files') {
                // try to apply settings
                if ($settings = self::$settings['files']) {
                    $this->setSavePath($settings['save_path']);
                }
                return true;
            }
        }

        if (is_string($this->adapter)) {

            $adapterClass = self::ADAPTER_NAMESPACE_PREF . ucfirst($this->adapter);
            if (!class_exists($adapterClass)) {//|| !is_subclass_of($adapterClass, '\SessionHandlerInterface')) {
                throw new ComponentException("Class for session adapter `{$this->adapter}` not found");
            }
            $settings = isset(self::$settings[$this->adapter]) ? self::$settings[$this->adapter] : array();

            $this->adapter = new $adapterClass($settings);
        }

        return session_set_save_handler($this->adapter);
    }

    public function setCookieLifetime($ttl = null)
    {
        // Set new cookie TTL
        $ttl = isset($ttl) ? $ttl : $this->ttl;

        session_set_cookie_params($ttl);
        if ($this->sessionExists()) {
            // There is a running session so we'll regenerate id to send a new cookie
            $this->regenerateId();
        }
    }


    protected function setSavePath($savePath)
    {
        if (!is_dir($savePath)
            || !is_writable($savePath)
        ) {
            throw new ComponentException('Session path is not writable');
        }
        session_save_path($savePath);
        return $this;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getName()
    {
        return session_name();
    }


    public function regenerateId($deleteOldSess = true)
    {
        session_regenerate_id($deleteOldSess);
    }

    /**
     *    Destroys the current session.
     *
     * @return    bool    TRUE is session has been deleted, else FALSE.
     **/


    public function destroy()
    {
        if ($this->sessionExists()) {
            session_unset();
            $this->sessionState = !session_destroy();
            return !$this->sessionState;
        }
        return false;
    }


}