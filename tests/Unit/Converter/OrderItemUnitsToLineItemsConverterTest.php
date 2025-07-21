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

namespace Tests\Sylius\InvoicingPlugin\Unit\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\InvoicingPlugin\Converter\LineItemsConverterInterface;
use Sylius\InvoicingPlugin\Converter\OrderItemUnitsToLineItemsConverter;
use Sylius\InvoicingPlugin\Entity\LineItemInterface;
use Sylius\InvoicingPlugin\Factory\LineItemFactoryInterface;
use Sylius\InvoicingPlugin\Provider\TaxRatePercentageProviderInterface;
use Sylius\InvoicingPlugin\Provider\UnitNetPriceProviderInterface;

final class OrderItemUnitsToLineItemsConverterTest extends TestCase
{
    private MockObject&TaxRatePercentageProviderInterface $taxRatePercentageProvider;

    private LineItemFactoryInterface&MockObject $lineItemFactory;

    private MockObject&UnitNetPriceProviderInterface $unitNetPriceProvider;

    private OrderItemUnitsToLineItemsConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxRatePercentageProvider = $this->createMock(TaxRatePercentageProviderInterface::class);
        $this->lineItemFactory = $this->createMock(LineItemFactoryInterface::class);
        $this->unitNetPriceProvider = $this->createMock(UnitNetPriceProviderInterface::class);

        $this->converter = new OrderItemUnitsToLineItemsConverter(
            $this->taxRatePercentageProvider,
            $this->lineItemFactory,
            $this->unitNetPriceProvider,
        );
    }

    /** @test */
    public function it_implements_line_items_converter_interface(): void
    {
        self::assertInstanceOf(LineItemsConverterInterface::class, $this->converter);
    }

    /** @test */
    public function it_extracts_line_items_from_order_item_units(): void
    {
        $lineItem = $this->createMock(LineItemInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);
        $orderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);

        $this->lineItemFactory
            ->expects(self::once())
            ->method('createWithData')
            ->with('Mjolnir', 1, 6000, 5000, 5000, 500, 5500, null, 'CODE', '10%')
            ->willReturn($lineItem);

        $order
            ->expects(self::once())
            ->method('getItemUnits')
            ->willReturn(new ArrayCollection([$orderItemUnit]));

        $orderItemUnit->expects(self::once())->method('getTaxTotal')->willReturn(500);
        $orderItemUnit->expects(self::once())->method('getTotal')->willReturn(5500);
        $orderItemUnit->expects(self::once())->method('getOrderItem')->willReturn($orderItem);

        $this->unitNetPriceProvider
            ->expects(self::once())
            ->method('getUnitNetPrice')
            ->with($orderItemUnit)
            ->willReturn(6000);

        $orderItem->expects(self::once())->method('getProductName')->willReturn('Mjolnir');
        $orderItem->expects(self::once())->method('getVariant')->willReturn($variant);
        $orderItem->expects(self::once())->method('getVariantName')->willReturn(null);

        $variant->expects(self::once())->method('getCode')->willReturn('CODE');

        $this->taxRatePercentageProvider
            ->expects(self::once())
            ->method('provideFromAdjustable')
            ->with($orderItemUnit)
            ->willReturn('10%');

        $result = $this->converter->convert($order);

        self::assertEquals([$lineItem], $result);
    }

    /** @test */
    public function it_groups_the_same_line_items_during_extracting_order_item_units(): void
    {
        $mjolnirLineItem = $this->createMock(LineItemInterface::class);
        $stormbreakerLineItem = $this->createMock(LineItemInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $firstOrderItem = $this->createMock(OrderItemInterface::class);
        $secondOrderItem = $this->createMock(OrderItemInterface::class);
        $firstOrderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $secondOrderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $thirdOrderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $firstVariant = $this->createMock(ProductVariantInterface::class);
        $secondVariant = $this->createMock(ProductVariantInterface::class);

        $this->lineItemFactory
            ->expects($this->exactly(3))
            ->method('createWithData')
            ->withConsecutive(
                ['Mjolnir', 1, 5000, 5000, 5000, 500, 5500, null, 'MJOLNIR', '10%'],
                ['Mjolnir', 1, 5000, 5000, 5000, 500, 5500, null, 'MJOLNIR', '10%'],
                ['Stormbreaker', 1, 8000, 8000, 8000, 1600, 9600, null, 'STORMBREAKER', '20%'],
            )
            ->willReturnOnConsecutiveCalls($mjolnirLineItem, $mjolnirLineItem, $stormbreakerLineItem);

        $mjolnirLineItem
            ->expects($this->exactly(2))
            ->method('compare')
            ->willReturnCallback(function ($item) use ($mjolnirLineItem) {
                if ($item === $mjolnirLineItem) {
                    return true;
                }

                return false;
            });

        $mjolnirLineItem
            ->expects(self::once())
            ->method('merge')
            ->with($mjolnirLineItem);

        $order
            ->expects(self::once())
            ->method('getItemUnits')
            ->willReturn(new ArrayCollection([
                $firstOrderItemUnit,
                $secondOrderItemUnit,
                $thirdOrderItemUnit,
            ]));

        // First order item unit setup
        $firstOrderItemUnit->expects(self::once())->method('getTaxTotal')->willReturn(500);
        $firstOrderItemUnit->expects(self::once())->method('getTotal')->willReturn(5500);
        $firstOrderItemUnit->expects(self::once())->method('getOrderItem')->willReturn($firstOrderItem);

        $this->unitNetPriceProvider
            ->expects($this->exactly(3))
            ->method('getUnitNetPrice')
            ->withConsecutive([$firstOrderItemUnit], [$secondOrderItemUnit], [$thirdOrderItemUnit])
            ->willReturnOnConsecutiveCalls(5000, 5000, 8000);

        // Second order item unit setup
        $secondOrderItemUnit->expects(self::once())->method('getTaxTotal')->willReturn(500);
        $secondOrderItemUnit->expects(self::once())->method('getTotal')->willReturn(5500);
        $secondOrderItemUnit->expects(self::once())->method('getOrderItem')->willReturn($firstOrderItem);

        // Third order item unit setup
        $thirdOrderItemUnit->expects(self::once())->method('getTaxTotal')->willReturn(1600);
        $thirdOrderItemUnit->expects(self::once())->method('getTotal')->willReturn(9600);
        $thirdOrderItemUnit->expects(self::once())->method('getOrderItem')->willReturn($secondOrderItem);

        $firstOrderItem->expects($this->exactly(2))->method('getProductName')->willReturn('Mjolnir');
        $firstOrderItem->expects($this->exactly(2))->method('getVariant')->willReturn($firstVariant);
        $firstOrderItem->expects($this->exactly(2))->method('getVariantName')->willReturn(null);

        $secondOrderItem->expects(self::once())->method('getProductName')->willReturn('Stormbreaker');
        $secondOrderItem->expects(self::once())->method('getVariant')->willReturn($secondVariant);
        $secondOrderItem->expects(self::once())->method('getVariantName')->willReturn(null);

        $firstVariant->expects($this->exactly(2))->method('getCode')->willReturn('MJOLNIR');
        $secondVariant->expects(self::once())->method('getCode')->willReturn('STORMBREAKER');

        $this->taxRatePercentageProvider
            ->expects($this->exactly(3))
            ->method('provideFromAdjustable')
            ->withConsecutive([$firstOrderItemUnit], [$secondOrderItemUnit], [$thirdOrderItemUnit])
            ->willReturnOnConsecutiveCalls('10%', '10%', '20%');

        $result = $this->converter->convert($order);

        self::assertEquals([$mjolnirLineItem, $stormbreakerLineItem], $result);
    }
}
