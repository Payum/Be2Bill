<?php

namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\Be2BillHostedFieldsGatewayFactory;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;

class Be2BillHostedFieldsGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory()
    {
        $rc = new \ReflectionClass(Be2BillHostedFieldsGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Be2BillHostedFieldsGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory();

        $this->assertAttributeInstanceOf(CoreGatewayFactory::class, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $factory = new Be2BillHostedFieldsGatewayFactory(array(), $coreGatewayFactory);

        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory();

        $gateway = $factory->create(array('payum.api' => new \stdClass()));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(array(// Merchant name
            'identifier' => '',
            'amex_identifier' => '',
            //Public APIKEYID
            'apikeyid' => '',
            // APIKEY
            'password' => '',
            'secret' => '',
            'amex_secret' => '',
            'sandbox' => true,
            'force_3d_secure' => false,
        ), $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('dalenys_hosted_fields', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Be2Bill Hosted Fields', $config['payum.factory_title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The identifier, password, apikeyid, secret, amex_identifier, amex_secret fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new Be2BillHostedFieldsGatewayFactory();

        $factory->create();
    }
}
