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

use Knp\Snappy\GeneratorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\InvoicingPlugin\Generator\PdfOptionsGeneratorInterface;
use Sylius\InvoicingPlugin\Generator\TwigToPdfGenerator;
use Sylius\InvoicingPlugin\Generator\TwigToPdfGeneratorInterface;
use Twig\Environment;

final class TwigToPdfGeneratorTest extends TestCase
{
    private Environment $twig;

    private GeneratorInterface $pdfGenerator;

    private PdfOptionsGeneratorInterface $pdfOptionsGenerator;

    private TwigToPdfGenerator $generator;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->pdfGenerator = $this->createMock(GeneratorInterface::class);
        $this->pdfOptionsGenerator = $this->createMock(PdfOptionsGeneratorInterface::class);

        $this->generator = new TwigToPdfGenerator(
            $this->twig,
            $this->pdfGenerator,
            $this->pdfOptionsGenerator,
        );
    }

    /** @test */
    public function it_is_twig_to_pdf_generator_interface(): void
    {
        $this->assertInstanceOf(TwigToPdfGeneratorInterface::class, $this->generator);
    }

    /** @test */
    public function it_generates_pdf_from_twig_template(): void
    {
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('template.html.twig', ['figcaption' => 'Swans', 'imgPath' => 'located-path/swans.png'])
            ->willReturn('<html>I am a pdf file generated from twig template</html>');

        $this->pdfOptionsGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn(['allow' => ['allowed_file_in_knp_snappy_config.png', 'located-path/swans.png']]);

        $this->pdfGenerator
            ->expects($this->once())
            ->method('getOutputFromHtml')
            ->with(
                '<html>I am a pdf file generated from twig template</html>',
                ['allow' => ['allowed_file_in_knp_snappy_config.png', 'located-path/swans.png']],
            )
            ->willReturn('PDF FILE');

        $result = $this->generator->generate('template.html.twig', ['figcaption' => 'Swans', 'imgPath' => 'located-path/swans.png']);

        $this->assertSame('PDF FILE', $result);
    }
}
