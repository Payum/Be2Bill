<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Api;
use Payum\Core\Security\SensitiveValue;

class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (null !== $model['EXECCODE']) {
            return;
        }

        if (false == $model['CLIENTUSERAGENT']) {
            $this->payment->execute($httpRequest = new GetHttpRequest());
            $model['CLIENTUSERAGENT'] = $httpRequest->userAgent;
        }
        if (false == $model['CLIENTIP']) {
            $this->payment->execute($httpRequest = new GetHttpRequest());
            $model['CLIENTIP'] = $httpRequest->clientIp;
        }

        $cardFields = array('CARDCODE', 'CARDCVV', 'CARDVALIDITYDATE', 'CARDFULLNAME');
        if (false == $model->validateNotEmpty($cardFields, false) && false == $model['ALIAS']) {
            try {
                $this->payment->execute($creditCardRequest = new ObtainCreditCard());
                $card = $creditCardRequest->obtain();

                $model['CARDVALIDITYDATE'] = new SensitiveValue($card->getExpireAt()->format('m-y'));
                $model['CARDCODE'] = $card->getNumber();
                $model['CARDFULLNAME'] = $card->getHolder();
                $model['CARDCVV'] = $card->getSecurityCode();
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
            }
        }

        //instruction must have an alias set (e.g oneclick payment) or credit card info.
        if (false == ($model['ALIAS'] || $model->validateNotEmpty($cardFields, false))) {
            throw new LogicException('Either credit card fields or its alias has to be set.');
        }

        $response = $this->api->payment($model->toUnsafeArray());

        $model->replace((array) $response->getContentJson());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
