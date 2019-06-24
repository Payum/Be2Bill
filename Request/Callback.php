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
    private $paymentNumber;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $message;

    /**
     * @param string $execCode
     * @param string $paymentNumber
     * @param string $transactionId
     * @param string $message
     */
    public function __construct($execCode, $paymentNumber, $transactionId, $message)
    {
        $this->execCode = $execCode;
        $this->paymentNumber = $paymentNumber;
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
    public function getPaymentNumber()
    {
        return $this->paymentNumber;
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

    /**
     * @return bool
     */
    public function isPending()
    {
         return $this->execCode === Api::EXECCODE_SDD_PENDING_PROCESSING;
    }
}
