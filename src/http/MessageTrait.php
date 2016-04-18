<?php
/**
 * @link https://github.com/bixuehujin/blink
 * @copyright Copyright (c) 2016 Jin Hu
 * @license the MIT License
 */

namespace blink\http;

use Psr\Http\Message\StreamInterface;

/**
 * Class MessageTrait
 *
 * @package blink\http
 */
trait MessageTrait
{
    /**
     * The protocol version.
     *
     * @var string
     */
    public $protocol = '1.1';

    /**
     * The message body stream.
     *
     * @var StreamInterface
     */
    private $_body;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        return $this->headers->get($name, []);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
        $headers = $this->headers->get($name, []);

        return implode(',', $headers);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->headers->set($name, $value);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;
        $new->headers->with($name, $value);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $new = clone $this;
        $new->headers->remove($name);

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->_body;
    }


    /**
     * Sets the body of the message.
     *
     * @param $body
     */
    public function setBody($body)
    {
        if ($body instanceof StreamInterface) {
            $this->_body = $body;
        } else {
            $this->_body = new Stream('php://memory', 'rw+');
        }
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->_body = $body;

        return $new;
    }
}
