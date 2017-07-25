<?php

namespace blink\testing;

use blink\core\Application;
use blink\core\InvalidParamException;
use blink\http\File;
use blink\http\HeaderBag;

/**
 * Class RequestActor
 *
 * @package blink\testing
 * @since   0.3.0
 */
class RequestActor
{
    use AuthTrait;

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

        if ($this->isJsonMessage($this->request->headers) && is_array($content)) {
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

    protected function isJsonMessage(HeaderBag $headers)
    {
        $headers = $headers->get('Content-Type', []);

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
     * Send the request with specified files.
     *
     * @param array $files
     * @return $this
     */
    public function withFiles(array $files)
    {
        foreach ($files as $key => $file) {
            if (!is_file($file)) {
                throw new InvalidParamException(sprintf("The file: '$file' does not exists."));
            }

            $target = new File();
            $target->name = basename($file);
            $target->size = filesize($file);
            $target->type = (new \finfo())->file($file, FILEINFO_MIME_TYPE);
            $target->tmpName = $file;
            $target->error = UPLOAD_ERR_OK;

            $this->request->files->set($key, $target);
        }

        return $this;
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param  string $uri
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
     * @param  string $uri
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
     * @param  string $uri
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
     * @param  string $uri
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
     * @param  string $uri
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
     * @param  array|null $data
     * @param  bool       $negate
     * @return $this
     */
    public function seeJson(array $data = null, $negate = false)
    {
        if (is_null($data)) {
            $this->phpunit->assertJson($this->response->content(),
                "Failed asserting that JSON returned [{$this->request->path}].");

            return $this;
        }

        return $this->seeJsonContains($data, $negate);
    }

    /**
     * Assert that the response contains the given JSON.
     *
     * @param  array $data
     * @param  bool  $negate
     * @return $this
     */
    protected function seeJsonContains(array $data, $negate = false)
    {
        $method = $negate ? 'assertFalse' : 'assertTrue';

        $actual = json_encode($this->sortRecursive(json_decode($this->response->content(), true)));

        foreach ($this->sortRecursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);

            $this->phpunit->{$method}(strpos($actual, $expected) !== false,
                ($negate ? 'Found unexpected' : 'Unable to find') . " JSON fragment [{$expected}] within [{$actual}].");
        }

        return $this;
    }

    protected function formatToExpectedJson($key, $value)
    {
        $expected = json_encode([$key => $value]);

        if ($expected[0] === '{') {
            $expected = substr($expected, 1);
        }

        if ($expected[strlen($expected) - 1] === '}') {
            $expected = substr($expected, 0, -1);
        }

        return $expected;
    }

    /**
     * Asserts that the status code of the response matches the given code.
     *
     * @param  int $status
     * @return $this
     */
    public function seeStatusCode($status)
    {
        $this->phpunit->assertEquals($status, $this->response->statusCode);

        return $this;
    }

    /**
     * Asserts that the content of the response matches the given content.
     *
     * @param string $content
     * @return $this
     */
    public function seeContent($content)
    {
        $this->phpunit->assertEquals($content, $this->response->content());

        return $this;
    }

    /**
     * Assert that the response doesn't contain JSON.
     *
     * @param  array|null $data
     * @return $this
     */
    public function dontSeeJson(array $data = null)
    {
        return $this->seeJson($data, true);
    }


    /**
     * Assert that the response contains an exact JSON array.
     *
     * @param  array $data
     * @return $this
     */
    public function seeJsonEquals(array $data)
    {
        $actual = json_encode($this->sortRecursive(json_decode($this->response->content(), true)));

        $this->phpunit->assertEquals(json_encode($this->sortRecursive($data)), $actual);

        return $this;
    }

    /**
     * Assert that the JSON response has a given structure.
     *
     * @param  array|null $structure
     * @param  array|null $responseData
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
     * @param  mixed  $value
     * @return $this
     */
    public function seeHeader($name, $value = null)
    {
        $headers = $this->response->headers;

        $this->phpunit->assertTrue($headers->has($name), "Header [{$name}] not present on response.");

        if (!is_null($value)) {
            $values = $headers->get($name);
            $strValues = implode(', ', $values);

            $this->phpunit->assertTrue(in_array($value, $values, true),
                "Header [{$name}] was found, but value [{$strValues}] does not match [{$value}].");
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return $this
     */
    public function seeCookie($name, $value = null)
    {
        $cookie = $this->response->cookies->get($name);

        $this->phpunit->assertTrue((boolean)$cookie, "Cookie [{$cookie}] not present on response.");

        if ($cookie && !is_null($value)) {
            $this->phpunit->assertEquals($cookie->value, $value,
                "Cookie [{$name}] was found, but value [{$cookie->value}] does not match [{$value}].");
        }

        return $this;
    }

    private $_verbose;

    protected function isVerbose()
    {
        if ($this->_verbose !== null) {
            return $this->_verbose;
        }

        $verbose = false;

        global $argv;

        foreach ($argv as $arg) {
            if (preg_match('/^\-{1,2}v(erbose)?$/', $arg)) {
                $verbose = true;
                break;
            }
        }

        return $this->_verbose = $verbose;
    }

    /**
     * Dump the response headers for debug purpose.
     *
     * @return $this
     */
    public function dumpHeaders()
    {
        $headers = $this->response->headers;

        if ($headers->count() && $this->isVerbose()) {
            $class = debug_backtrace()[1];

            printf("\ndump headers in %s::%s():\n", $class['class'], $class['function']);

            foreach ($headers as $key => $values) {
                echo $key, ': ', implode(',', $values), "\n";
            }
        }

        return $this;
    }

    /**
     * Dump the response cookies for debug purpose.
     *
     * @return $this
     */
    public function dumpCookies()
    {
        $cookies = $this->response->cookies;

        if ($cookies->count() && $this->isVerbose()) {
            $class = debug_backtrace()[1];

            printf("\ndump cookies in %s::%s():\n", $class['class'], $class['function']);

            foreach ($cookies as $key => $value) {
                echo $key, '=> ', $value, "\n";
            }
        }

        return $this;
    }

    /**
     * Dump the response json for debug purpose.
     *
     * @return $this
     */
    public function dumpJson()
    {
        if ($this->isVerbose()) {
            $json = $this->asJson();

            $class = debug_backtrace()[1];

            printf("\ndump json in %s::%s():\n", $class['class'], $class['function']);
            var_export($json);
        }

        return $this;
    }

    /**
     * Returns the response as json.
     *
     * @param boolean $asArray Converts object to associative arrays, defaults to true.
     * @return mixed
     */
    public function asJson($asArray = true)
    {
        if (!$this->isJsonMessage($this->response->headers)) {
            throw new \RuntimeException('The response is not a valid json response');
        }

        return json_decode($this->response->content(), $asArray);
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param array $array
     * @return array
     */
    private function sortRecursive(array $array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->sortRecursive($value);
            }
        }

        if ($this->isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    /**
     * Determines if an array is associative.
     *
     * @param array $array
     * @return bool
     */
    private function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }
}
