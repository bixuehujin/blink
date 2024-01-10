<?php

namespace blink\tests\testing;

use blink\di\Container;
use blink\eventbus\EventBus;
use blink\http\Request;
use blink\http\Response;
use blink\routing\Router;
use blink\testing\TestCase;

/**
 * Class ActorTest
 *
 * @package blink\tests\testing
 */
class ActorTest extends TestCase
{
    public function createApplication(): Router
    {
        $bus = new EventBus();
        $app = new Router($bus);
        $app->setContainer(new Container());
        $app->post('/files', function (Request $request, Response $response) {
            $response->headers->with('Content-Type', 'application/json');
            $file = $request->files->first('foo');

            return $response->with([
                'name' => $file->name,
                'size' => $file->size
            ]);
        });

        $app->get('/', function (Request $request, Response $response) {
            return $response->with('Hello, Blink!');
        });

        $app->get('/json', function (Request $request, Response $response) {
            return $response->with([
                'name' => 'Blink',
                'ext'  => 'swoole',
                'dev'  => 'test'
            ]);
        });

        $app->get('/json_contains', function (Request $request, Response $response) {
            return $response->with([
                'status' => 'ok',
                'data'   => [
                    "name" => 'blink',
                    "ext"  => 'swoole'
                ]
            ]);
        });

        return $this->app = $app;
    }

    public function testJson()
    {
        $this->actor()
            ->get('/json')
            ->seeJsonEquals([
                'ext'  => 'swoole',
                'dev'  => 'test',
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
