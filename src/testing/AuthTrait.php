<?php

namespace blink\testing;

use blink\auth\Authenticatable;
use blink\core\InvalidParamException;
use blink\core\InvalidValueException;

/**
 * A trait providing methods to make tests easier for authenticated operations.
 *
 * @package blink\src\testing
 */
trait AuthTrait
{

    /**
     * The current session id.
     *
     * @var string
     */
    private $sessionId;

    /**
     * Set the currently logged in user to identifier.
     *
     * @param $identifier
     * @return static
     * @throws InvalidParamException
     * @throws InvalidValueException
     */
    public function actingAs($identifier)
    {
        if (!$identifier instanceof Authenticatable) {
            $class = $this->app->auth->model;
            $identifier = $class::findIdentity($identifier);
        }

        if (!$identifier instanceof Authenticatable) {
            throw new InvalidParamException('The "identifier" parameter is valid ');
        }

        $this->app->auth->login($identifier);

        $this->sessionId = request()->session->id;

        if (!$this->sessionId) {
            throw new InvalidValueException('Session id is not generated successfully');
        }

        return $this;
    }

    /**
     * Returns the currently logged in user.
     *
     * @return Authenticatable|null
     */
    public function actor()
    {
        if (!$this->sessionId) {
            return;
        }

        return $this->app->auth->who($this->sessionId);
    }

    /**
     * Sets the currently logged user credential to request.
     *
     * @return static
     * @throws InvalidValueException
     */
    public function withActorCredential()
    {
        if (!$this->sessionId) {
            throw new InvalidValueException('Session id is not generated successfully');
        }

        $this->app->request->headers->with('X-Session-Id', $this->sessionId);

        return $this;
    }
}
