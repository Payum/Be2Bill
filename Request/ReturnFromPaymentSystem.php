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
     * @param string $paymentNumber
     * @param string $transactionId
     * @param string $message
     * @param string $secureStatus
     * @param string $secureSignatureStatus
     * @param string $secureGlobalStatus
     * @param string $secureEnrollStatus
     */
    public function __construct(
        $execCode,
        $paymentNumber,
        $transactionId,
        $message,
        $secureStatus,
        $secureSignatureStatus,
        $secureGlobalStatus,
        $secureEnrollStatus
    ) {
        $this->execCode = $execCode;
        $this->paymentNumber = $paymentNumber;
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
         return ($this->execCode === Api::EXECCODE_SUCCESSFUL) || ($this->execCode === Api::EXECCODE_SDD_PENDING_PROCESSING);
    }
}
