<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\server;

use blink\http\File;
use blink\http\HeaderBag;
use blink\http\Request;
use blink\http\Response;
use blink\http\Stream;
use blink\http\Uri;
use Symfony\Component\Dotenv\Dotenv;

/**
 * The CgiServer makes it possible to run Blink application upon php-fpm or Apache's mod_php.
 *
 * @package blink\server
 * @author Jin Hu <bixuehujin@gmail.com>
 * @since 0.2.0
 */
class CgiServer extends Server
{
    public function init()
    {
        if ($file = getenv('ENV_FILE')) {
            (new Dotenv())->load($file);
        }
    }

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

    protected function resolveSchema(HeaderBag $headers, $default)
    {
        if ($headers->first('x-forwarded-proto') === 'https'
            || (int)$headers->first('x-forwarded-port') === 443) {
            return 'https';
        }

        return $default;
    }

    public function extractRequest()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }

        $protocolParts = explode('/', $_SERVER['SERVER_PROTOCOL']);
        $hostParts = explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $headers = new HeaderBag($this->extractHeaders());

        $uriConfig = [
            'scheme' => $this->resolveSchema($headers, strtolower($protocolParts[0])),
            'query' => isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
            'path' => parse_url($requestUri, PHP_URL_PATH),
            'host' => $hostParts[0],
        ];

        if (isset($hostParts[1])) {
            $uriConfig['port'] = $hostParts[1];
        }

        $body = new Stream('php://memory', 'w+');
        $body->write(file_get_contents('php://input'));

        $config = [
            'protocol' => $protocolParts[1],
            'uri' => new Uri('', $uriConfig),
            'method' => strtoupper($_SERVER['REQUEST_METHOD']),
            'headers' => $headers,
            'cookies' => $_COOKIE,
            'body' => $body,
            'serverParams' => [
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ],
        ];

        if (!empty($_FILES)) {
            $config['files'] = $this->normalizeFiles($_FILES);
        }

        return app()->makeRequest($config);
    }

    protected function response(Response $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            foreach ($values as $value) {
                header($name . ': ' . $value, false, $response->statusCode);
            }
        }

        echo (string)$response->getBody();
    }

    public function run()
    {
        $app = $this->createApplication();

        $response = $app->handleRequest($this->extractRequest());

        $this->response($response);
    }
}
