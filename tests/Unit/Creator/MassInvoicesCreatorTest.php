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

namespace Tests\Sylius\InvoicingPlugin\Unit\Creator;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Creator\InvoiceCreatorInterface;
use Sylius\InvoicingPlugin\Creator\MassInvoicesCreator;
use Symfony\Component\Clock\ClockInterface;

final class MassInvoicesCreatorTest extends TestCase
{
    private InvoiceCreatorInterface $invoiceCreator;

    private ClockInterface $clock;

    private MassInvoicesCreator $massInvoicesCreator;

    protected function setUp(): void
    {
        $this->invoiceCreator = $this->createMock(InvoiceCreatorInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);

        $this->massInvoicesCreator = new MassInvoicesCreator($this->invoiceCreator, $this->clock);
    }

    /** @test */
    public function it_requests_invoices_creation_for_multiple_orders(): void
    {
        $firstOrder = $this->createMock(OrderInterface::class);
        $secondOrder = $this->createMock(OrderInterface::class);
        $thirdOrder = $this->createMock(OrderInterface::class);

        $firstOrder->method('getNumber')->willReturn('0000001');
        $secondOrder->method('getNumber')->willReturn('0000002');
        $thirdOrder->method('getNumber')->willReturn('0000003');

        $firstInvoiceDateTime = new \DateTimeImmutable('2019-02-25');
        $secondInvoiceDateTime = new \DateTimeImmutable('2019-02-25');
        $thirdInvoiceDateTime = new \DateTimeImmutable('2019-02-25');

        $this->clock
            ->method('now')
            ->willReturnOnConsecutiveCalls($firstInvoiceDateTime, $secondInvoiceDateTime, $thirdInvoiceDateTime);

        $this->invoiceCreator
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->withConsecutive(
                ['0000001', $firstInvoiceDateTime],
                ['0000002', $secondInvoiceDateTime],
                ['0000003', $thirdInvoiceDateTime],
            );

        $this->massInvoicesCreator->__invoke([$firstOrder, $secondOrder, $thirdOrder]);
    }
}
