<?php
namespace HappyCake\Http;

class Uri
{
    /** @var string */
    private $url = '';
    /** @var string */
    private $scheme = 'http';

    private $defaultPorts = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'news' => 119,
        'nntp' => 119
    ];

    /** @var string */
    private $host = '';

    /** @var int|NULL */
    private $port = null;

    /** @var string */
    private $user = '';

    /** @var string */
    private $pass = '';

    /** @var string */

    private $path = '';

    /** @var string */
    private $query = '';

    /** @var string */
    private $fragment = '';

    /** @var string */
    /** Keeps Uri decoded parts*/
    private $params = [];


    public function __construct($uri = '')
    {
        $this->url = $uri;
        $this->parseUrl($this->url);
    }

    private function parseUrl($uri)
    {
        $parsed_uri = parse_url($uri);

        $this->scheme = !empty($parsed_uri['scheme']) ? $parsed_uri['scheme'] : $this->scheme;

        $this->host = !empty($parsed_uri['host']) ? $parsed_uri['host'] : $this->host;

        $this->port = !empty($parsed_uri['port']) ? $parsed_uri['port'] : $this->port;

        $this->user = !empty($parsed_uri['user']) ? $parsed_uri['user'] : $this->user;

        $this->pass = !empty($parsed_uri['pass']) ? $parsed_uri['pass'] : $this->pass;

        // $this->path = empty($parsed_uri['path']) ? $this->path : $parsed_uri['path'] ;
        $this->path = !empty($parsed_uri['path']) ? $parsed_uri['path'] : $this->path;

        $this->query = !empty($parsed_uri['query']) ? $this->normalizeQuery($parsed_uri['query']) : $this->normalizeQuery($this->query);

        $this->fragment = !empty($parsed_uri['fragment']) ? rawurlencode($parsed_uri['fragment']) : $this->fragment;

        if (!empty($parsed_uri['pass'])) {

            $this->user .= ':' . $parsed_uri['pass'];

        }
        if (!empty($parsed_uri['query'])) {
            parse_str(($this->query), $rez);
            $this->params = $rez;
        }

    }

    public function normalizeQuery($query)
    {

        $query = rawurldecode($query);
        $params = explode('&', $query);
        foreach ($params as &$param) {
            $encodestr = substr($param, strpos($param, '=') + 1);
            $param = str_replace($encodestr, rawurlencode($encodestr), $param);
        }
        return implode('&', $params);
    }

    /**
     * Retrieve the user information component of the URI.
     * @return string The URI user information, in "username[:password]" format.
     */

    public function getUserInfo()
    {
        return $this->user;
    }

    public function getQueryParams()
    {
        return $this->params;
    }

    /**
     * @param string $scheme The scheme to use with the new instance.
     * @return self A new instance with the specified scheme
     */
    public function withScheme($scheme)
    {
        // $new returns same instance if no change was made.
        $new = clone $this;
        $scheme = (string)$scheme;
        if ($this->scheme !== $scheme) {
            $new->scheme = $scheme;
        }
        return $new;
    }

    /**
     * Return an instance with the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        // $new returns same instance if no change was made.
        $new = clone $this;
        $user = (string)$user;

        if ($this->user !== $user) {
            $new->user = $user;
        }
        if (empty($user)) {
            $new->user = '';
            return $new;
        }

        if (isset($password)) {
            $new->user .= ':' . $password;
        }

        return $new;
    }

    /**
     * Return an instance with the specified host.
     * An empty host value is equivalent to removing the host.
     * @param string $host The hostname to use with the new instance.
     * @return self A new instance with the specified host.
     */

    public function withHost($host)
    {
        // $new returns same instance if no change was made.
        $new = clone $this;

        if ($this->host !== $host) {
            $new->host = $host;
        }

        if (empty($host)) {
            $new->host = '';
            return $new;
        }
        return $new;
    }

    /**
     * Return an instance with the specified port.
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return self A new instance with the specified port.
     */
    public function withPort($port)
    {   // $new returns same instance if no change was made.
        $new = clone $this;

        if (empty($port)) {
            $new->port = null;
            return $new;
        }

        if ($port !== $this->port) {
            $new->port = $port;
        }

        if ($port !== null && $port < 1 || $port > 65535) {
            return;
        }

        return $new;

    }

    /**
     * Return an instance with the specified path.
     * @param string $path The path to use with the new instance.
     * @return self A new instance with the specified path.
     */

    public function withPath($path)
    {
        $new = clone $this;
        if (empty($path)) {
            $new->path = '/';
            return $new;
        }
        $new->path = $path;
        return $new;
    }

    /**
     * Return an instance with the specified query string.
     * An empty fragment value is equivalent to removing the fragment.
     * @param string $query The fragment to use with the new instance.
     * @return self A new instance with the specified query string.
     */

    public function withQuery($query)
    {
        $new = clone $this;
        if (empty($query)) {
            $new->query = '';
            return $new;
        }
        if ($this->query !== $query) {
            $new->query = $this->normalizeQuery($query);
        }

        return $new;
    }

    /**
     * Return an instance with the specified URI fragment.
     * An empty fragment value is equivalent to removing the fragment.
     * @param string $fragment The fragment to use with the new instance.
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        {
            $new = clone $this;
            if (empty($fragment)) {
                $new->fragment = '';
                return $new;
            }
            if ($this->fragment !== $fragment) {
                $new->fragment = $fragment;
            }

            return $new;
        }
    }

    public function getDefaultPort()
    {
        return $this->defaultPorts[$this->scheme];
    }

    public function getHostFromUri()
    {
        $host = $this->getHost();
        $host .= $this->getPort() ? ':' . $this->getPort() : '';
        return $host;
    }

    /**
     * Retrieve the host component of the URI.
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     **/

    public function getHost()
    {
        return $this->host;
    }

    /**
     * Retrieve the port component of the URI.
     * @return null|int The URI port.
     */

    public function getPort()
    {
        return $this->port;
    }

    /**
     * Check if url is encoded
     * @param string $query
     * @return bool
     */

    public function is_queryEncoded($query)
    {
        return (strcasecmp(rawurlencode($query), $query) == 0) ? true : false;
    }

    public function __toString()
    {
        $url = '';
        if ($this->getScheme()) {
            $url .= $this->getScheme() . ':';
        }
        if ($this->getAuthority()) {
            $url .= '//' . $this->getAuthority();
        }

        if ($this->getPath()) {
            $url .= $this->getPath();
        }
        if ($this->getQuery()) {
            $url .= '?' . $this->getQuery();
        }
        if ($this->getFragment()) {
            $url .= '#' . $this->getFragment();
        }
        return $url;
    }

    /**
     * @return string The URI scheme
     */

    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI.
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */

    public function getAuthority()
    {
        if (empty($this->host)) {
            return '';
        }
        $authority = $this->host;
        if (!empty($this->user)) {
            $authority = $this->user . '@' . $authority;
        }
        $authority .= ':' . $this->port;
        return $authority;
    }

    /**
     * Retrieve the path component of the URI.
     * @return string The URI path
     */
    public function getPath()
    {
        return $this->path;
    }


    //normalize query encode part after '='

    /**
     * Retrieve the query string of the URI.
     * @return string The URI query string.
     */

    public function getQuery()
    {
        return $this->query;
    }

    /**
     *  Retrieve the fragment component of the URI.
     * * @return string The URI fragment.
     */

    public function getFragment()
    {
        return $this->fragment;
    }


}
