<?php

namespace Payum\Be2Bill\Tests\Action\SDD;

use Payum\Be2Bill\Action\SDD\CaptureAction;
use Payum\Be2Bill\Action\SDD\ObtainSDDAction;
use Payum\Be2Bill\Api;
use Payum\Be2Bill\Request\SDD\ExecutePayment;
use Payum\Be2Bill\Request\SDD\ObtainSDDData;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Tests\GenericActionTest;

class ObtainSDDActionTest extends GenericActionTest
{
    protected $actionClass = ObtainSDDAction::class;

    protected $requestClass = ObtainSDDData::class;

    protected function setUp()
    {
        $this->action = new ObtainSDDAction('template');
    }

    public function couldBeConstructedWithoutAnyArguments()
    {
        //overwrite
    }

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new \ArrayObject())),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new $this->requestClass('foo')),
            array(new $this->requestClass(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainSDDAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
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

        $action = new ObtainSDDAction('template');
        $action->setGateway($gatewayMock);

        $request = new ObtainSDDData(new \Payum\Core\Bridge\Spl\ArrayObject(['EXECCODE' => 1]));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldReturnRenderTemplateResponseIfMethodNotPost()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->willReturnCallback(
                static function (GetHttpRequest $request) {
                    $request->method = 'GET';
                }
            )
        ;

        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->willReturnCallback(
                function (RenderTemplate $renderTemplate) {
                    $renderTemplate->setResult('content');
                    $this->assertSame('template', $renderTemplate->getTemplateName());
                    $parameters = $renderTemplate->getParameters();
                    $this->assertArrayHasKey('actionUrl', $parameters);
                    $this->assertNull($parameters['token']);
                    $this->assertArrayHasKey('token', $parameters);
                    $this->assertNull($parameters['token']);
                    $this->assertArrayHasKey('amount', $parameters);
                    $this->assertSame(1, $parameters['amount']);
                }
            )
        ;

        $action = new ObtainSDDAction('template');
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new ObtainSDDData(new \Payum\Core\Bridge\Spl\ArrayObject([
                'AMOUNT' => 100
            ])));
        } catch (HttpResponse $reply) {
            $this->assertSame(200, $reply->getStatusCode());
            $this->assertSame('content', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function shouldExecGatewayExecutePaymentIfRequestMethodPostAndSetAllRequiredRequestParameters()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->willReturnCallback(
                static function (GetHttpRequest $request) {
                    $request->method = 'POST';
                    $request->request = [
                        'BILLINGFIRSTNAME' => 'firstName',
                        'BILLINGLASTNAME' => 'lastName',
                        'BILLINGADDRESS' => 'address',
                        'BILLINGCITY' => 'city',
                        'BILLINGCOUNTRY' => 'country',
                        'BILLINGMOBILEPHONE' => 'mobilePhone',
                        'BILLINGPOSTALCODE' => 'postalCode',
                        'CLIENTGENDER' => 'gender',
                    ];
                }
            )
        ;

        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(ExecutePayment::class))
        ;

        $action = new ObtainSDDAction('template');
        $action->setGateway($gatewayMock);

        $action->execute(new ObtainSDDData(new \Payum\Core\Bridge\Spl\ArrayObject([
            'AMOUNT' => 1.0
        ])));

    }
}
