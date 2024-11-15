<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Sylius\InvoicingPlugin\Creator;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Creator\InvoiceCreatorInterface;
use Symfony\Component\Clock\ClockInterface;

final class MassInvoicesCreatorSpec extends ObjectBehavior
{
    function let(
        InvoiceCreatorInterface $invoiceCreator,
        ClockInterface $clock,
    ): void {
        $this->beConstructedWith($invoiceCreator, $clock);
    }

    function it_requests_invoices_creation_for_multiple_orders(
        InvoiceCreatorInterface $invoiceCreator,
        ClockInterface $clock,
        OrderInterface $firstOrder,
        OrderInterface $secondOrder,
        OrderInterface $thirdOrder,
    ): void {
        $firstOrder->getNumber()->willReturn('0000001');
        $secondOrder->getNumber()->willReturn('0000002');
        $thirdOrder->getNumber()->willReturn('0000003');

        $firstInvoiceDateTime = new \DateTimeImmutable('2019-02-25');
        $secondInvoiceDateTime = new \DateTimeImmutable('2019-02-25');
        $thirdInvoiceDateTime = new \DateTimeImmutable('2019-02-25');

        $clock->now()->willReturn($firstInvoiceDateTime, $secondInvoiceDateTime, $thirdInvoiceDateTime);

        $invoiceCreator->__invoke('0000001', $firstInvoiceDateTime)->shouldBeCalled();
        $invoiceCreator->__invoke('0000002', $secondInvoiceDateTime)->shouldBeCalled();
        $invoiceCreator->__invoke('0000003', $thirdInvoiceDateTime)->shouldBeCalled();

        $this->__invoke([$firstOrder->getWrappedObject(), $secondOrder->getWrappedObject(), $thirdOrder->getWrappedObject()]);
    }
}
