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

namespace spec\Sylius\InvoicingPlugin\CommandHandler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\InvoicingPlugin\Command\SendInvoiceEmail;
use Sylius\InvoicingPlugin\Doctrine\ORM\InvoiceRepositoryInterface;
use Sylius\InvoicingPlugin\Email\InvoiceEmailSenderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;

final class SendInvoiceEmailHandlerSpec extends ObjectBehavior
{
    function let(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceEmailSenderInterface $emailSender,
    ): void {
        $this->beConstructedWith($invoiceRepository, $orderRepository, $emailSender);
    }

    function it_requests_an_email_with_an_invoice_to_be_sent(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceEmailSenderInterface $emailSender,
        InvoiceInterface $invoice,
        OrderInterface $order,
        CustomerInterface $customer,
    ): void {
        $orderRepository->findOneByNumber('0000001')->willReturn($order);
        $order->getCustomer()->willReturn($customer);
        $customer->getEmail()->willReturn('shop@example.com');
        $invoiceRepository->findOneByOrder($order)->willReturn($invoice);

        $emailSender->sendInvoiceEmail($invoice, 'shop@example.com')->shouldBeCalled();

        $this(new SendInvoiceEmail('0000001'));
    }

    function it_does_not_request_an_email_to_be_sent_if_order_was_not_found(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceEmailSenderInterface $emailSender,
        CustomerInterface $customer,
    ): void {
        $orderRepository->findOneByNumber('0000001')->willReturn(null);

        $invoiceRepository->findOneByOrder(Argument::any())->shouldNotBeCalled();
        $customer->getEmail()->shouldNotBeCalled();
        $emailSender->sendInvoiceEmail(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this(new SendInvoiceEmail('0000001'));
    }

    function it_does_not_request_an_email_to_be_sent_if_customer_was_not_found(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceEmailSenderInterface $emailSender,
        OrderInterface $order,
        CustomerInterface $customer,
    ): void {
        $orderRepository->findOneByNumber('0000001')->willReturn($order);
        $order->getCustomer()->willReturn(null);

        $invoiceRepository->findOneByOrder($order)->shouldNotBeCalled();
        $customer->getEmail()->shouldNotBeCalled();
        $emailSender->sendInvoiceEmail(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this(new SendInvoiceEmail('0000001'));
    }

    function it_does_not_request_an_email_to_be_sent_if_invoice_was_not_found(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceEmailSenderInterface $emailSender,
        OrderInterface $order,
        CustomerInterface $customer,
    ): void {
        $orderRepository->findOneByNumber('0000001')->willReturn($order);
        $order->getCustomer()->willReturn($customer);
        $invoiceRepository->findOneByOrder($order)->willReturn(null);

        $customer->getEmail()->shouldNotBeCalled();
        $emailSender->sendInvoiceEmail(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this(new SendInvoiceEmail('0000001'));
    }
}
