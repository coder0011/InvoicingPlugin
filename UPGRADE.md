### UPGRADE FROM 0.25 TO 1.0

1. Support for Sylius 1.14 has been added, it is now the recommended Sylius version to use with InvoicingPlugin.

1. Support for Sylius 1.12 has been dropped, upgrade your application to [Sylius 1.13](https://github.com/Sylius/Sylius/blob/1.13/UPGRADE-1.13.md).
   or [Sylius 1.14](https://github.com/Sylius/Sylius/blob/1.14/UPGRADE-1.14.md).

1. The directories structure has been updated to the current Symfony recommendations:
   - `@SyliusInvoicingPlugin/Resources/assets` -> `@SyliusInvoicingPlugin/assets`
   - `@SyliusInvoicingPlugin/Resources/config` -> `@SyliusInvoicingPlugin/config`
   - `@SyliusInvoicingPlugin/Resources/translations` -> `@SyliusInvoicingPlugin/translations`
   - `@SyliusInvoicingPlugin/Resources/views` -> `@SyliusInvoicingPlugin/templates`

   You need to adjust the import of configuration file in your end application:
   ```diff
   imports:
   -   - { resource: "@SyliusInvoicingPlugin/Resources/config/config.yml" }
   +   - { resource: '@SyliusInvoicingPlugin/config/config.yaml' }
   ```
   
   And the routes configuration paths:
   ```diff
   sylius_invoicing_plugin_admin:
   -   resource: "@SyliusInvoicingPlugin/Resources/config/app/routing/admin_invoicing.yml"
   -   prefix: /admin
   +   resource: '@SyliusInvoicingPlugin/config/admin_routes.yaml'
   +   prefix: '/%sylius_admin.path_name%'
    
   sylius_invoicing_plugin_shop:
   -   resource: "@SyliusInvoicingPlugin/Resources/config/app/routing/shop_invoicing.yml"
   +   resource: '@SyliusInvoicingPlugin/config/shop_routes.yaml'
       prefix: /{_locale}
       requirements:
           _locale: ^[a-z]{2}(?:_[A-Z]{2})?$
   ```

   And the paths to assets and templates if you are using them.   

1. The deprecated method `Sylius\InvoicingPlugin\Entity\InvoiceInterface::orderNumber()` has been removed, 
   use `Sylius\InvoicingPlugin\Entity\InvoiceInterface::order()` instead.

1. The `Sylius\InvoicingPlugin\SystemDateTimeProvider` class, `Sylius\InvoicingPlugin\DateTimeProvider` interface
   and corresponding `sylius_invoicing_plugin.date_time_provider` service have been removed. 
   It has been replaced by `clock` service and `Symfony\Component\Clock\ClockInterface` interface.

   Affected classes:
   - `Sylius\InvoicingPlugin\Creator\MassInvoicesCreator`
   - `Sylius\InvoicingPlugin\EventProducer\OrderPaymentPaidProducer`
   - `Sylius\InvoicingPlugin\EventProducer\OrderPlacedProducer`
   - `Sylius\InvoicingPlugin\Generator\SequentialInvoiceNumberGenerator`

1. The translation keys have been changed from `sylius_invoicing_plugin` and `sylius_admin_order_creation` to `sylius_invoicing`.

1. The `sylius_invoicing_admin_order_show_by_number` route and `Sylius\InvoicingPlugin\Ui\RedirectToOrderShowAction` controller
   have been removed and replaced by the `sylius_admin_order_show` route from the Sylius Core.

1. The following templates have been removed:
   - `@SyliusInvoicingPlugin/Invoice/Grid/Field/orderNumber.html.twig`
   - `@SyliusInvoicingPlugin/Invoice/Grid/Field/channel.html.twig`

1. The custom `invoice_channel` filter, its `Sylius\InvoicingPlugin\Grid\Filter\ChannelFilter` class 
   and `Sylius\InvoicingPlugin\Form\Type\ChannelFilterType` form type have been removed and replaced by 
   the `entity` filter from GridBundle.

1. The invoice grid configuration has been updated accordingly to the above changes:

   ```diff
   sylius_grid:
   -   templates:
   -       filter:
   -           invoice_channel: '@SyliusInvoicingPlugin/Grid/Filter/channel.html.twig'
       grids:
           sylius_invoicing_plugin_invoice:
   #           ...
               fields:
   #               ...
                   orderNumber:
                       type: twig
                       label: sylius.ui.order
   -                   path: order.number
   -                   options:
   -                       template: '@SyliusInvoicingPlugin/Invoice/Grid/Field/orderNumber.html.twig'
   +                   path: order
   +                   options:
   +                       template: "@SyliusAdmin/Order/Grid/Field/number.html.twig"
                       sortable: order.number
                   channel:
                       type: twig
                       label: sylius.ui.channel
                       options:
   -                       template: "@SyliusInvoicingPlugin/Invoice/Grid/Field/channel.html.twig"
   +                       template: "@SyliusAdmin/Order/Grid/Field/channel.html.twig"
               filters:
   #               ...
                   channel:
   -                   type: invoice_channel
   +                   type: entity
                       label: sylius.ui.channel
   +                   form_options:
   +                       class: "%sylius.model.channel.class%"
   #           ...
   ```
