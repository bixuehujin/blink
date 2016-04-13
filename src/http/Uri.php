<?php

namespace blink\http;

use blink\core\InvalidParamException;
use blink\core\Object;
use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 *
 * @property string $authority
 *
 * @package blink\http
 * @since 0.3
 */
class Uri extends Object implements UriInterface
{
    /**
     * @var string
     */
    public $scheme = '';

    /**
     * @var string
     */
    public $userInfo = '';

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var integer
     */
    public $port;

    /**
     * @var string
     */
    public $path = '';

    /**
     * @var string
     */
    public $query = '';

    /**
     * @var string
     */
    public $fragment = '';

    private $_uriString;

    protected $defaultPortsMap = [
        'http'  => 80,
        'https' => 443,
    ];

    /**
     * @inheritDoc
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        if (empty($this->host)) {
            return '';
        }

        $authority = $this->host;
        if (!empty($this->userInfo)) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->isNonStandardPort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $new  = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        if ($password) {
            $user .= ':' . $password;
        }

        $new = clone $this;
        $new->userInfo = $user;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    public function setUriString($uriString)
    {
        $this->_uriString = $uriString;

        $this->parseUri($uriString);
    }

    private function isNonStandardPort($scheme, $host, $port)
    {
        if (!$scheme) {
            return true;
        }

        if (!$host || !$port) {
            return false;
        }

        return !isset($this->defaultPortsMap[$scheme]) || $port !== $this->defaultPortsMap[$scheme];
    }

    /**
     * @param $uri
     */
    protected function parseUri($uri)
    {
        $parts = parse_url($uri);

        if (false === $parts) {
            throw new InvalidParamException('The source URI string appears to be malformed');
        }

        $this->scheme    = isset($parts['scheme'])   ? $parts['scheme']   : '';
        $this->userInfo  = isset($parts['user'])     ? $parts['user']     : '';
        $this->host      = isset($parts['host'])     ? $parts['host']     : '';
        $this->port      = isset($parts['port'])     ? $parts['port']     : null;
        $this->path      = isset($parts['path'])     ? $parts['path']     : '';
        $this->query     = isset($parts['query'])    ? $parts['query']    : '';
        $this->fragment  = isset($parts['fragment']) ? $parts['fragment'] : '';

        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    public function __toString()
    {
        if (null !== $this->_uriString) {
            return $this->_uriString;
        }

        return $this->_uriString = $this->buildUriString();
    }

    public function __clone()
    {
        $this->_uriString = null;
    }

    protected function buildUriString()
    {
        $uri = '';

        if (!empty($this->scheme)) {
            $uri .= $this->scheme . '://';
        }

        if (!empty($this->authority)) {
            $uri .= $this->authority;
        }

        $path = $this->path;
        if ($path) {
            if (empty($path) || '/' !== substr($path, 0, 1)) {
                $path = '/' . $path;
            }

            $uri .= $path;
        }

        if ($this->query) {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
