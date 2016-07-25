<?php
namespace HappyCake\Http;

class Message
{
    /**
     * $headers = array($key=>array($values))
     * keeps all $keys in Lower-Case
     */

    protected $headers = [];
    private $protocolVersion = '1.1';

    public function __construct(array $headers = [])
    {
        $this->headers = $this->normalizeHeader($headers);
    }

    /**@return lowCaseHeader
     */
    public function normalizeHeader($headers)
    {
        $lowKeysHeader = array_change_key_case($headers, CASE_LOWER);

        foreach ($lowKeysHeader as $values => $val) {
            if (!is_array($val)) {
                $lowKeysHeader[$values] = [$val];
            }
        }
        return $lowKeysHeader;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param string|integer $version .
     * @return self
     */

    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocolVersion = (string)$version;
        return $new;
    }

    /* @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        $name = strtolower($name);
        if ($this->hasHeader($name)) {
            if (!empty($this->headers[$name])) {
                $value = $this->headers[$name];
                return $value;
            }
        }
        return [];
    }

    public function hasHeader($name)
    {
        $name = strtolower($name);
        return array_key_exists($name, $this->headers);
    }

    /**
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     */

    public function getHeaderLine($name)
    {
        $name = strtolower($name);

        if ($this->hasHeader($name)) {
            if (!empty($this->headers[$name])) {
                $value = $this->headers[$name];
                return implode(',', $value);
            }
        }
        return '';
    }

    public function addHeader($name, $value)
    {
        $name = strtolower($name);

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $this->headers[$name] = $value;
        //$new = clone $this;
        //return $new;
        return $this;
    }

    /** Return an instance with the specified header appended with the given value.
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     */

    public function withAddedHeader($name, $value)
    {
        $name = strtolower($name);
        $new = clone $this;

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if ($this->hasHeader($name)) {
            $new->headers[$name] = array_merge($this->headers[$name], $value);
        } else {
            $new->withHeader($name, $value);
        }

        return $new;
    }


    /**
     * Sets new header. Delete the old one
     * @param array $name Case-insensitive header field names to set.
     * @param string[] $value Header value(s).
     * @example setHeader(array(Host,CacheControl),array(text/html,application/xhtml+xml,max-age=0))
     * @return self
     */

    /*
        public function setHeader($name, $value)
        {
            $this->headers = [];

            foreach ($value as $val) {
                if (is_string($value)) {
                    $value = explode(',', $value);
                }
            }
            $this->headers[$name] = $value;
            return $this;
        }
    */

    /**Return an instance with the provided value replacing the specified header.
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return self
     */


    public function withHeader($name, $value)
    {
        $name = strtolower($name);
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        $new = clone $this;
        $new->headers[$name] = $value;
        return $new;
    }

    /**
     * @param string $name Case-insensitive header field name to remove.
     * @return self
     */
    public function withoutHeader($name)
    {
        $name = strtolower($name);
        $new = clone $this;
        if ($this->hasHeader($name)) {
            unset($new->headers[$name]);
        }
        return $new;
    }

    public function showHeaders()
    {
        echo "<pre>";

        print_r($this->getHeaders());
        echo "</pre>";
    }


    // отправить заголовки Http

    public function getHeaders()
    {
        return $this->headers;
    }


    public function sendHeaders()
    {
        $headers = array();
        foreach ($this->headers as $name => $value) {
            $headers[] = $name . ': ' . implode(',', $value);
            header(array_pop($headers));
        }
    }


    public function headerToSend()
    {
        $headerKeys = array_keys($this->headers);
        $headerValues = array_values($this->headers);

        $newKeys = [];
        foreach ($headerKeys as $values) {
            $keysParts = explode('-', $values);
            foreach ($keysParts as &$val) {
                $val = ucfirst($val);
            }
            $newKeys[] = implode('-', $keysParts);
        }

        return array_combine($newKeys, $headerValues);
    }


    public function __toString()
    {
        $headers = array();

        foreach ($this->headers as $name => $value) {
            $headers[] = $name . ': ' . implode(',', $value);
        }
        $headers = join("\n\t", $headers);
        return $headers;
    }


}