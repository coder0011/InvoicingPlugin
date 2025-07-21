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

namespace Tests\Sylius\InvoicingPlugin\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sylius\InvoicingPlugin\Model\InvoicePdf;

final class InvoicePdfTest extends TestCase
{
    public function test_it_has_filename(): void
    {
        $invoicePdf = new InvoicePdf('2018_01_0000002.pdf', 'pdf content');

        self::assertSame('2018_01_0000002.pdf', $invoicePdf->filename());
    }

    public function test_it_has_content(): void
    {
        $invoicePdf = new InvoicePdf('2018_01_0000002.pdf', 'pdf content');

        self::assertSame('pdf content', $invoicePdf->content());
    }

    public function test_it_has_full_path(): void
    {
        $invoicePdf = new InvoicePdf('2018_01_0000002.pdf', 'pdf content');
        $invoicePdf->setFullPath('/full/path/invoice.pdf');

        self::assertSame('/full/path/invoice.pdf', $invoicePdf->fullPath());
    }
}
