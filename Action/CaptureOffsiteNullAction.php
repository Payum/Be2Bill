<?php
namespace Payum\Be2Bill\Action;

use League\Url\Url;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;

class CaptureOffsiteNullAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        //we are back from be2bill site so we have to just update model.
        if (empty($httpRequest->query['EXTRADATA'])) {
            throw new HttpResponse('The capture is invalid. Code Be2Bell1', 400);
        }

        $extraDataJson = $httpRequest->query['EXTRADATA'];
        if (false == $extraData = json_decode($extraDataJson, true)) {
            throw new HttpResponse('The capture is invalid. Code Be2Bell2', 400);
        }

        if (empty($extraData['capture_token'])) {
            throw new HttpResponse('The capture is invalid. Code Be2Bell3', 400);
        }

        $this->gateway->execute($getToken = new GetToken($extraData['capture_token']));

        $url = Url::createFromUrl($getToken->getToken()->getTargetUrl());
        $url->setQuery($httpRequest->query);

        throw new HttpRedirect((string) $url);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            null === $request->getModel()
        ;
    }
}
