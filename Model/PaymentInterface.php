<?php

namespace Payum\Be2Bill\Model;

use Payum\Core\Model\PaymentInterface as PayumPaymentInterface;

interface PaymentInterface extends PayumPaymentInterface
{
    const GENDER_MALE = 'm';
    const GENDER_FEMALE = 'f';

    /**
     * @return string
     */
    public function getClientGender();
}
