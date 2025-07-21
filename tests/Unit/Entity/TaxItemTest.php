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

namespace Tests\Sylius\InvoicingPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Entity\TaxItem;
use Sylius\InvoicingPlugin\Entity\TaxItemInterface;

final class TaxItemTest extends TestCase
{
    private TaxItem $taxItem;

    protected function setUp(): void
    {
        $this->taxItem = new TaxItem('VAT (23%)', 2300);
    }

    /** @test */
    public function it_implements_tax_item_interface(): void
    {
        $this->assertInstanceOf(TaxItemInterface::class, $this->taxItem);
    }

    /** @test */
    public function it_implements_resource_interface(): void
    {
        $this->assertInstanceOf(ResourceInterface::class, $this->taxItem);
    }

    /** @test */
    public function it_has_proper_tax_item_data(): void
    {
        $this->assertSame('VAT (23%)', $this->taxItem->label());
        $this->assertSame(2300, $this->taxItem->amount());
    }

    /** @test */
    public function it_has_an_invoice(): void
    {
        $invoice = $this->createMock(InvoiceInterface::class);

        $this->taxItem->setInvoice($invoice);

        $this->assertSame($invoice, $this->taxItem->invoice());
    }
}
