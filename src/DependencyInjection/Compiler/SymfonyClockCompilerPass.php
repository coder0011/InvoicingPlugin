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

namespace Sylius\InvoicingPlugin\DependencyInjection\Compiler;

use Symfony\Component\Clock\Clock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class SymfonyClockCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasParameter('clock')) {
            return;
        }

        $container->setDefinition('clock', new Definition(Clock::class));
    }
}
