<?php

namespace JUIT\PdfUtil\Test;

use JUIT\PdfUtil\PdfToImageRenderer;
use Symfony\Component\Process\Process;

class PdfToImageRendererEndToEndTest extends EndToEndTestCase
{
    /** @test */
    public function can_render_png_from_single_page_pdf()
    {
        $fixturesDir = __DIR__ . '/fixtures';
        $referenceImagePath = escapeshellarg($fixturesDir . '/single_page_expected.png');
        $sut = new PdfToImageRenderer(null, self::getTempDir());

        $renderedFiles = $sut->render(new \SplFileInfo($fixturesDir . '/single_page.pdf'));

        $renderedImagePath = $renderedFiles[0]->getPathname();
        $this->assertCount(1, $renderedFiles);
        $this->assertEquals(self::getTempDir() . '/single_page_1.png', $renderedImagePath);
        $this->assertImagesEqual($renderedImagePath, $referenceImagePath);
    }
}
