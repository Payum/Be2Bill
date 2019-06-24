<?php

namespace Payum\Be2Bill\Tests\Action\SDD;

use Payum\Be2Bill\Action\SDD\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Tests\GenericActionTest;
use Payum\Be2Bill\Model\PaymentInterface as SDDPaymentInterface;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPaymentAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment(), 'array')),
            array(new $this->requestClass($this->createMock(PaymentInterface::class), 'array')),
            array(new $this->requestClass($this->createMock(SDDPaymentInterface::class), 'array')),
            array(new $this->requestClass(new Payment(), 'array', $this->createMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payment(), 'foobar')),
            array(new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')),
            array(new $this->requestClass($this->createMock(SDDPaymentInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertPaymentToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();

        $convert = new Convert($payment, 'array');
        //guard
        $this->assertTrue($action->supports($convert));

        $action->execute($convert);

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('AMOUNT', $details);
        $this->assertEquals(123, $details['AMOUNT']);

        $this->assertArrayHasKey('ORDERID', $details);
        $this->assertEquals('theNumber', $details['ORDERID']);

        $this->assertArrayHasKey('DESCRIPTION', $details);
        $this->assertEquals('the description', $details['DESCRIPTION']);

        $this->assertArrayHasKey('CLIENTIDENT', $details);
        $this->assertEquals('theClientId', $details['CLIENTIDENT']);

        $this->assertArrayHasKey('CLIENTEMAIL', $details);
        $this->assertEquals('theClientEmail', $details['CLIENTEMAIL']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertCustomPaymentToDetailsAndSetItBack()
    {
        $ssdPayment = $this->createMock(SDDPaymentInterface::class);
        $ssdPayment->method('getNumber')->willReturn('theNumber');
        $ssdPayment->method('getCurrencyCode')->willReturn('USD');
        $ssdPayment->method('getTotalAmount')->willReturn(123);
        $ssdPayment->method('getDescription')->willReturn('the description');
        $ssdPayment->method('getClientId')->willReturn('theClientId');
        $ssdPayment->method('getClientEmail')->willReturn('theClientEmail');
        $ssdPayment->method('getClientGender')->willReturn('theClientGender');
        $ssdPayment->method('getDetails')->willReturn([]);

        $action = new ConvertPaymentAction();

        $convert = new Convert($ssdPayment, 'array');
        //guard
        $this->assertTrue($action->supports($convert));

        $action->execute($convert);

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('AMOUNT', $details);
        $this->assertEquals(123, $details['AMOUNT']);

        $this->assertArrayHasKey('ORDERID', $details);
        $this->assertEquals('theNumber', $details['ORDERID']);

        $this->assertArrayHasKey('DESCRIPTION', $details);
        $this->assertEquals('the description', $details['DESCRIPTION']);

        $this->assertArrayHasKey('CLIENTIDENT', $details);
        $this->assertEquals('theClientId', $details['CLIENTIDENT']);

        $this->assertArrayHasKey('CLIENTEMAIL', $details);
        $this->assertEquals('theClientEmail', $details['CLIENTEMAIL']);

        $this->assertArrayHasKey('CLIENTGENDER', $details);
        $this->assertEquals('theClientGender', $details['CLIENTGENDER']);
    }
}
