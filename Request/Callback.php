<?php

namespace Payum\Be2Bill\Request;

use Payum\Be2Bill\Api;

class Callback
{
    /**
     * @var string
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
     * @var string
     */
    private $message;

    /**
     * @param bool $execCode
     * @param string $orderId
     * @param string $transactionId
     * @param string $message
     */
    public function __construct($execCode, $orderId, $transactionId, $message)
    {
        $this->execCode = $execCode;
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getExecCode()
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
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
         return $this->execCode === Api::EXECCODE_SUCCESSFUL;
    }
}
