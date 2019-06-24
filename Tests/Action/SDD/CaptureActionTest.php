<?php

namespace Payum\Be2Bill\Tests\Action\SDD;

use Payum\Be2Bill\Action\SDD\CaptureAction;
use Payum\Be2Bill\Api;
use Payum\Be2Bill\Request\SDD\ObtainSDDData;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new CaptureAction();
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }


    /**
     * @test
     */
    public function shouldDoNothingIfExeccodeSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute');

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('payment');

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(['EXECCODE' => 1]);

        $action->execute($request);
    }


    /**
     * @test
     */
    public function shouldBeCallExecuteObtainSDDDate()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ObtainSDDData::class));

        $apiMock = $this->createApiMock();

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'AMOUNT' => 10,
        ]);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
