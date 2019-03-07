<?php

namespace Payum\Be2Bill\Request;

use Payum\Be2Bill\Api;

class ReturnFromPaymentSystem
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
     * @var string
     */
    private $secureStatus;

    /**
     * @var string
     */
    private $secureSignatureStatus;

    /**
     * @var string
     */
    private $secureGlobalStatus;

    /**
     * @var string
     */
    private $secureEnrollStatus;

    /**
     * @param bool $execCode
     * @param string $orderId
     * @param string $transactionId
     * @param string $message
     */
    public function __construct(
        $execCode,
        $orderId,
        $transactionId,
        $message,
        $secureStatus,
        $secureSignatureStatus,
        $secureGlobalStatus,
        $secureEnrollStatus
    ) {
        $this->execCode = $execCode;
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
        $this->message = $message;
        $this->secureStatus = $secureStatus;
        $this->secureSignatureStatus = $secureSignatureStatus;
        $this->secureGlobalStatus = $secureGlobalStatus;
        $this->secureEnrollStatus = $secureEnrollStatus;
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
     * @return string
     */
    public function getSecureStatus()
    {
        return $this->secureStatus;
    }

    /**
     * @return string
     */
    public function getSecureSignatureStatus()
    {
        return $this->secureSignatureStatus;
    }

    /**
     * @return string
     */
    public function getSecureGlobalStatus()
    {
        return $this->secureGlobalStatus;
    }

    /**
     * @return string
     */
    public function getSecureEnrollStatus()
    {
        return $this->secureEnrollStatus;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
         return $this->execCode === Api::EXECCODE_SUCCESSFUL;
    }
}
