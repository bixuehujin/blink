<?php

namespace blink\tests\testing;

use blink\http\Request;
use blink\http\Response;
use blink\testing\TestCase;
use blink\core\Application;

/**
 * Class ActorTest
 * @package blink\tests\testing
 */
class ActorTest extends TestCase
{
    public function createApplication()
    {
        $application = new Application(['root' => '.']);
        $application
            ->route('POST', '/files', function (Request $request, Response $response) {
                $response->headers->with('Content-Type', 'application/json');
                $file = $request->files->first('foo');

                return [
                    'name' => $file->name,
                    'size' => $file->size
                ];
            })
            ->bootstrap();

        return $this->app = $application;
    }

    public function testUploadFile()
    {
        $this->actor()
            ->withFiles(['foo' => __FILE__])
            ->post('/files')
            ->seeJsonStructure(['name', 'size']);
    }
}

