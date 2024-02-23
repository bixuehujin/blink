<?php

namespace blink\routing\events;

use blink\http\Request;

class RequestRouting
{
    public function __construct(public Request $request) {
    }
}
