<?php

namespace blink\testing;

use blink\core\Application;

/**
 * Class RequestActor
 *
 * @package blink\testing
 * @since 0.3.0
 */
class RequestActor
{
    protected $phpunit;
    protected $app;

    /**
     * @var \blink\http\Request
     */
    protected $request;
    /**
     * @var \blink\http\Response
     */
    protected $response;

    public function __construct(\PHPUnit_Framework_TestCase $phpunit, Application $app)
    {
        $this->phpunit = $phpunit;
        $this->app = $app;

        $this->request = $app->makeRequest();
    }


    protected function doRequest($method, $uri, $query = '', $cookies = [], $files = [], $headers = [], $content = null)
    {
        $this->request->headers->add($headers);

        if ($this->isJsonRequest() && is_array($content)) {
            $content = json_encode($content);
        }

        $config = [
            'method' => $method,
            'path' => $uri,
            'queryString' => is_array($query) ? http_build_query($query, '', '&') : $query,
            'cookies' => $cookies,
            'content' => $content,
        ];

        foreach ($config as $key => $value) {
            $this->request->{$key} = $value;
        }

        $this->response = $this->app->handleRequest($this->request);

        return $this;
    }

    protected function isJsonRequest()
    {
        $headers = $this->request->headers->get('Content-Type', []);

        foreach ($headers as $header) {
            if (strpos($header, 'application/json') === 0) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeUri($uri)
    {
        if (($pos = strpos($uri, '?')) !== false) {
            return [substr($uri, 0, $pos), substr($uri, $pos + 1)];
        }

        return [$uri, ''];
    }

    public function accepts($accepts)
    {
        return $this->withHeaders(['Accept' => $accepts]);
    }

    public function withHeaders(array $headers = [])
    {
        $this->request->headers->add($headers);

        return $this;
    }

    public function withJson()
    {
        $this->withHeaders(['Content-Type' => 'application/json']);

        return $this;
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return $this
     */
    public function get($uri, array $headers = [])
    {
        list($uri, $query) = $this->normalizeUri($uri);

        return $this->doRequest('GET', $uri, $query, [], [], $headers, '');
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return $this
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        list($uri, $query) = $this->normalizeUri($uri);

        return $this->doRequest('POST', $uri, $query, [], [], $headers, $data);
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return $this
     */
    public function put($uri, array $data = [], array $headers = [])
    {
        list($uri, $query) = $this->normalizeUri($uri);

        return $this->doRequest('PUT', $uri, $query, [], [], $headers, $data);
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return $this
     */
    public function patch($uri, array $data = [], array $headers = [])
    {
        list($uri, $query) = $this->normalizeUri($uri);

        return $this->doRequest('PATCH', $uri, $query, [], [], $headers, $data);
    }

    /**
     * Visit the given URI with a DELETE request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return $this
     */
    public function delete($uri, array $data = [], array $headers = [])
    {
        list($uri, $query) = $this->normalizeUri($uri);

        return $this->doRequest('DELETE', $uri, $query, [], [], $headers, $data);
    }

    /**
     * Assert that the response contains JSON.
     *
     * @param  array|null  $data
     * @param  bool  $negate
     * @return $this
     */
    public function seeJson(array $data = null, $negate = false)
    {
        if (is_null($data)) {
            $this->phpunit->assertJson(
                $this->response->content(), "Failed asserting that JSON returned [{$this->request->path}]."
            );

            return $this;
        }

        return $this->seeJsonContains($data, $negate);
    }

    /**
     * Assert that the response contains the given JSON.
     *
     * @param  array  $data
     * @param  bool  $negate
     * @return $this
     */
    protected function seeJsonContains(array $data, $negate = false)
    {
        $method = $negate ? 'assertFalse' : 'assertTrue';

        $actual = json_encode(array_sort_recursive(
            json_decode($this->response->content(), true)
        ));

        foreach (array_sort_recursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);

            $this->{$method}(
                strpos($actual, $expected) !== false,
                ($negate ? 'Found unexpected' : 'Unable to find')." JSON fragment [{$expected}] within [{$actual}]."
            );
        }

        return $this;
    }

    protected function formatToExpectedJson($key, $value)
    {
        $expected = json_encode([$key => $value]);

        if ($expected[0] == '{') {
            $expected = substr($expected, 1);
        }

        if ($expected[strlen($expected) - 1] == '}') {
            $expected = substr($expected, 0, -1);
        }

        return $expected;
    }

    /**
     * Asserts that the status code of the response matches the given code.
     *
     * @param  int  $status
     * @return $this
     */
    public function seeStatusCode($status)
    {
        $this->phpunit->assertEquals($status, $this->response->statusCode);

        return $this;
    }

    /**
     * Assert that the response doesn't contain JSON.
     *
     * @param  array|null  $data
     * @return $this
     */
    public function dontSeeJson(array $data = null)
    {
        return $this->seeJson($data, true);
    }


    /**
     * Assert that the response contains an exact JSON array.
     *
     * @param  array  $data
     * @return $this
     */
    public function seeJsonEquals(array $data)
    {
        $actual = json_encode(array_sort_recursive(
            json_decode($this->response->content(), true)
        ));

        $this->phpunit->assertEquals(json_encode(array_sort_recursive($data)), $actual);

        return $this;
    }

    /**
     * Assert that the JSON response has a given structure.
     *
     * @param  array|null  $structure
     * @param  array|null  $responseData
     * @return $this
     */
    public function seeJsonStructure(array $structure = null, $responseData = null)
    {
        if (is_null($structure)) {
            return $this->seeJson();
        }

        if (!$responseData) {
            $responseData = json_decode($this->response->content(), true);
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                $this->phpunit->assertInternalType('array', $responseData);
                foreach ($responseData as $responseDataItem) {
                    $this->seeJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                $this->phpunit->assertArrayHasKey($key, $responseData);
                $this->seeJsonStructure($structure[$key], $responseData[$key]);
            } else {
                $this->phpunit->assertArrayHasKey($value, $responseData);
            }
        }

        return $this;
    }


    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param  string $name
     * @param  mixed $value
     * @return $this
     */
    public function seeHeader($name, $value = null)
    {
        $headers = $this->response->headers;

        $this->phpunit->assertTrue($headers->has($name), "Header [{$name}] not present on response.");

        if (!is_null($value)) {
            $values = $headers->get($name);
            $strValues = implode(', ', $values);

            $this->phpunit->assertTrue(
                in_array($value, $values),
                "Header [{$name}] was found, but value [{$strValues}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string $name
     * @param  mixed $value
     * @return $this
     */
    public function seeCookie($name, $value = null)
    {
        $cookie = $this->response->cookies->get($name);

        $this->phpunit->assertTrue((boolean)$cookie, "Cookie [{$cookie}] not present on response.");

        if ($cookie && !is_null($value)) {
            $this->phpunit->assertEquals(
                $cookie->value, $value,
                "Cookie [{$name}] was found, but value [{$cookie->value}] does not match [{$value}]."
            );
        }

        return $this;
    }
}
