<?php

namespace Payum\Be2Bill\Tests\Action\SDD;

use Payum\Be2Bill\Action\SDD\CaptureAction;
use Payum\Be2Bill\Action\SDD\ExecutePaymentAction;
use Payum\Be2Bill\Api;
use Payum\Be2Bill\Model\PaymentInterface as SDDPaymentInterface;
use Payum\Be2Bill\Request\SDD\ExecutePayment;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Tests\GenericActionTest;

class ExecutePaymentActionTest extends GenericActionTest
{
    protected $actionClass = ExecutePaymentAction::class;

    protected $requestClass = ExecutePayment::class;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new \ArrayObject(), '', '','','','','','','')),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ExecutePaymentAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(ExecutePaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
    * @test
    */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new ExecutePaymentAction();
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
        $action = new ExecutePaymentAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldResponseIfSetExeccodeNeedProcessIBAN()
    {
        $request = new ExecutePayment(new \ArrayObject([
            'CLIENTUSERAGENT' => 'CLIENTUSERAGENT',
            'CLIENTIP' => 'CLIENTIP',
        ]), 'firstName',  'lastName', 'address', 'city', 'country', 'phone', 'postalCode', 'clientGender');

        $result = new \StdClass();
        $result->EXECCODE = Api::EXECCODE_SDD_NEED_PROCESS_IBAN;
        $result->REDIRECTHTML = base64_encode('<html>REDIRECTHTML</html>');

        $api = $this->createApiMock();
        $api
            ->method('sddPayment')
            ->willReturn($result)
        ;
        $getaway = $this->createGatewayMock();

        $action = new ExecutePaymentAction();
        $action->setApi($api);
        $action->setGateway($getaway);

        try {
            $action->execute($request);
        } catch (HttpResponse $reply) {
            $this->assertSame(200, $reply->getStatusCode());
            $this->assertSame('<html>REDIRECTHTML</html>', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function shouldUpdateModelIfPaymentSuccess()
    {
        $request = new ExecutePayment([
            'CLIENTUSERAGENT' => 'CLIENTUSERAGENT',
            'CLIENTIP' => 'CLIENTIP',
        ], 'firstName',  'lastName', 'address', 'city', 'country', 'phone', 'postalCode', 'clientGender');

        $result = new \StdClass();
        $result->EXECCODE = null;
        $result->FOO = 'BAR';

        $api = $this->createApiMock();
        $api
            ->method('sddPayment')
            ->willReturn($result)
        ;
        $getaway = $this->createGatewayMock();

        $action = new ExecutePaymentAction();
        $action->setApi($api);
        $action->setGateway($getaway);

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertSame([
            'CLIENTUSERAGENT' => 'CLIENTUSERAGENT',
            'CLIENTIP' => 'CLIENTIP',
            'BILLINGFIRSTNAME' => 'firstName',
            'BILLINGLASTNAME' => 'lastName',
            'BILLINGADDRESS' => 'address',
            'BILLINGCITY' => 'city',
            'BILLINGCOUNTRY' => 'country',
            'BILLINGMOBILEPHONE' => 'phone',
            'BILLINGPOSTALCODE' => 'postalCode',
            'CLIENTGENDER' => 'clientGender',
            'EXECCODE' => null,
            'FOO' => 'BAR',
        ], $model);
    }
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
