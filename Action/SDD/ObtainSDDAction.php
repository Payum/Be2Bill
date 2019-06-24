<?php

namespace Payum\Be2Bill\Action\SDD;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\Request\SDD\ObtainSDDData;
use Payum\Be2Bill\Request\SDD\ExecutePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Reply\HttpResponse;

class ObtainSDDAction implements ActionInterface, GatewayAwareInterface
{
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
    }

    /**
     * @param mixed $request
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var ObtainSDDData $request  */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['EXECCODE']) {
            return;
        }

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        $keyNames = [
            'BILLINGFIRSTNAME', 'BILLINGLASTNAME', 'BILLINGADDRESS',
            'BILLINGCITY', 'BILLINGCOUNTRY', 'BILLINGMOBILEPHONE', 'BILLINGPOSTALCODE','CLIENTGENDER'
        ];

        if ($getHttpRequest->method === 'POST' && $this->isIssetAllKeys($getHttpRequest->request, $keyNames)) {
            $executePayment = new ExecutePayment(
                $request->getToken(),
                $getHttpRequest->request['BILLINGFIRSTNAME'],
                $getHttpRequest->request['BILLINGLASTNAME'],
                $getHttpRequest->request['BILLINGADDRESS'],
                $getHttpRequest->request['BILLINGCITY'],
                $getHttpRequest->request['BILLINGCOUNTRY'],
                $getHttpRequest->request['BILLINGMOBILEPHONE'],
                $getHttpRequest->request['BILLINGPOSTALCODE'],
                $getHttpRequest->request['CLIENTGENDER']
            );
            $executePayment->setModel($model);
            $this->gateway->execute($executePayment);

            return;
        }

        $token = $request->getToken();
        $this->gateway->execute($renderTemplate = new RenderTemplate($this->template, [
            'actionUrl' => $token ? $token->getTargetUrl() : null,
            'token' => $token,
            'amount' => $model['AMOUNT'] / 100,
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
            $request instanceof ObtainSDDData &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    /**
     * @param array $array
     * @param array $keys
     * @return bool
     */
    private function isIssetAllKeys(array $array, array $keys)
    {
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return false;
            }
        }

        return true;
    }
}
