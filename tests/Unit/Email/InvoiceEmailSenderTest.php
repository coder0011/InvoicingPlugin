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

namespace Tests\Sylius\InvoicingPlugin\Unit\Email;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\InvoicingPlugin\Email\Emails;
use Sylius\InvoicingPlugin\Email\InvoiceEmailSender;
use Sylius\InvoicingPlugin\Email\InvoiceEmailSenderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Model\InvoicePdf;
use Sylius\InvoicingPlugin\Provider\InvoiceFileProviderInterface;

final class InvoiceEmailSenderTest extends TestCase
{
    private SenderInterface $sender;
    private InvoiceFileProviderInterface $invoiceFileProvider;

    protected function setUp(): void
    {
        $this->sender = $this->createMock(SenderInterface::class);
        $this->invoiceFileProvider = $this->createMock(InvoiceFileProviderInterface::class);
    }

    /** @test */
    public function it_implements_invoice_email_sender_interface(): void
    {
        $invoiceEmailSender = new InvoiceEmailSender($this->sender, $this->invoiceFileProvider);

        $this->assertInstanceOf(InvoiceEmailSenderInterface::class, $invoiceEmailSender);
    }

    /** @test */
    public function it_sends_an_invoice_to_a_given_email_address(): void
    {
        $invoiceEmailSender = new InvoiceEmailSender($this->sender, $this->invoiceFileProvider);
        $invoice = $this->createMock(InvoiceInterface::class);

        $invoicePdf = new InvoicePdf('invoice.pdf', 'CONTENT');
        $invoicePdf->setFullPath('/path/to/invoices/invoice.pdf');

        $this->invoiceFileProvider
            ->expects($this->once())
            ->method('provide')
            ->with($invoice)
            ->willReturn($invoicePdf);

        $this->sender
            ->expects($this->once())
            ->method('send')
            ->with(
                Emails::INVOICE_GENERATED,
                ['sylius@example.com'],
                ['invoice' => $invoice],
                ['/path/to/invoices/invoice.pdf'],
            );

        $invoiceEmailSender->sendInvoiceEmail($invoice, 'sylius@example.com');
    }

    /** @test */
    public function it_sends_an_invoice_without_attachment_to_a_given_email_address(): void
    {
        $invoiceEmailSender = new InvoiceEmailSender($this->sender, $this->invoiceFileProvider, false);
        $invoice = $this->createMock(InvoiceInterface::class);

        $this->invoiceFileProvider
            ->expects($this->never())
            ->method('provide');

        $this->sender
            ->expects($this->once())
            ->method('send')
            ->with(Emails::INVOICE_GENERATED, ['sylius@example.com'], ['invoice' => $invoice]);

        $invoiceEmailSender->sendInvoiceEmail($invoice, 'sylius@example.com');
    }
}