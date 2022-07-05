<?php

namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;

class CaptureOffsiteActionTest extends GenericActionTest
{
    protected $actionClass = CaptureOffsiteAction::class;

    protected $requestClass = Capture::class;

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CaptureOffsiteAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldRedirectToBe2billSiteIfExecCodeNotPresentInQuery()
    {
        $this->expectException(\Payum\Core\Reply\HttpPostRedirect::class);
        $model = [
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Gateway for digital stuff',
            'ORDERID' => 'orderId',
            'EXTRADATA' => '[]',
        ];

        $postArray = array_replace($model, [
            'HASH' => 'foobarbaz',
        ]);

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('prepareOffsitePayment')
            ->with($model)
            ->willReturn($postArray)
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(\Payum\Core\Request\GetHttpRequest::class))
        ;

        $action = new CaptureOffsiteAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture($model);

        $action->execute($request);
    }

    public function testShouldUpdateModelWhenComeBackFromBe2billSite()
    {
        $model = [
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Gateway for digital stuff',
            'ORDERID' => 'orderId',
        ];

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('prepareOffsitePayment')
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(\Payum\Core\Request\GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->query['EXECCODE'] = 1;
                $request->query['FOO'] = 'fooVal';
            })
        ;

        $action = new CaptureOffsiteAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture($model);

        $action->execute($request);

        $actualModel = $request->getModel();

        $this->assertArrayHasKey('EXECCODE', $actualModel);

        $this->assertArrayHasKey('FOO', $actualModel);
        $this->assertSame('fooVal', $actualModel['FOO']);

        $this->assertArrayHasKey('CLIENTIDENT', $actualModel);
        $this->assertSame($model['CLIENTIDENT'], $actualModel['CLIENTIDENT']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
