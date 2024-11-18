### UPGRADE FROM 0.25 TO 1.0

1. Support for Sylius 1.14 has been added, it is now the recommended Sylius version to use with InvoicingPlugin.

1. Support for Sylius 1.12 has been dropped, upgrade your application to [Sylius 1.13](https://github.com/Sylius/Sylius/blob/1.13/UPGRADE-1.13.md).
   or [Sylius 1.14](https://github.com/Sylius/Sylius/blob/1.14/UPGRADE-1.14.md).

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
