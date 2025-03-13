### UPGRADE FROM 1.6 to 1.7

1. Support for Sylius 1.14 has been added, it is now the recommended Sylius version to use.

1. Support for Sylius 1.12 has been dropped, upgrade your application to [Sylius 1.13](https://github.com/Sylius/Sylius/blob/1.13/UPGRADE-1.13.md).
   or [Sylius 1.14](https://github.com/Sylius/Sylius/blob/1.14/UPGRADE-1.14.md).

1. The following classes have been deprecated and will be removed in Sylius/PayPalPlugin 2.0:
   - `Sylius\PayPalPlugin\Form\Type\ChangePaymentMethodType`
   - `Sylius\PayPalPlugin\Form\Type\SelectPaymentType`
   - `Sylius\PayPalPlugin\Controller\PayPalOrderItemController`
   - `Sylius\PayPalPlugin\Resolver\CompleteOrderPaymentResolver`
   - `Sylius\PayPalPlugin\Resolver\CompleteOrderPaymentResolverInterface`

1. The definition of `Sylius\PayPalPlugin\Provider\PayPalOrderProviderInterface` has been removed because neither the interface nor the class exists in the codebase.

1. The definition of `Http\Discovery\Psr18Client` has been deprecated and will be removed in Sylius/PayPalPlugin 2.0. Use `sylius.http_client` service instead.

1. The routes `sylius_shop_checkout_select_payment` and `sylius_shop_order_show` have been moved to the `sylius_shop_override_routing.yaml` file because they override 
   routes from SyliusShop. These routes are also imported in the `shop_routing.yaml` file, so no action is required unless you have overridden them yourself.

1. Aliases for the following services have been introduced to standardize service IDs and will replace the incorrect IDs
   in PayPalPlugin 2.0. The old service IDs are now deprecated and will be removed in PayPalPlugin 2.0.
   Please update your service definitions accordingly to ensure compatibility with next major version of PayPalPlugin.

   | Old ID                                                                    | New ID                                                                  |
   |---------------------------------------------------------------------------|-------------------------------------------------------------------------|
   | `Sylius\PayPalPlugin\ApiPlatform\PayPalPayment`                           | `sylius_paypal.api_platform.paypal_payment`                             |
   | `Sylius\PayPalPlugin\Command\CompletePaidPaymentsCommand`                 | `sylius_paypal.console.command.complete_paid_payments`                  |
   | `Sylius\PayPalPlugin\Controller\CancelLastPayPalPaymentAction`            | `sylius_paypal.controller.cancel_last_paypal_payment`                   |
   | `Sylius\PayPalPlugin\Controller\CancelPayPalCheckoutPaymentAction`        | `sylius_paypal.controller.cancel_paypal_checkout_payment`               |
   | `Sylius\PayPalPlugin\Controller\CancelPayPalOrderAction`                  | `sylius_paypal.controller.cancel_paypal_order`                          |
   | `Sylius\PayPalPlugin\Controller\CancelPayPalPaymentAction`                | `sylius_paypal.controller.cancel_paypal_payment`                        |
   | `Sylius\PayPalPlugin\Controller\CompletePayPalOrderAction`                | `sylius_paypal.controller.complete_paypal_order`                        |
   | `Sylius\PayPalPlugin\Controller\CompletePayPalOrderFromPaymentPageAction` | `sylius_paypal.controller.complete_paypal_order_from_payment_page`      |
   | `Sylius\PayPalPlugin\Controller\CreatePayPalOrderAction`                  | `sylius_paypal.controller.create_paypal_order`                          |
   | `Sylius\PayPalPlugin\Controller\CreatePayPalOrderFromCartAction`          | `sylius_paypal.controller.create_paypal_order_from_cart`                |
   | `Sylius\PayPalPlugin\Controller\CreatePayPalOrderFromPaymentPageAction`   | `sylius_paypal.controller.create_paypal_order_from_payment_page`        |
   | `Sylius\PayPalPlugin\Controller\DownloadPayoutsReportAction`              | `sylius_paypal.controller.download_payouts_report`                      |
   | `Sylius\PayPalPlugin\Controller\EnableSellerAction`                       | `sylius_paypal.controller.enable_seller`                                |
   | `Sylius\PayPalPlugin\Controller\PayPalButtonsController`                  | `sylius_paypal.controller.paypal_buttons`                               |
   | `Sylius\PayPalPlugin\Controller\PayPalPaymentOnErrorAction`               | `sylius_paypal.controller.paypal_payment_on_error`                      |
   | `Sylius\PayPalPlugin\Controller\PayWithPayPalFormAction`                  | `sylius_paypal.controller.pay_with_paypal_form`                         |
   | `Sylius\PayPalPlugin\Controller\ProcessPayPalOrderAction`                 | `sylius_paypal.controller.process_paypal_order`                         |
   | `Sylius\PayPalPlugin\Controller\UpdatePayPalOrderAction`                  | `sylius_paypal.controller.update_paypal_order`                          |
   | `Sylius\PayPalPlugin\Controller\Webhook\RefundOrderAction`                | `sylius_paypal.controller.webhook.refund_order`                         |
   | `Sylius\PayPalPlugin\EventListener\Workflow\CompletePayPalOrderListener`  | `sylius_paypal.listener.workflow.complete_paypal_order`                 |
   | `Sylius\PayPalPlugin\EventListener\Workflow\RefundPaymentListener`        | `sylius_paypal.listener.workflow.refund_payment`                        |
   | `Sylius\PayPalPlugin\Factory\PayPalPaymentMethodNewResourceFactory`       | `sylius_paypal.factory.paypal_payment_method_new_resource`              |
   | `Sylius\PayPalPlugin\Form\Extension\PaymentMethodTypeExtension`           | `sylius_paypal.form.extension.payment_method`                           |
   | `Sylius\PayPalPlugin\Form\Type\PayPalConfigurationType`                   | `sylius_paypal.form.type.paypal_configuration`                          |
   | `Sylius\PayPalPlugin\Listener\PayPalPaymentMethodListener`                | `sylius_paypal.listener.paypal_payment_method`                          |
   | `Sylius\PayPalPlugin\Payum\Action\AuthorizeAction`                        | `sylius_paypal.payum.action.authorize`                                  |
   | `Sylius\PayPalPlugin\Payum\Action\CaptureAction`                          | `sylius_paypal.payum.action.capture`                                    |
   | `Sylius\PayPalPlugin\Payum\Action\CompleteOrderAction`                    | `sylius_paypal.payum.action.complete_order`                             |
   | `Sylius\PayPalPlugin\Payum\Action\ResolveNextRouteAction`                 | `sylius_paypal.payum.action.resolve_next_route`                         |
   | `Sylius\PayPalPlugin\Processor\AfterCheckoutOrderPaymentProcessor`        | `sylius_paypal.order_processing.order_payment_processor.after_checkout` |
   | `Sylius\PayPalPlugin\Processor\OrderPaymentProcessor`                     | `sylius_paypal.order_processing.order_payment_processor.checkout`       |
   | `Sylius\PayPalPlugin\Processor\PayPalAddressProcessor`                    | `sylius_paypal.processor.paypal_address`                                |
   | `Sylius\PayPalPlugin\Processor\PayPalOrderCompleteProcessor`              | `sylius_paypal.processor.paypal_order_complete`                         |
   | `Sylius\PayPalPlugin\Processor\UiPayPalPaymentRefundProcessor`            | `sylius_paypal.processor.ui_paypal_payment_refund`                      |
   | `Sylius\PayPalPlugin\Resolver\PayPalDefaultPaymentMethodResolver`         | `sylius_paypal.resolver.payment_method.paypal`                          |
   | `Sylius\PayPalPlugin\Resolver\PayPalPrioritisingPaymentMethodsResolver`   | `sylius_paypal.resolver.payment_method.paypal_prioritising`             |
   | `Sylius\PayPalPlugin\Twig\OrderAddressExtension`                          | `sylius_paypal.twig.extension.order_address`                            |
   | `Sylius\PayPalPlugin\Twig\PayPalExtension`                                | `sylius_paypal.twig.extension.paypal`                                   |
   | `sylius.paypal.client.sftp`                                               | `sylius_paypal.client.sftp`                                             |
   | `sylius.plugin.pay_pal.gateway_factory_builder`                           | `sylius_paypal.gateway_factory_builder`                                 |
   | `sylius_pay_pal_plugin.repository.pay_pal_credentials`                    | `sylius_paypal.repository.paypal_credentials`                           |

1. For the following services, new aliases have been added, these aliases will become the primary services IDs
   in PayPalPlugin 2.0, while the current service IDs will be converted into aliases:

   | Current ID                                                              | New Alias                                           |
   |-------------------------------------------------------------------------|-----------------------------------------------------|
   | `Sylius\PayPalPlugin\Api\AuthorizeClientApiInterface`                   | `sylius_paypal.api.authorize_client`                |
   | `Sylius\PayPalPlugin\Api\CacheAuthorizeClientApiInterface`              | `sylius_paypal.api.cache_authorize_client`          |
   | `Sylius\PayPalPlugin\Api\CompleteOrderApiInterface`                     | `sylius_paypal.api.complete_order`                  |
   | `Sylius\PayPalPlugin\Api\CreateOrderApiInterface`                       | `sylius_paypal.api.create_order`                    |
   | `Sylius\PayPalPlugin\Api\GenericApiInterface`                           | `sylius_paypal.api.generic`                         |
   | `Sylius\PayPalPlugin\Api\IdentityApiInterface`                          | `sylius_paypal.api.identity`                        |
   | `Sylius\PayPalPlugin\Api\OrderDetailsApiInterface`                      | `sylius_paypal.api.order_details`                   |
   | `Sylius\PayPalPlugin\Api\RefundPaymentApiInterface`                     | `sylius_paypal.api.refund_payment`                  |
   | `Sylius\PayPalPlugin\Api\UpdateOrderApiInterface`                       | `sylius_paypal.api.update_order`                    |
   | `Sylius\PayPalPlugin\Api\WebhookApiInterface`                           | `sylius_paypal.api.webhook`                         |
   | `Sylius\PayPalPlugin\Client\PayPalClientInterface`                      | `sylius_paypal.client.paypal`                       |
   | `Sylius\PayPalPlugin\Downloader\ReportDownloaderInterface`              | `sylius_paypal.downloader.report`                   |
   | `Sylius\PayPalPlugin\Enabler\PaymentMethodEnablerInterface`             | `sylius_paypal.enabler.payment_method`              |
   | `Sylius\PayPalPlugin\Generator\PayPalAuthAssertionGeneratorInterface`   | `sylius_paypal.generator.paypal_auth_assertion`     |
   | `Sylius\PayPalPlugin\Manager\PaymentStateManagerInterface`              | `sylius_paypal.manager.payment_state`               |
   | `Sylius\PayPalPlugin\Onboarding\Initiator\OnboardingInitiatorInterface` | `sylius_paypal.onboarding.initiator`                |
   | `Sylius\PayPalPlugin\Onboarding\Processor\OnboardingProcessorInterface` | `sylius_paypal.onboarding.processor.basic`          |
   | `Sylius\PayPalPlugin\Processor\PaymentCompleteProcessorInterface`       | `sylius_paypal.processor.payment_complete`          |
   | `Sylius\PayPalPlugin\Processor\LocaleProcessorInterface`                | `sylius_paypal.processor.locale`                    |
   | `Sylius\PayPalPlugin\Processor\PaymentRefundProcessorInterface`         | `sylius_paypal.processor.payment_refund`            |
   | `Sylius\PayPalPlugin\Provider\AvailableCountriesProviderInterface`      | `sylius_paypal.provider.available_countries`        |
   | `Sylius\PayPalPlugin\Provider\OrderItemNonNeutralTaxProviderInterface`  | `sylius_paypal.provider.order_item_non_neutral_tax` |
   | `Sylius\PayPalPlugin\Provider\OrderProviderInterface`                   | `sylius_paypal.provider.order`                      |
   | `Sylius\PayPalPlugin\Provider\PaymentProviderInterface`                 | `sylius_paypal.provider.payment`                    |
   | `Sylius\PayPalPlugin\Provider\PaymentReferenceNumberProviderInterface`  | `sylius_paypal.provider.payment_reference_number`   |
   | `Sylius\PayPalPlugin\Provider\PayPalConfigurationProviderInterface`     | `sylius_paypal.provider.paypal_configuration`       |
   | `Sylius\PayPalPlugin\Provider\PayPalItemDataProviderInterface`          | `sylius_paypal.provider.paypal_item_data`           |
   | `Sylius\PayPalPlugin\Provider\PayPalPaymentMethodProviderInterface`     | `sylius_paypal.provider.paypal_payment_method`      |
   | `Sylius\PayPalPlugin\Provider\PayPalRefundDataProviderInterface`        | `sylius_paypal.provider.paypal_refund_data`         |
   | `Sylius\PayPalPlugin\Provider\RefundReferenceNumberProviderInterface`   | `sylius_paypal.provider.refund_reference_number`    |
   | `Sylius\PayPalPlugin\Provider\UuidProviderInterface`                    | `sylius_paypal.provider.uuid`                       |
   | `Sylius\PayPalPlugin\Registrar\SellerWebhookRegistrarInterface`         | `sylius_paypal.registrar.seller_webhook`            |
   | `Sylius\PayPalPlugin\Resolver\CapturePaymentResolverInterface`          | `sylius_paypal.resolver.capture_payment`            |
   | `Sylius\PayPalPlugin\Updater\PaymentUpdaterInterface`                   | `sylius_paypal.updater.payment`                     |

1. The following old parameters have been deprecated and will be removed in PayPalPlugin 2.0. Use the corresponding new parameters instead:

   | Old parameter                            | New parameter                            | 
   |------------------------------------------|------------------------------------------|
   | `sylius.paypal.prioritized_factory_name` | `sylius_paypal.prioritized_factory_name` |
   | `sylius.pay_pal.request_trials_limit`    | `sylius_paypal.request_trials_limit`     |
   | `sylius.paypal.logging.increased`        | `sylius_paypal.logging.increased`        |
   | `sylius.pay_pal.facilitator_url`         | `sylius_paypal.facilitator_url`          |
   | `sylius.pay_pal.api_base_url`            | `sylius_paypal.api_base_url`             |
   | `sylius.pay_pal.reports_sftp_host`       | `sylius_paypal.reports_sftp_host`        |

1. The parameter `sylius.paypal.logging_level_increased` has been deprecated and will be removed in PayPalPlugin 2.0, because it is not used.

1. The following constructor signatures have been changed:

   `Sylius\PayPalPlugin\Controller\ProcessPayPalOrderAction`
      ```diff
      public function __construct(
      -      private OrderRepositoryInterface $orderRepository,
      +      private ?OrderRepositoryInterface $orderRepository,
             private CustomerRepositoryInterface $customerRepository,
             private FactoryInterface $customerFactory,
             private AddressFactoryInterface $addressFactory,
             private ObjectManager $orderManager,
             private StateMachineFactoryInterface|StateMachineInterface $stateMachineFactory,
             private PaymentStateManagerInterface $paymentStateManager,
             private CacheAuthorizeClientApiInterface $authorizeClientApi,
             private OrderDetailsApiInterface $orderDetailsApi,
             private OrderProviderInterface $orderProvider,
      )
      ```

   `Sylius\PayPalPlugin\Controller\UpdatePayPalOrderAction`
      ```diff
      public function __construct(
             PaymentProviderInterface $paymentProvider,
             CacheAuthorizeClientApiInterface $authorizeClientApi,
      -      OrderDetailsApiInterface $orderDetailsApi,
      +      ?OrderDetailsApiInterface $orderDetailsApi,
             UpdateOrderApiInterface $updateOrderApi,
             AddressFactoryInterface $addressFactory,
             OrderProcessorInterface $orderProcessor,
      )
      ```

   `Sylius\PayPalPlugin\Payum\Action\CompleteOrderAction`
      ```diff
      public function __construct(
             CacheAuthorizeClientApiInterface $authorizeClientApi,
             UpdateOrderApiInterface $updateOrderApi,
             CompleteOrderApiInterface $completeOrderApi,
             OrderDetailsApiInterface $orderDetailsApi,
             PayPalAddressProcessorInterface $payPalAddressProcessor,
             PaymentUpdaterInterface $payPalPaymentUpdater,
             StateResolverInterface $orderPaymentStateResolver,
      -      PayPalItemDataProviderInterface $payPalItemsDataProvider,
      +      ?PayPalItemDataProviderInterface $payPalItemsDataProvider,
      )
      ```

### UPGRADE FROM 1.6.0 to 1.6.1

1. The following constructor signatures have been changed:

   `Sylius\PayPalPlugin\Controller\CreatePayPalOrderFromCartAction`:
    ```diff
    public function __construct(
        private readonly ?Payum $payum,
        private readonly ?OrderRepositoryInterface $orderRepository,
        private readonly ?FactoryInterface $stateMachineFactory,
        private readonly ObjectManager $paymentManager,
        private readonly OrderProviderInterface $orderProvider,
        private readonly CapturePaymentResolverInterface $capturePaymentResolver,
    +   private readonly ?OrderPaymentsRemoverInterface $orderPaymentsRemover = null,
    +   private readonly ?OrderProcessorInterface $orderProcessor = null,
    )
    ```

   `Sylius\PayPalPlugin\Model\PayPalPurchaseUnit`:
    ```diff
    public function __construct(
        private readonly string $referenceId,
        private readonly string $invoiceNumber,
        private readonly string $currencyCode,
        private readonly int $totalAmount,
        private readonly int $shippingValue,
        private readonly float $itemTotalValue,
        private readonly float $taxTotalValue,
        private readonly int $discountValue,
        private readonly string $merchantId,
        private readonly array $items,
        private readonly bool $shippingRequired,
        private readonly ?AddressInterface $shippingAddress = null,
        private readonly string $softDescriptor = 'Sylius PayPal Payment',
    +   private readonly int $shippingDiscountValue = 0,
    )
    ```

   `Sylius\PayPalPlugin\Controller\ProcessPayPalOrderAction`:
    ```diff
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly FactoryInterface $customerFactory,
        private readonly AddressFactoryInterface $addressFactory,
        private readonly ObjectManager $orderManager,
        private readonly StateMachineFactoryInterface|StateMachineInterface $stateMachineFactory,
        private readonly PaymentStateManagerInterface $paymentStateManager,
        private readonly CacheAuthorizeClientApiInterface $authorizeClientApi,
        private readonly OrderDetailsApiInterface $orderDetailsApi,
        private readonly OrderProviderInterface $orderProvider,
    +   private readonly ?PaymentAmountVerifierInterface $paymentAmountVerifier = null,
    )
    ```

   `Sylius\PayPalPlugin\Controller\CompletePayPalOrderFromPaymentPageAction`:
    ```diff
    public function __construct(
        private readonly PaymentStateManagerInterface $paymentStateManager,
        private readonly UrlGeneratorInterface $router,
        private readonly OrderProviderInterface $orderProvider,
        private readonly FactoryInterface|StateMachineInterface $stateMachine,
        private readonly ObjectManager $orderManager,
    +   private readonly ?PaymentAmountVerifierInterface $paymentAmountVerifier = null,
    +   private readonly ?OrderProcessorInterface $orderProcessor = null,
    )
    ```

### UPGRADE FROM 1.5.1 to 1.6.0

1. Support for Sylius 1.13 has been added, it is now the recommended Sylius version to use.

1. Support for PHP 8.0 has been dropped.

1. The following constructor signatures have been changed:

    `Sylius\PayPalPlugin\Client\PayPalClient`:
     ```diff
     use Psr\Http\Client\ClientInterface;
     use GuzzleHttp\ClientInterface as GuzzleClientInterface;
     use Psr\Http\Message\RequestFactoryInterface;
     use Psr\Http\Message\StreamFactoryInterface;

        public function __construct(
    -      private readonly GuzzleClientInterface $client,
    +      private readonly GuzzleClientInterface|ClientInterface $client,
            private readonly LoggerInterface $logger,
            private readonly UuidProviderInterface $uuidProvider,
            private readonly PayPalConfigurationProviderInterface $payPalConfigurationProvider,
            private readonly ChannelContextInterface $channelContext,
            private readonly string $baseUrl,
            private int $requestTrialsLimit,
            private readonly bool $loggingLevelIncreased = false,
    +      private readonly ?RequestFactoryInterface $requestFactory = null,
    +      private readonly ?StreamFactoryInterface $streamFactory = null,
        )
     ```

   `Sylius\PayPalPlugin\Api\GeneralApi`:
     ```diff
     use Psr\Http\Client\ClientInterface;
     use GuzzleHttp\ClientInterface as GuzzleClientInterface;
     use Psr\Http\Message\RequestFactoryInterface;

        public function __construct(
   -      private readonly GuzzleClientInterface $client,
   +      private readonly GuzzleClientInterface|ClientInterface $client,
   +      private readonly ?RequestFactoryInterface $requestFactory = null,
    )
     ```

   `Sylius\PayPalPlugin\Api\WebhookApi`:
     ```diff
     use Psr\Http\Client\ClientInterface;
     use GuzzleHttp\ClientInterface as GuzzleClientInterface;
     use Psr\Http\Message\RequestFactoryInterface;
     use Psr\Http\Message\StreamFactoryInterface;

        public function __construct(
   -       private readonly GuzzleClientInterface $client,
   +       private readonly GuzzleClientInterface|ClientInterface $client,
             private readonly string $baseUrl,
   +       private readonly ?RequestFactoryInterface $requestFactory = null,
   +       private readonly ?StreamFactoryInterface $streamFactory = null,
    )
     ```

   `Sylius\PayPalPlugin\Onboarding\Processor\BasicOnboardingProcessor`:
     ```diff
      use Psr\Http\Client\ClientInterface;
      use GuzzleHttp\ClientInterface as GuzzleClientInterface;
      use Psr\Http\Message\RequestFactoryInterface;

        public function __construct(
   -      private readonly GuzzleClientInterface $client,
   +      private readonly GuzzleClientInterface|ClientInterface $client,
            private readonly SellerWebhookRegistrarInterface $sellerWebhookRegistrar,
            private readonly string $url,
   +      private readonly ?RequestFactoryInterface $requestFactory = null,
    )
     ```

1. Added doctrine migration for PostgreSQL. For more information, please refer to the [Sylius 1.13 UPGRADE.md](https://github.com/Sylius/Sylius/blob/1.13/UPGRADE-1.13.md)

### UPGRADE FROM 1.3.0 to 1.3.1

1. `sylius_paypal_plugin_pay_with_paypal_form` route now operates on both payment ID and order token. URl then changed from
   `/pay-with-paypal/{id}` to `/pay-with-paypal/{orderToken}/{paymentId}`. If you use this route anywhere in your application, you
   need to change the URL attributes

### UPGRADE FROM 1.2.3 to 1.2.4

1. `sylius_paypal_plugin_pay_with_paypal_form` route now operates on both payment ID and order token. URl then changed from
    `/pay-with-paypal/{id}` to `/pay-with-paypal/{orderToken}/{paymentId}`. If you use this route anywhere in your application, you
    need to change the URL attributes

### UPGRADE FROM 1.0.X TO 1.1.0

1. Upgrade your application to [Sylius 1.8](https://github.com/Sylius/Sylius/blob/master/UPGRADE-1.8.md).

1. Remove previously copied migration files (You may check migrations to remove [here](https://github.com/Sylius/PayPalPlugin/pull/160/files)).
