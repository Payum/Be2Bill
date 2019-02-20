<?php

namespace Request;

use Payum\Be2Bill\Api;

class Callback
{
    /**
     * @var bool
     */
    private $execCode;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @param bool $execCode
     * @param string $orderId
     * @param string $transactionId
     */
    public function __construct($execCode, $orderId, $transactionId)
    {
        $this->execCode = $execCode;
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
    }

    /**
     * @return bool
     */
    public function execCode()
    {
        return $this->execCode;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
         return $this->execCode === Api::EXECCODE_SUCCESSFUL;
    }
}
