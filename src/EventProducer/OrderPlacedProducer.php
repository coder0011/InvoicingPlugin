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

namespace Sylius\InvoicingPlugin\EventProducer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\InvoicingPlugin\Event\OrderPlaced;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class OrderPlacedProducer
{
    public function __construct(
        private readonly MessageBusInterface $eventBus,
        private readonly ClockInterface $clock,
    ) {
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();

        if (
            !$order instanceof OrderInterface ||
            $order->getCheckoutState() !== OrderCheckoutStates::STATE_COMPLETED
        ) {
            return;
        }

        $this->dispatchOrderPlacedEvent($order);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();
        if (!$order instanceof OrderInterface) {
            return;
        }

        $entityManager = $event->getObjectManager();
        Assert::isInstanceOf($entityManager, EntityManagerInterface::class);

        $unitOfWork = $entityManager->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($order);

        if (
            !isset($changeSet['checkoutState']) ||
            $changeSet['checkoutState'][1] !== OrderCheckoutStates::STATE_COMPLETED
        ) {
            return;
        }

        $this->dispatchOrderPlacedEvent($order);
    }

    private function dispatchOrderPlacedEvent(OrderInterface $order): void
    {
        $this->eventBus->dispatch(new OrderPlaced($order->getNumber(), $this->clock->now()));
    }
}
