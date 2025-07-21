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
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Converter\TaxItemsConverter;
use Sylius\InvoicingPlugin\Converter\TaxItemsConverterInterface;
use Sylius\InvoicingPlugin\Entity\TaxItemInterface;
use Sylius\InvoicingPlugin\Factory\TaxItemFactoryInterface;
use Sylius\InvoicingPlugin\Provider\TaxRatePercentageProviderInterface;

final class TaxItemsConverterTest extends TestCase
{
    private TaxRatePercentageProviderInterface $taxRatePercentageProvider;

    private TaxItemFactoryInterface $taxItemFactory;

    private TaxItemsConverter $converter;

    protected function setUp(): void
    {
        $this->taxRatePercentageProvider = $this->createMock(TaxRatePercentageProviderInterface::class);
        $this->taxItemFactory = $this->createMock(TaxItemFactoryInterface::class);

        $this->converter = new TaxItemsConverter(
            $this->taxRatePercentageProvider,
            $this->taxItemFactory,
        );
    }

    /** @test */
    public function it_implements_tax_items_converter_interface(): void
    {
        $this->assertInstanceOf(TaxItemsConverterInterface::class, $this->converter);
    }

    /** @test */
    public function it_extracts_tax_items_from_order(): void
    {
        $taxItem = $this->createMock(TaxItemInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $taxAdjustment = $this->createMock(AdjustmentInterface::class);

        $this->taxItemFactory
            ->expects($this->once())
            ->method('createWithData')
            ->with('10%', 500)
            ->willReturn($taxItem);

        $order
            ->expects($this->once())
            ->method('getAdjustmentsRecursively')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$taxAdjustment]));

        $this->taxRatePercentageProvider
            ->expects($this->once())
            ->method('provideFromAdjustment')
            ->with($taxAdjustment)
            ->willReturn('10%');

        $taxAdjustment
            ->expects($this->once())
            ->method('getAmount')
            ->willReturn(500);

        $result = $this->converter->convert($order);

        $this->assertEquals(new ArrayCollection([$taxItem]), $result);
    }
}
