<?php

namespace MD\Flavour\Controller;

class Result
{

    /**
     * Data returned from the controller.
     *
     * @var mixed
     */
    protected $data;

    /**
     * HTTP Status Code the controller responded with.
     *
     * @var integer
     */
    protected $httpCode = 200;

    /**
     * Constructor.
     *
     * @param mixed   $data     Data returned from the controller.
     * @param integer $httpCode HTTP Status Code the controller responded with. Default: `200`.
     */
    public function __construct($data, $httpCode = 200)
    {
        $this->data = $data;
        $this->httpCode = $httpCode;
    }

    /**
     * Returns the data returned from the controller.
     *
     * @return mixed
     */
    public function getData()
    {
        if (!isset($this->data)) {
            return [];
        }

        return $this->data;
    }

    /**
     * Sets the controller return data.
     *
     * @param mixed $data Controller return data.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Returns the HTTP Status Code the controller responded with.
     *
     * @return integer
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Sets the HTTP Status Code the controller responded with.
     *
     * @param integer $httpCode HTTP Status Code.
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }
}
