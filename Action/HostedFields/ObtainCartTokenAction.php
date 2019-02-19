<?php

namespace Payum\Be2Bill\Action\HostedFields;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\Request\Api\ExecutePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Be2Bill\Request\Api\ObtainCartToken;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;

class ObtainCartTokenAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    /**
     * @var string
     */
    private $template;

    /**
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
        $this->apiClass = Api::class;
    }

    /**
     * @param mixed $request
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var $request ObtainCartToken */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['HFTOKEN']) {
            throw new \LogicException('The token has already been set.');
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);

        if ($getHttpRequest->method === 'POST' && isset($getHttpRequest->request['hfToken'])) {
            $model['HFTOKEN'] = $getHttpRequest->request['hfToken'];
            $model['CARDFULLNAME'] = $getHttpRequest->request['cardfullname'];

            $executePayment = new ExecutePayment(
                $request->getToken(),
                $getHttpRequest->request['cardType'],
                $getHttpRequest->request['execCode']
            );
            $executePayment->setModel($model);
            $this->gateway->execute($executePayment);

            return;
        }

        /** @var Api $api */
        $api = $this->api;
        $token = $request->getToken();
        $this->gateway->execute($renderTemplate = new RenderTemplate($this->template, [
            'credentials' => $api->getObtainJsTokenCredentials(),
            'actionUrl' => $token ? $token->getTargetUrl() : null,
        ]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request)
    {
        return
            $request instanceof ObtainCartToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
