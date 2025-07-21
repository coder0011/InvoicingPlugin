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

namespace Tests\Sylius\InvoicingPlugin\Unit\Provider;

use Gaufrette\Exception\FileNotFound;
use Gaufrette\File;
use Gaufrette\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Generator\InvoiceFileNameGeneratorInterface;
use Sylius\InvoicingPlugin\Generator\InvoicePdfFileGeneratorInterface;
use Sylius\InvoicingPlugin\Manager\InvoiceFileManagerInterface;
use Sylius\InvoicingPlugin\Model\InvoicePdf;
use Sylius\InvoicingPlugin\Provider\InvoiceFileProvider;
use Sylius\InvoicingPlugin\Provider\InvoiceFileProviderInterface;

final class InvoiceFileProviderTest extends TestCase
{
    private InvoiceFileNameGeneratorInterface $invoiceFileNameGenerator;

    private FilesystemInterface $filesystem;

    private InvoicePdfFileGeneratorInterface $invoicePdfFileGenerator;

    private InvoiceFileManagerInterface $invoiceFileManager;

    private InvoiceFileProvider $provider;

    protected function setUp(): void
    {
        $this->invoiceFileNameGenerator = $this->createMock(InvoiceFileNameGeneratorInterface::class);
        $this->filesystem = $this->createMock(FilesystemInterface::class);
        $this->invoicePdfFileGenerator = $this->createMock(InvoicePdfFileGeneratorInterface::class);
        $this->invoiceFileManager = $this->createMock(InvoiceFileManagerInterface::class);

        $this->provider = new InvoiceFileProvider(
            $this->invoiceFileNameGenerator,
            $this->filesystem,
            $this->invoicePdfFileGenerator,
            $this->invoiceFileManager,
            '/path/to/invoices',
        );
    }

    /** @test */
    public function it_implements_invoice_file_provider_interface(): void
    {
        $this->assertInstanceOf(InvoiceFileProviderInterface::class, $this->provider);
    }

    /** @test */
    public function it_provides_invoice_file_for_invoice(): void
    {
        $invoice = $this->createMock(InvoiceInterface::class);
        $invoiceFile = $this->createMock(File::class);

        $this->invoiceFileNameGenerator
            ->expects($this->once())
            ->method('generateForPdf')
            ->with($invoice)
            ->willReturn('invoice.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('get')
            ->with('invoice.pdf')
            ->willReturn($invoiceFile);

        $invoiceFile
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('CONTENT');

        $result = $this->provider->provide($invoice);

        $expected = new InvoicePdf('invoice.pdf', 'CONTENT');
        $expected->setFullPath('/path/to/invoices/invoice.pdf');

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_generates_invoice_if_it_does_not_exist_and_provides_it(): void
    {
        $invoice = $this->createMock(InvoiceInterface::class);

        $this->invoiceFileNameGenerator
            ->expects($this->once())
            ->method('generateForPdf')
            ->with($invoice)
            ->willReturn('invoice.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('get')
            ->with('invoice.pdf')
            ->willThrowException(new FileNotFound('invoice.pdf'));

        $invoicePdf = new InvoicePdf('invoice.pdf', 'CONTENT');
        $invoicePdf->setFullPath('/path/to/invoices/invoice.pdf');

        $this->invoicePdfFileGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($invoice)
            ->willReturn($invoicePdf);

        $this->invoiceFileManager
            ->expects($this->once())
            ->method('save')
            ->with($invoicePdf);

        $result = $this->provider->provide($invoice);

        $expected = new InvoicePdf('invoice.pdf', 'CONTENT');
        $expected->setFullPath('/path/to/invoices/invoice.pdf');

        $this->assertEquals($expected, $result);
    }
}
