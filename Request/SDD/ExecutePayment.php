<?php

namespace Payum\Be2Bill\Request\SDD;

use Payum\Core\Request\Generic;

class ExecutePayment extends Generic
{
    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $clientGender;

    /**
     * @param mixed $model
     * @param string $firstName
     * @param string $lastName
     * @param string $address
     * @param string $city
     * @param string $country
     * @param string $phone
     * @param string $postalCode
     * @param string $clientGender
     */
    public function __construct($model, $firstName, $lastName, $address, $city, $country, $phone, $postalCode, $clientGender)
    {
        parent::__construct($model);

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->phone = $phone;
        $this->postalCode = $postalCode;
        $this->clientGender = $clientGender;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return string
     */
    public function getClientGender()
    {
        return $this->clientGender;
    }
}
