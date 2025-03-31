### UPGRADE FROM 2.0.2 to 2.0.3

1. #### Removed overwriting of shipping address in `CompleteOrderAction` and introduced shipping address update to PayPal.
   Previously, the `Sylius\PayPalPlugin\Payum\Action\CompleteOrderAction` class retrieved the shipping address from PayPal after order completion and overwrote
   the existing shipping address in Sylius. This behavior was incorrect because if a customer changed their address after completing the PayPal checkout,
   the system would revert it back to the PayPal-provided address.

   This mechanism has been removed. Instead, a new mechanism has been introduced: if shipping is required, the order completion process now updates
   the PayPal shipping address with the one stored in Sylius. This ensures that the shipping address remains consistent with the one confirmed in the store.

1. The following classes have been deprecated and will be removed in Sylius/PayPalPlugin 3.0:
   - `Sylius\PayPalPlugin\Processor\PayPalAddressProcessor`
   - `Sylius\PayPalPlugin\Processor\PayPalAddressProcessorInterface`

1. The following constructor signatures have been changed:

`Sylius\PayPalPlugin\Payum\Action\CompleteOrderAction`:
```diff
public function __construct(
    private CacheAuthorizeClientApiInterface $authorizeClientApi,
    private UpdateOrderApiInterface $updateOrderApi,
    private CompleteOrderApiInterface $completeOrderApi,
    private OrderDetailsApiInterface $orderDetailsApi,
-   private PayPalAddressProcessorInterface $payPalAddressProcessor,
+   private ?PayPalAddressProcessorInterface $payPalAddressProcessor,
    private PaymentUpdaterInterface $payPalPaymentUpdater,
    private StateResolverInterface $orderPaymentStateResolver,
+   private ?UpdateOrderAddressApiInterface $updateOrderAddressApi = null,
)
```

### UPGRADE FROM 2.0.0 to 2.0.1

1. The following constructor signatures have been changed:

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

# UPGRADE FROM 1.x to 2.0

1. Support for Sylius 2.0 has been added, it is now the recommended Sylius version to use with PayPalPlugin.

1. Support for Sylius 1.X has been dropped, upgrade your application to [Sylius 2.0](https://github.com/Sylius/Sylius/blob/2.0/UPGRADE-2.0.md).

1. The minimum supported version of PHP has been increased to 8.2.

1. The configuration root node has been changed from `sylius_pay_pal` to `sylius_paypal`.

1. The factory name has been changed from `sylius.pay_pal` to `sylius_paypal`.

1. The translations' prefix has been changed from `sylius.pay_pal` to `sylius_paypal`. Additionally, all occurrences of `pay_pal` have been replaced with `paypal`.

1. The directories structure has been updated to the current Symfony recommendations:
   - `@SyliusPayPalPlugin/Resources/config` -> `@SyliusPayPalPlugin/config`
   - `@SyliusPayPalPlugin/Resources/translations` -> `@SyliusPayPalPlugin/translations`
   - `@SyliusPayPalPlugin/Resources/views` -> `@SyliusPayPalPlugin/templates`

   You need to adjust the import of configuration file in your end application:
   ```diff
   imports:
   -   - { resource: "@SyliusPayPalPlugin/Resources/config/config.yml" }
   +   - { resource: '@SyliusPayPalPlugin/config/config.yaml' }
   ```

   The routes have been consolidated into a single `config/routes.yaml` file. It is sufficient to import this file instead of defining each route explicitly:
   ```yaml
       sylius_paypal:
           resource: "@SyliusPayPalPlugin/config/routes.yaml"
   ```

   However, if you have customized the routes, you need to adjust the paths to reflect the new structure:
   ```diff
   sylius_paypal_admin:
   -   resource: "@SyliusPayPalPlugin/Resources/config/admin_routing.yaml"
   -   prefix: /admin
   +   resource: "@SyliusPayPalPlugin/config/routes/admin.yaml"
   +   prefix: '/%sylius_admin.path_name%'

   sylius_paypal_shop:
   -   resource: "@SyliusPayPalPlugin/Resources/config/shop_routing.yaml"
   +   resource: "@SyliusPayPalPlugin/config/routes/shop.yaml"
       prefix: /{_locale}
       requirements:
           _locale: ^[a-z]{2}(?:_[A-Z]{2})?$

   sylius_paypal_webhook:
   -   resource: "@SyliusPayPalPlugin/Resources/config/webhook_routing.yaml"
   +   resource: "@SyliusPayPalPlugin/config/routes/webhook.yaml"
   ```

   And the paths to assets and templates if you are using them.

1. The following routes have been renamed:

| Old route                                                      | New route                                                    |
|----------------------------------------------------------------|--------------------------------------------------------------|
| `sylius_paypal_plugin_admin_download_payouts_report`           | `sylius_paypal_admin_download_payouts_report`                |
| `sylius_paypal_plugin_admin_enable_seller`                     | `sylius_paypal_admin_enable_seller`                          |
| `sylius_paypal_plugin_pay_with_paypal_form`                    | `sylius_paypal_shop_pay_with_paypal_form`                    |
| `sylius_paypal_plugin_create_paypal_order`                     | `sylius_paypal_shop_create_paypal_order`                     |
| `sylius_paypal_plugin_create_paypal_order_from_cart`           | `sylius_paypal_shop_create_paypal_order_from_cart`           |
| `sylius_paypal_plugin_complete_paypal_order`                   | `sylius_paypal_shop_complete_paypal_order`                   |
| `sylius_paypal_plugin_process_paypal_order`                    | `sylius_paypal_shop_process_paypal_order`                    |
| `sylius_paypal_plugin_update_paypal_order`                     | `sylius_paypal_shop_update_paypal_order`                     |
| `sylius_paypal_plugin_create_paypal_order_from_payment_page`   | `sylius_paypal_shop_create_paypal_order_from_payment_page`   |
| `sylius_paypal_plugin_complete_paypal_order_from_payment_page` | `sylius_paypal_shop_complete_paypal_order_from_payment_page` |
| `sylius_paypal_plugin_cancel_order`                            | `sylius_paypal_shop_cancel_order`                            |
| `sylius_paypal_plugin_cancel_payment`                          | `sylius_paypal_shop_cancel_payment`                          |
| `sylius_paypal_plugin_cancel_last_payment`                     | `sylius_paypal_shop_cancel_last_payment`                     |
| `sylius_paypal_plugin_cancel_checkout_payment`                 | `sylius_paypal_shop_cancel_checkout_payment`                 |
| `sylius_paypal_plugin_payment_error`                           | `sylius_paypal_shop_payment_error`                           |
| `sylius_paypal_plugin_webhook_refund_order`                    | `sylius_paypal_webhook_refund_order`                         |

1. The route `sylius_paypal_plugin_create_paypal_order_from_product` has been removed and replaced with the `AddToCartFormComponent`.

1. The route `sylius_paypal_plugin_complete_paypal_payment` has been removed because the associated controller does not exist.

1. The following callbacks of WinzouStateMachine have been renamed:
   - `complete_pay_pal_order` -> `complete_paypal_order`
   - `refund_pay_pal_order` -> `refund_paypal_order`

1. The Twig function `sylius_is_pay_pal_enabled` has been renamed to `sylius_is_paypal_enabled`.

1. **No need to overwrite templates**:  
   Thanks to the use of Twig Hooks and the refactoring of templates, you no longer need to overwrite templates to use plugin features.

1. The following classes have been removed:
   - `Sylius\PayPalPlugin\Controller\PayPalOrderItemController`
   - `Sylius\PayPalPlugin\Form\Type\ChangePaymentMethodType`
   - `Sylius\PayPalPlugin\Form\Type\SelectPaymentType`
   - `Sylius\PayPalPlugin\Resolver\CompleteOrderPaymentResolver`
   - `Sylius\PayPalPlugin\Resolver\CompleteOrderPaymentResolverInterface`

1. The definition of `Http\Discovery\Psr18Client` has been removed, use `sylius.http_client` service instead.

1. The command `sylius:pay-pal-plugin:complete-payments` has been changed to `sylius-paypal:complete-payments`.

1. Aliases introduced in PayPalPlugin 1.7 have now become the primary service IDs in PayPalPlugin 2.0.
   The old service IDs have been removed, and all references must be updated accordingly:

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

1. The following services had new aliases added in PayPalPlugin 1.7. In PayPalPlugin 2.0, these aliases have become
   the primary service IDs, and the old service IDs remain as aliases:

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

1. The following parameters have been renamed:

   | Old parameter                            | New parameter                            | 
   |------------------------------------------|------------------------------------------|
   | `sylius.paypal.prioritized_factory_name` | `sylius_paypal.prioritized_factory_name` |
   | `sylius.pay_pal.request_trials_limit`    | `sylius_paypal.request_trials_limit`     |
   | `sylius.paypal.logging.increased`        | `sylius_paypal.logging.increased`        |
   | `sylius.pay_pal.facilitator_url`         | `sylius_paypal.facilitator_url`          |
   | `sylius.pay_pal.api_base_url`            | `sylius_paypal.api_base_url`             |
   | `sylius.pay_pal.reports_sftp_host`       | `sylius_paypal.reports_sftp_host`        |

1. The parameter `sylius.paypal.logging_level_increased` has been removed.

1. The following constructor signatures have been changed:

   `Sylius\PayPalPlugin\Client\PayPalClient`
   ```diff
     public function __construct(
   -     private readonly GuzzleClientInterface|ClientInterface $client,
   +     private readonly ClientInterface $client,
         private readonly LoggerInterface $logger,
         private readonly UuidProviderInterface $uuidProvider,
         private readonly PayPalConfigurationProviderInterface $payPalConfigurationProvider,
         private readonly ChannelContextInterface $channelContext,
         private readonly string $baseUrl,
         private int $requestTrialsLimit,
   -     private readonly bool $loggingLevelIncreased = false,
   -     private readonly ?RequestFactoryInterface $requestFactory = null,
   -     private readonly ?StreamFactoryInterface $streamFactory = null,
   +     private readonly RequestFactoryInterface $requestFactory,
   +     private readonly StreamFactoryInterface $streamFactory,
   +     private readonly bool $loggingLevelIncreased = false,
     )
   ```

   `Sylius\PayPalPlugin\Api\GenericApi`
   ```diff
     public function __construct(
   -      private readonly GuzzleClientInterface|ClientInterface $client,
   +      private readonly ClientInterface $client,
   -      private readonly ?RequestFactoryInterface $requestFactory = null,
   +      private readonly RequestFactoryInterface $requestFactory,
   )
   ```

   `Sylius\PayPalPlugin\Api\WebhookApi`
   ```diff
   public function __construct(
   -      private readonly GuzzleClientInterface|ClientInterface $client,
   +      private readonly ClientInterface $client,
          private readonly string $baseUrl,
   -      private readonly ?RequestFactoryInterface $requestFactory = null,
   -      private readonly ?StreamFactoryInterface $streamFactory = null,
   +      private readonly RequestFactoryInterface $requestFactory,
   +      private readonly StreamFactoryInterface $streamFactory,
   )
   ```

   `Sylius\PayPalPlugin\Onboarding\Processor\BasicOnboardingProcessor`
   ```diff
   public function __construct(
   -      private readonly GuzzleClientInterface|ClientInterface $client,
   +      private readonly ClientInterface $client,
          private readonly SellerWebhookRegistrarInterface $sellerWebhookRegistrar,
          private readonly string $url,
   -      private readonly ?RequestFactoryInterface $requestFactory = null,
   +      private readonly RequestFactoryInterface $requestFactory,
   )
   ```

   `Sylius\PayPalPlugin\Controller\ProcessPayPalOrderAction`
   ```diff
   public function __construct(
   -      private ?OrderRepositoryInterface $orderRepository,
          private CustomerRepositoryInterface $customerRepository,
          private FactoryInterface $customerFactory,
          private AddressFactoryInterface $addressFactory,
          private ObjectManager $orderManager,
   -      private StateMachineFactoryInterface|StateMachineInterface $stateMachineFactory,
   +      private StateMachineInterface $stateMachineFactory,
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
   -      ?OrderDetailsApiInterface $orderDetailsApi,
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
      -      ?PayPalItemDataProviderInterface $payPalItemsDataProvider,
      )
      ```

   `Sylius\PayPalPlugin\Console\Command\CompletePaidPaymentsCommand`
      ```diff
      public function __construct(
             PaymentRepositoryInterface $paymentRepository,
             ObjectManager $paymentManager,
             CacheAuthorizeClientApiInterface $authorizeClientApi,
             OrderDetailsApiInterface $orderDetailsApi,
      -      FactoryInterface|StateMachineInterface $stateMachineFactory,
      +      StateMachineInterface $stateMachine,
      )
      ```
