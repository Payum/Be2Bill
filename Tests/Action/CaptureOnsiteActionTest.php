<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\PaymentInterface;
use Payum\Core\Request\CaptureRequest;
use Payum\Be2Bill\Action\CaptureOnsiteAction;
use Payum\Core\Request\PostRedirectUrlInteractiveRequest;

class CaptureOnsiteActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\CaptureOnsiteAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\CaptureOnsiteAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureOnsiteAction();
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAccessAsModelWhichContainsCARDCODE()
    {
        $action = new CaptureOnsiteAction();

        $model = new \ArrayObject(array(
            'CARDCODE' => '1234432112344321',
        ));

        $request = new CaptureRequest($model);

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAccessAsModelWhichNotContainsCARDCODE()
    {
        $action = new CaptureOnsiteAction();

        $model = new \ArrayObject(array());

        $request = new CaptureRequest($model);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureRequest()
    {
        $action = new CaptureOnsiteAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestAndNotArrayAsModel()
    {
        $action = new CaptureOnsiteAction();

        $request = new CaptureRequest(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureOnsiteAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new CaptureOnsiteAction();
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
        $action = new CaptureOnsiteAction();

        $action->setApi(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldRedirectToBe2billSiteIfExecCodeNotPresentInQuery()
    {
        $model = array(
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Payment for digital stuff',
            'ORDERID' => 'orderId',
        );

        $postArray = array_replace($model, array(
            'HASH' => 'foobarbaz',
        ));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('prepareOnsitePayment')
            ->with($model)
            ->will($this->returnValue($postArray))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpQueryRequest'))
        ;

        $action = new CaptureOnsiteAction();
        $action->setApi($apiMock);
        $action->setPayment($paymentMock);

        $request = new CaptureRequest($model);

        try {
            $action->execute($request);

            $this->fail('Interactive request is expected to be thrown.');
        } catch (PostRedirectUrlInteractiveRequest $interactiveRequest) {
            $this->assertAttributeEquals($postArray, 'post', $interactiveRequest);
        }
    }

    /**
     * @test
     */
    public function shouldUpdateModelWhenComeBackFromBe2billSite()
    {
        $model = array(
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Payment for digital stuff',
            'ORDERID' => 'orderId',
        );

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('prepareOnsitePayment')
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpQueryRequest'))
            ->will($this->returnCallback(function($request) {
                $request['EXECCODE'] = 1;
                $request['FOO'] = 'fooVal';
            }))
        ;

        $action = new CaptureOnsiteAction();
        $action->setApi($apiMock);
        $action->setPayment($paymentMock);

        $request = new CaptureRequest($model);

        $action->execute($request);

        $actualModel = $request->getModel();

        $this->assertTrue(isset($actualModel['EXECCODE']));

        $this->assertTrue(isset($actualModel['FOO']));
        $this->assertEquals('fooVal', $actualModel['FOO']);

        $this->assertTrue(isset($actualModel['CLIENTIDENT']));
        $this->assertEquals($model['CLIENTIDENT'], $actualModel['CLIENTIDENT']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Be2Bill\Api', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
