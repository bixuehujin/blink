<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\server;

use blink\http\File;
use blink\http\Request;
use blink\http\Response;
use blink\http\Stream;
use blink\http\Uri;

/**
 * The CgiServer makes it possible to run Blink application upon php-fpm or Apache's mod_php.
 *
 * @package blink\server
 * @author Jin Hu <bixuehujin@gmail.com>
 * @since 0.2.0
 */
class CgiServer extends Server
{
    protected function extractHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strncmp($name, 'HTTP_', 5) === 0) {
                $headers[substr($name, 5)] = $value;
            }
        }

        return $headers;
    }

    private $_files = [];

    protected function loadFilesRecursive($key, $names, $tempNames, $types, $sizes, $errors)
    {
        if (is_array($names)) {
            foreach ($names as $i => $name) {
                self::loadFilesRecursive($key . '[' . $i . ']', $name, $tempNames[$i], $types[$i], $sizes[$i], $errors[$i]);
            }
        } elseif ($errors !== UPLOAD_ERR_NO_FILE) {
            $this->_files[$key] = new File([
                'name' => $names,
                'tmpName' => $tempNames,
                'type' => $types,
                'size' => $sizes,
                'error' => $errors,
            ]);
        }
    }

    /**
     * Normalize the PHP $_FILE array.
     *
     * @param $files
     * @return array
     */
    protected function normalizeFiles($files)
    {
        foreach ($files as $name => $info) {
            $this->loadFilesRecursive($name, $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
        }

        return $this->_files;
    }

    protected function extractRequest()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }

        $protocolParts = explode('/', $_SERVER['SERVER_PROTOCOL']);
        $hostParts = explode(':', $_SERVER['HTTP_HOST']);

        $uriConfig = [
            'scheme' => strtolower($protocolParts[0]),
            'query' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
            'path' => parse_url($requestUri, PHP_URL_PATH),
            'host' => $hostParts[0],
            'port' => isset($hostParts[1]) ? $hostParts[1] : 80,
        ];

        $body = new Stream('php://memory', 'w+');
        $body->write(file_get_contents('php://input'));

        $config = [
            'protocol' => $protocolParts[1],
            'uri' => new Uri('', $uriConfig),
            'method' => strtoupper($_SERVER['REQUEST_METHOD']),
            'headers' => $this->extractHeaders(),
            'params' => $_GET,
            'cookies' => $_COOKIE,
            'body' => $body,
        ];

        if (!empty($_FILES)) {
            $config['files'] = $this->normalizeFiles($_FILES);
        }

        return new Request($config);
    }

    protected function response(Response $response)
    {
        foreach ($response->headers->all() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach($values as $value) {
                header($name . ': ' . $value, false, $response->statusCode);
            }
        }

        foreach ($response->cookies as $cookie) {
            setcookie($cookie->name, $cookie->value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }

        echo $response->content();
    }

    public function run()
    {
        $this->startApp();

        $response = $this->handleRequest($this->extractRequest());

        $this->response($response);
    }
}
