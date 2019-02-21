<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\ConvertPaymentAction;
use Payum\Be2Bill\Action\HostedFields\CaptureAction;
use Payum\Be2Bill\Action\HostedFields\ExecutePaymentAction;
use Payum\Be2Bill\Action\HostedFields\ObtainCartTokenAction;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class Be2BillHostedFieldsGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'dalenys_hosted_fields',
            'payum.factory_title' => 'Be2Bill Hosted Fields',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.execute_payment' => new ExecutePaymentAction(),
            'payum.action.obtain_cart_token' => function (ArrayObject $config) {
                return new ObtainCartTokenAction($config['payum.template.obtain_cart_token']);
            },
            'payum.template.obtain_cart_token' => '@PayumBe2Bill/Action/obtain_cart_token.html.twig',
        ]);

        $paths = $config['payum.paths'];
        $paths['PayumBe2Bill'] = realpath(__DIR__ . '/Resources/views');
        $config->offsetSet('payum.paths', $paths);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                // Merchant name
                'identifier' => '',
                'amex_identifier' => '',
                //Public APIKEYID
                'apikeyid' => '',
                // APIKEY
                'password' => '',
                'secret' => '',
                'amex_secret' => '',
                'sandbox' => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['identifier', 'password'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    [
                        'identifier' => $config['identifier'],
                        'amex_identifier' => $config['amex_identifier'],
                        'apikeyid' => $config['apikeyid'],
                        'password' => $config['password'],
                        'secret' => $config['secret'],
                        'amex_secret' => $config['amex_secret'],
                        'sandbox' => $config['sandbox'],
                    ],
                    $config['payum.http_client'],
                    $config['httplug.message_factory']
                );
            };
        }
    }
}
