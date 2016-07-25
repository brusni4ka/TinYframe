<?php
namespace HappyCake\Http;
require_once 'Message.php';
require_once 'RequestCreate.php';
require_once 'Uri.php';

ini_set('display_errors', 1);


class Request extends Message
{


    /** @var object */
    public $uri;
    /** @var string */
    private $method = '';
    /** @var array */
    private $allowedMethods = ['PUT', 'POST', 'GET', 'HEAD', 'DELETE', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE', 'CONNECT'];
    private $server;
    private $requestTarget = '';
    private $controller;
    private $action;
    private $params;

    public function __construct($uri = null, $method = null, array $server = [], array $header = [])
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        $this->uri = $uri ?: new Uri();
        parent::__construct($header);
        $this->method = $method;
        $this->parseUri();
        $this->server = $server;
    }

    private function parseUri()
    {

        $uri = $this->uri->getPath();
        $uri = rtrim($uri, '/');
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        $uri = explode('/', $uri);

        array_shift($uri);
        $this->controller = array_shift($uri);
        $this->action = array_shift($uri);

        //if uri looks like controller/action/param/value
        if (!empty($uri[0]) && count($uri) % 2 === 0) {
            for ($keys = array(), $values = array(), $i = 0; $i < count($uri); $i++) {
                if ($i % 2 === 0) {
                    array_push($keys, $uri[$i]);
                } else {
                    array_push($values, $uri[$i]);
                }
            };

            $this->params = array_combine($keys, $values);
            return;
        }
//if uri looks like controller/action?param=value
        $this->params = $this->uri->getQueryParams();

    }

    public static function __callStatic($methodName, $arguments)
    {
        if (method_exists(RequestCreate::class, $methodName)) {
            return call_user_func_array(array(RequestCreate::class, $methodName), $arguments);
        }
    }

    /**
     * Retrieves the Http method of the request.
     * @return string Returns the request method.
     */

    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided Http method.
     * @param string $method Case-sensitive method.
     * @return self
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $method = strtoupper($method);

        if (in_array($method, $this->allowedMethods)) {
            $new->method = $method;
        }

        return $new;
    }

    /**
     * Retrieves the URI instance.
     * @return Uri instance
     * representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    public function withUri(Uri $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->uri = $uri;
        $new->parseUri();
        if ($preserveHost && $this->hasHeader('Host')) {
            return $new;
        }
        if (!$uri->getHost()) {
            return $new;
        }
        $host = $uri->getHost();
        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }
        return $new->withHeader('Host', [$host]);

    }

    public function getRequestTarget()
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }
        $this->requestTarget = $this->uri->getPath();
        if ($this->uri->getQueryLine()) {
            $this->requestTarget .= '?' . $this->uri->getQueryLine();
        }
        return $this->requestTarget;
    }

    public function find($key)
    {
        return $_REQUEST[$key];
    }

    public function isMethod($method)
    {
        return in_array($method, $this->allowedMethods);
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

//For using FromGlob and Create func in RequestCreate

    public function getParams()
    {
        return $this->params;
    }


}

//TEST
/*
$var = new Request();
$var = Request::create('https://yiiframework.com.ua/controller/action/mama/20','PATCH');
//$var1 = Request::createFromGlobals();
print_r( $var->getParams());
 $var1 = $var->withUri(new Uri('https://mamam.com.ua/controller/action/mama/24','PATCH'));


print_r( $var1->getParams());

*/