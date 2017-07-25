<?php

namespace blink\http;

use blink\core\MiddlewareTrait;
use blink\core\Object;
use blink\core\ShouldBeRefreshed;
use blink\support\Json;
use blink\core\InvalidParamException;

/**
 * Class Response
 *
 * @property CookieBag $cookies
 * @package blink\http
 */
class Response extends Object implements ShouldBeRefreshed
{
    use MiddlewareTrait;

    public $data;

    /**
     * @var HeaderBag
     */
    public $headers;

    public $version = '1.0';

    public $statusCode = 200;

    public $statusText;

    public static $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
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
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    protected $content;

    protected $prepared = false;

    public function init()
    {
        $this->headers = new HeaderBag();
    }

    public function status($code, $text = null)
    {
        if (!isset(self::$httpStatuses[$code])) {
            throw new InvalidParamException("The HTTP status code is invalid: $code");
        }

        $this->statusCode = $code;

        if ($text === null) {
            $this->statusText = isset(static::$httpStatuses[$this->statusCode]) ? static::$httpStatuses[$this->statusCode] : '';
        } else {
            $this->statusText = $text;
        }
    }

    /**
     * Redirects to the specified url.
     *
     * @param string $url
     * @param int    $statusCode
     * @since 0.2.0
     */
    public function redirect($url, $statusCode = 302)
    {
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $url = request()->root() . $url;
        }

        $this->status($statusCode);
        $this->headers->set('Location', $url);
    }

    private $_cookies;

    /**
     * Returns the cookies that should be sent with the response.
     *
     * @return CookieBag
     */
    public function getCookies()
    {
        if (!$this->_cookies) {
            $this->_cookies = new CookieBag();
        }

        return $this->_cookies;
    }

    public function with($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Prepare the response to ready to send to client.
     */
    public function prepare()
    {
        if (!$this->prepared) {
            $this->content = is_string($this->data) ? $this->data : Json::encode($this->data);
            if ($this->data !== null && !is_string($this->data) && !$this->headers->has('Content-Type')) {
                $this->headers->set('Content-Type', 'application/json');
            }

            $this->prepared = true;
        }
    }

    /**
     * Gets the raw response content.
     *
     * @return string
     */
    public function content()
    {
        if (!$this->prepared) {
            $this->prepare();
        }

        return $this->content;
    }
}
