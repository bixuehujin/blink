<?php

namespace blink\http;

use blink\auth\Authenticatable;
use blink\core\InvalidParamException;
use blink\core\MiddlewareTrait;
use blink\core\NotSupportedException;
use blink\core\BaseObject;
use blink\core\ShouldBeRefreshed;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Request
 *
 * @property ParamBag               $params  The collection of query parameters
 * @property HeaderBag              $headers The collection of request headers
 * @property ParamBag               $payload    The collection of request body
 * @property FileBag                $files   The collection of uploaded files
 * @property CookieBag              $cookies The collection of received cookies.
 * @property Uri                    $uri     The uri instance of the request
 * @property \blink\session\Session $session The session associated to the request
 * @package blink\http
 */
class Request extends BaseObject implements ShouldBeRefreshed, ServerRequestInterface
{
    use MiddlewareTrait;
    use MessageTrait;

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';

    public $method = '';

    /**
     * The key of a header field that stores the session id, or a callable that will returns the session id.
     * The following is the signature of the callable:
     * ```
     * string function (Request $request);
     * ```
     *
     * **deprecated**
     *
     * The sessionKey configuration is deprecated since v0.3.1, which will be removed in future release, please using
     * CookieAuthenticator or custom middleware to resolve the session of a request.
     *
     * @var string|callable
     * @deprecated
     */
    public $sessionKey = 'X-Session-Id';

    public function method()
    {
        return $this->method;
    }

    /**
     * Returns whether the request method is the given $method.
     *
     * @param $method
     * @return bool
     */
    public function is($method)
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Checks whether the path of the request match the given pattern.
     *
     * @param $pattern
     * @return boolean
     */
    public function match($pattern)
    {
        return preg_match($pattern, $this->uri->path);
    }

    /**
     * Returns whether this request is secure.
     *
     * @return boolean
     */
    public function secure()
    {
        if ($this->headers->first('x-forwarded-proto') === 'https') {
            return true;
        }

        if ((int)$this->headers->first('x-forwarded-port') === 443) {
            return true;
        }

        return 'https' === $this->uri->scheme;
    }

    private $_params;

    public function setParams($params = [])
    {
        if (!$params instanceof ParamBag) {
            $params = new ParamBag($params);
        }

        $this->_params = $params;
    }

    public function getParams()
    {
        if ($this->_params !== null) {
            return $this->_params;
        }

        $params = [];
        if ($query = $this->uri->getQuery()) {
            $params = $this->parseQueryString($query);
        }

        return $this->_params = new ParamBag($params);
    }

    private function parseQueryString($queryString)
    {

        $params = [];
        foreach (explode('&', $queryString) as $kvp) {
            $parts = explode('=', $kvp);
            $key = rawurldecode($parts[0]);
            $value = array_key_exists(1, $parts) ? rawurldecode($parts[1]) : null;
            $params[$key] = $value;
        }

        return $params;
    }

    private $_headers;

    public function setHeaders($headers = [])
    {
        if (!$headers instanceof HeaderBag) {
            $headers = new HeaderBag($headers);
        }

        $this->_headers = $headers;
    }

    public function getHeaders()
    {
        if ($this->_headers === null) {
            $this->_headers = new HeaderBag();
        }

        return $this->_headers;
    }

    private $_payload;

    public function getPayload()
    {
        if ($this->_payload !== null) {
            return $this->_payload;
        }

        if ($content = (string)$this->getBody()) {
            $payload = $this->parseBody($content);
        } else {
            $payload = [];
        }

        return $this->_payload = new ParamBag($payload);
    }

    public function setPayload($body = [])
    {
        if (!$body instanceof ParamBag) {
            $body = new ParamBag($body);
        }

        $this->_body = $body;
    }

    private $_files;

    /**
     * Defines the setter for files property.
     *
     * @param array $files
     */
    public function setFiles($files = [])
    {
        if (!$files instanceof FileBag) {
            $files = new FileBag($files);
        }

        $this->_files = $files;
    }

    /**
     * Defines the getter for files property.
     *
     * @return FileBag
     */
    public function getFiles()
    {
        if ($this->_files === null) {
            $this->_files = new FileBag();
        }

        return $this->_files;
    }

    private $_cookies;

    /**
     * Defines the setter for cookie property.
     *
     * @param $cookies
     */
    public function setCookies($cookies)
    {
        if (!$cookies instanceof CookieBag) {
            $cookies = new CookieBag(CookieBag::normalize($cookies));
        }

        $this->_cookies = $cookies;
    }

    /**
     * Defines the getter for cookies property.
     *
     * @return CookieBag
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieBag();
        }

        return $this->_cookies;
    }

    public function host()
    {
        $uri = $this->getUri();

        if (empty($uri->host) || empty($uri->port)) {
            return '';
        }

        $secure = $this->secure();

        if ((!$secure && $uri->port === 80) || ($secure && $uri->port === 443)) {
            return $uri->host;
        } else {
            return $uri->host . ':' . $uri->port;
        }
    }

    public function root()
    {
        if ($host = $this->host()) {
            return ($this->secure() ? 'https' : 'http') . '://' . $this->host();
        } else {
            return '';
        }
    }

    public function url($full = true)
    {
        if ($full) {
            $params = $this->getParams();
            $params = http_build_query($params->all());
            if ($params) {
                $params = '?' . $params;
            }
        } else {
            $params = '';
        }

        return $this->root() . $this->uri->path . $params;
    }

    /**
     * Returns the Content-Type of the request without it's parameters.
     *
     * @return string
     */
    public function getContentType()
    {
        $contentType = $this->headers->first('Content-Type');
        if (($pos = strpos($contentType, ';')) !== false) {
            $contentType = substr($contentType, 0, $pos);
        }

        return $contentType;
    }

    /**
     * Returns parameters of the Content-Type header.
     *
     * @return array
     */
    public function getContentTypeParams()
    {
        $contentType = $this->headers->first('Content-Type');
        $contentTypeParams = [];

        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);
            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    private function parseBody($body)
    {
        $parsedBody = [];
        $contentType = $this->getContentType();

        if ($contentType === 'application/json') {
            $parsedBody = json_decode($body, true);
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            parse_str($body, $parsedBody);
        } elseif ($contentType === 'multipart/form-data') {
            // noop
        } else {
            throw new NotSupportedException("The content type: '$contentType' does not supported");
        }

        return $parsedBody;
    }

    /**
     * Returns whether has an input value by key.
     *
     * @param $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->params->has($key) || $this->payload->has($key);
    }

    /**
     * Gets a input value by key.
     *
     * @param      $key
     * @param null $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        if (($value = $this->params->get($key)) !== null) {
            return $value;
        }

        if (($value = $this->payload->get($key)) !== null) {
            return $value;
        }

        return $default;
    }

    public function all()
    {
        return array_replace_recursive($this->params->all(), $this->payload->all());
    }

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return array_replace_recursive($this->params->only($keys), $this->payload->only($keys));
    }


    private $_session = false;

    /**
     * Sets the session of the request.
     *
     * @param $session
     */
    public function setSession($session)
    {
        $this->_session = $session;
    }

    /**
     * Returns the current session associated to the request.
     *
     * @return \blink\session\Session|null
     */
    public function getSession()
    {
        if ($this->_session === false) {
            $sessionId = is_callable($this->sessionKey) ? call_user_func($this->sessionKey,
                $this) : $this->headers->first($this->sessionKey);
            if ($session = session()->get($sessionId)) {
                $this->_session = $session;
            } else {
                $this->_session = null;
            }
        }

        return $this->_session;
    }

    private $_user = false;

    /**
     * Gets or sets the authenticated user for this request.
     *
     * @param Authenticatable $user
     * @return \blink\auth\Authenticatable|null
     */
    public function user($user = null)
    {
        if ($user !== null) {
            $this->_user = $user;

            return;
        }

        if ($this->_user === false) {
            if (($session = $this->getSession()) && $session->id) {
                $this->_user = auth()->who($session);
            } else {
                $this->_user = null;
            }
        }

        return $this->_user;
    }

    /**
     * Returns the whether the request is a guest request.
     *
     * @return bool
     */
    public function guest()
    {
        return $this->user() === null;
    }

    private $requestTarget;

    /**
     * @inheritDoc
     */
    public function getRequestTarget()
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $uri = $this->getUri();

        $target = $uri->path;
        if ($query = $uri->query) {
            $target .= '?' . $query;
        }

        if (empty($target)) {
            $target = '/';
        }

        return $target;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
        $new = clone  $this;
        $new->method = $method;

        return $new;
    }

    private $_uri;

    /**
     * @inheritDoc
     */
    public function getUri()
    {
        if (!$this->_uri) {
            $this->_uri = new Uri();
        }

        return $this->_uri;
    }

    public function setUri($uri)
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        } elseif ($uri === null) {
            $uri = new Uri();
        }

        if (!$uri instanceof UriInterface) {
            throw new InvalidParamException('Invalid URI provided');
        }

        $this->_uri = $uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->_uri = $uri;

        if ($preserveHost && $this->headers->has('Host')) {
            return $new;
        }

        if (!$uri->getHost()) {
            return $new;
        }

        $host = $uri->getHost();
        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }
        $new->headers->set('Host', $host);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getServerParams()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams()
    {
        $cookies = [];
        foreach ($this->cookies as $name => $cookie) {
            $cookies[$name] = $cookie->value;
        }

        return $cookies;
    }


    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookies->replace($cookies);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams()
    {
        return $this->params->all();
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->params->replace($query);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles()
    {
        return $this->files->all();
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $new->files->replace($uploadedFiles);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->getPayload()->all();
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->setPayload($data);

        return $new;
    }

    private $_attributes;

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        if (!$this->_attributes) {
            $this->_attributes = new ParamBag();
        }

        return $this->_attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes->set($name, $value);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
        $new = clone $this;
        $new->attributes->remove($name);

        return $new;
    }
}
