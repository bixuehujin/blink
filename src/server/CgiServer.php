<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2015 Jin Hu
 * @license the MIT License
 */

namespace blink\server;

use blink\http\File;
use blink\http\Response;

/**
 * The CgiServer makes it possible to run Blink application upon php-fpm or Apache's mod_php.
 *
 * @package blink\server
 * @author Jin Hu <bixuehujin@gmail.com>
 * @since 0.1.1
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

    /**
     * Blink only support the 2-dimensions $_FILES structure, we have no plan to support recursively $_FILES parsing as
     * it is quite useless and makes our design complicated.
     *
     * @param $files
     * @return array
     */
    protected function normalizeFiles($files)
    {
        $results = [];

        foreach ($files as $class => $info) {
            if (is_array($info['name'])) {
                foreach ($info['name'] as $key => $_) {
                    $results[$class][$key] = new File([
                        'name' => $files[$class]['name'][$key],
                        'tmpName' => $files[$class]['tmp_name'][$key],
                        'size' => $files[$class]['size'][$key],
                        'type' => $files[$class]['type'][$key],
                        'error' => $files[$class]['error'][$key],
                    ]);
                }
            } else {
                $results[$class][] = new File($info);
            }
        }

        return $results;
    }

    protected function extractRequest()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        if ($requestUri !== '' && $requestUri[0] !== '/') {
            $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
        }

        $config = [
            'protocol' => $_SERVER['SERVER_PROTOCOL'],
            'method' => strtoupper($_SERVER['REQUEST_METHOD']),
            'path' => parse_url($requestUri, PHP_URL_PATH),
            'headers' => $this->extractHeaders(),
            'params' => $_GET,
            'cookies' => $_COOKIE,
            'content' => file_get_contents('php://input'),
        ];

        if (!empty($_FILES)) {
            $config['files'] = $this->normalizeFiles($_FILES);
        }

        return app()->makeRequest($config);
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
