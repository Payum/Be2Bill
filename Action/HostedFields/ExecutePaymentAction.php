<?php

namespace Payum\Be2Bill\Action\HostedFields;

use Payum\Be2Bill\Request\Api\ExecutePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
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
     * @param Capture $request
     */
    public function execute($request)
    {
        /** @var ExecutePayment $request */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = new ArrayObject($request->getModel());
        $model->validateNotEmpty(['HFTOKEN']);

        if ($request->getExecCode() === Api::EXECCODE_3DSECURE_IDENTIFICATION_REQUIRED) {
            throw new HttpResponse(base64_decode($model['3DSECUREHTML']), 302);
        }

        // Unsuccess
        if ($request->getExecCode() !== Api::EXECCODE_SUCCESSFUL) {
            return;
        }

        if (!$model['CLIENTUSERAGENT']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());
            $model['CLIENTUSERAGENT'] = $httpRequest->userAgent;
        }

        if (!$model['CLIENTIP']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());
            $model['CLIENTIP'] = $httpRequest->clientIp;
        }

        /** @var Api $api */
        $api = $this->api;
        $result = $api->hostedFieldsPayment($model->toUnsafeArray(), $request->getCardType());

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
