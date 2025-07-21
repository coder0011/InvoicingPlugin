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

namespace Tests\Sylius\InvoicingPlugin\Unit\Generator;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceSequenceInterface;
use Sylius\InvoicingPlugin\Generator\InvoiceNumberGenerator;
use Sylius\InvoicingPlugin\Generator\SequentialInvoiceNumberGenerator;
use Symfony\Component\Clock\ClockInterface;

final class SequentialInvoiceNumberGeneratorTest extends TestCase
{
    private RepositoryInterface $sequenceRepository;

    private FactoryInterface $sequenceFactory;

    private EntityManagerInterface $sequenceManager;

    private ClockInterface $clock;

    private SequentialInvoiceNumberGenerator $generator;

    protected function setUp(): void
    {
        $this->sequenceRepository = $this->createMock(RepositoryInterface::class);
        $this->sequenceFactory = $this->createMock(FactoryInterface::class);
        $this->sequenceManager = $this->createMock(EntityManagerInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);

        $this->generator = new SequentialInvoiceNumberGenerator(
            $this->sequenceRepository,
            $this->sequenceFactory,
            $this->sequenceManager,
            $this->clock,
            1,
            9,
        );
    }

    /** @test */
    public function it_implements_invoice_number_generator_interface(): void
    {
        $this->assertInstanceOf(InvoiceNumberGenerator::class, $this->generator);
    }

    /** @test */
    public function it_generates_invoice_number(): void
    {
        $sequence = $this->createMock(InvoiceSequenceInterface::class);

        $dateTime = new \DateTimeImmutable('now');
        $this->clock->method('now')->willReturn($dateTime);

        $this->sequenceRepository->method('findOneBy')->with([])->willReturn($sequence);

        $sequence->method('getVersion')->willReturn(1);
        $sequence->method('getIndex')->willReturn(0);

        $this->sequenceManager
            ->expects($this->once())
            ->method('lock')
            ->with($sequence, LockMode::OPTIMISTIC, 1);

        $sequence
            ->expects($this->once())
            ->method('incrementIndex');

        $result = $this->generator->generate();

        $this->assertSame($dateTime->format('Y/m') . '/000000001', $result);
    }

    /** @test */
    public function it_generates_invoice_number_when_sequence_is_null(): void
    {
        $sequence = $this->createMock(InvoiceSequenceInterface::class);

        $dateTime = new \DateTimeImmutable('now');
        $this->clock->method('now')->willReturn($dateTime);

        $this->sequenceRepository->method('findOneBy')->with([])->willReturn(null);

        $this->sequenceFactory->method('createNew')->willReturn($sequence);

        $this->sequenceManager
            ->expects($this->once())
            ->method('persist')
            ->with($sequence);

        $sequence->method('getVersion')->willReturn(1);
        $sequence->method('getIndex')->willReturn(0);

        $this->sequenceManager
            ->expects($this->once())
            ->method('lock')
            ->with($sequence, LockMode::OPTIMISTIC, 1);

        $sequence
            ->expects($this->once())
            ->method('incrementIndex');

        $result = $this->generator->generate();

        $this->assertSame($dateTime->format('Y/m') . '/000000001', $result);
    }
}
