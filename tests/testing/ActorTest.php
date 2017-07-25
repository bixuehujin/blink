<?php

namespace blink\tests\testing;

use blink\http\Request;
use blink\http\Response;
use blink\testing\TestCase;
use blink\core\Application;

/**
 * Class ActorTest
 *
 * @package blink\tests\testing
 */
class ActorTest extends TestCase
{
    public function createApplication()
    {
        $application = new Application(['root' => '.']);
        $application->route('POST', '/files', function (Request $request, Response $response) {
            $response->headers->with('Content-Type', 'application/json');
            $file = $request->files->first('foo');

            return [
                'name' => $file->name,
                'size' => $file->size
            ];
        })
                    ->route('GET', '/', function (Request $request, Response $response) {
                        return 'Hello, Blink!';
                    })
                    ->route('GET', '/json', function (Request $request, Response $response) {
                        return [
                            'name' => 'Blink',
                            'ext' => 'swoole',
                            'dev' => 'test'
                        ];
                    })
                    ->route('GET', '/json_contains', function (Request $request, Response $response) {
                        return [
                            'status' => 'ok',
                            'data' => [
                                "name" => 'blink',
                                "ext" => 'swoole'
                            ]
                        ];
                    })
                    ->bootstrapIfNeeded();

        return $this->app = $application;
    }

    public function testJson()
    {
        $this->actor()
             ->get('/json')
             ->seeJsonEquals([
                 'ext' => 'swoole',
                 'dev' => 'test',
                 'name' => 'Blink',
             ]);
    }

    public function testJsonContains()
    {
        $this->actor()
             ->get('/json_contains')
             ->seeJson(['ext' => 'swoole']);
    }

    public function testContent()
    {
        $this->actor()
             ->get('/')
             ->seeContent('Hello, Blink!');
    }

    public function testUploadFile()
    {
        $this->actor()
             ->withFiles(['foo' => __FILE__])
             ->post('/files')
             ->seeJsonStructure(['name', 'size']);
    }
}
