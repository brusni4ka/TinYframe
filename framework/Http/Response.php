<?php
namespace HappyCake\Http;
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 31.01.16
 * Time: 23:34
 */
ini_set('display_errors', 1);

class  Response extends Message
{

    private $statusCode;

    private $customReasonPhrase = [];

    private $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'Http Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];


    public function __construct($content = '', $code = 200, $header = [])
    {
        $this->content = $content;
        $this->statusCode = $code;
        parent::__construct($header);
    }

    public function withStatus($code, $phrase = '')
    {
        $new = clone $this;

        if ($code > 99 && $code < 512 && is_int($code)) {
            $new->statusCode = $code;
            if ($phrase) {
                $new->customReasonPhrase[$code] = $phrase;
            }
        }
        return $new;
    }

    public function send()
    {
        $code = $this->statusCode;
        $phrase = $this->getReasonPhrase();
        $version = $this->getProtocolVersion();
        header('Http/' . $version . " " . $code . " " . $phrase, true);
        switch ($this->getStatusCode()) {
            case 204:
                $this->sendHeaders();
                break;
            case $this->getStatusCode() >= 400:
                $this->sendHeaders();
                break;
            default:
                $this->sendHeaders();
                echo $this->content;
        }

    }

    public function getReasonPhrase()
    {
        $code = $this->statusCode;
        $phrase = $this->phrases[$code];

        if (array_key_exists($code, $this->customReasonPhrase)) {
            $phrase = $this->customReasonPhrase[$code];
        }

        return $phrase;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }


}