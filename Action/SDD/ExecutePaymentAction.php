<?php

namespace Payum\Be2Bill\Action\SDD;

use Payum\Be2Bill\Request\SDD\ExecutePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Api;

class ExecutePaymentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param ExecutePayment $request
     */
    public function execute($request)
    {
        /** @var ExecutePayment $request */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = new ArrayObject($request->getModel());

        if (!$model['CLIENTUSERAGENT']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());
            $model['CLIENTUSERAGENT'] = $httpRequest->userAgent;
        }

        if (!$model['CLIENTIP']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());
            $model['CLIENTIP'] = $httpRequest->clientIp;
        }

        $model['BILLINGFIRSTNAME'] = $request->getFirstName();
        $model['BILLINGLASTNAME'] = $request->getLastName();
        $model['BILLINGADDRESS'] = $request->getAddress();
        $model['BILLINGCITY'] = $request->getCity();
        $model['BILLINGCOUNTRY'] = $request->getCountry();
        $model['BILLINGMOBILEPHONE'] = $request->getPhone();
        $model['BILLINGPOSTALCODE'] = $request->getPostalCode();

        if ($request->getClientGender()) {
            $model['CLIENTGENDER'] = $request->getClientGender();
        }

        /** @var Api $api */
        $api = $this->api;
        $result = $api->sddPayment($model->toUnsafeArray());

        if ($result->EXECCODE === Api::EXECCODE_SDD_NEED_PROCESS_IBAN) {
            throw new HttpResponse(base64_decode($result->REDIRECTHTML));
        }

        $model->replace((array) $result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ExecutePayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
