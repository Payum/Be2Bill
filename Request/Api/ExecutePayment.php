<?php

namespace Payum\Be2Bill\Request\Api;

use Payum\Core\Request\Generic;

class ExecutePayment extends Generic
{
    /**
     * @var string
     */
    private $cardType;

    /**
     * @var string
     */
    private $execCode;

    /**
     * @param mixed $model
     * @param string $cardType
     * @param string $execCode
     */
    public function __construct($model, $cardType, $execCode)
    {
        parent::__construct($model);
        $this->cardType = $cardType;
        $this->execCode = $execCode;
    }

    /**
     * @return mixed
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @return mixed
     */
    public function getExecCode()
    {
        return $this->execCode;
    }
}
