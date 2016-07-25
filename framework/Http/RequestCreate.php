<?php
namespace HappyCake\Http;
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 22.02.16
 * Time: 22:37
 */
class RequestCreate
{

    private function __construct()
    {
    }

    /**
     * Creates a Request based on a given URI and configuration.
     * @param $uri
     * @param $method
     * @param $server
     * @return self
     */
    static public function create(
        $uri,
        $method = 'GET',
        $server = array(),
        $header = array()
    )
    {
        $components = parse_url($uri);
        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST'] = $components['host'];
            $header['HOST'] = $components['host'];
        }
        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $server['HTTPS'] = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }
        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] = $server['HTTP_HOST'] . ':' . $components['port'];
        }
        if (isset($components['user'])) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }
        if (isset($components['pass'])) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }
        if (!isset($components['path'])) {
            $components['path'] = '/';
        }


        return new Request($uri, $method, $server, $header);
    }

    /**
     * Creates a new request with values from PHP's super globals.
     * @return self
     */

    static public function createFromGlobals()
    {
        $url = '';
        $method = $_SERVER['REQUEST_METHOD'];
        $server = $_SERVER;
        $header = [];
        if ('cli-server' === php_sapi_name()) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        $scheme = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https' : 'http';

        $url .= $scheme . '://';

        if ($_SERVER["SERVER_PORT"] != '80' || '443') {

            $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];

        } else {

            $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        }

        foreach ($_SERVER as $value => $val) {
            if (stripos($value, 'HTTP') !== false) {
                $name = substr($value, strlen('HTTP_'));
                $name = str_replace('_', '-', $name);
                $valuesArray = explode(',', $val);
                $header[strtolower($name)] = $valuesArray;
            }
        }

        return new Request($url, $method, $server, $header);
    }


}