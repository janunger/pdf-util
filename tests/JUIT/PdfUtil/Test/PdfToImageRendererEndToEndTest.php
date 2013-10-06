<?php

namespace JUIT\PdfUtil\Test;

use JUIT\PdfUtil\PdfToImageRenderer;
use Symfony\Component\Process\Process;

class PdfToImageRendererEndToEndTest extends EndToEndTestCase
{
    private $fixturesDir;

    protected function setUp()
    {
        parent::setUp();

        $this->fixturesDir = __DIR__ . '/fixtures';
    }

    /**
     * @return PdfToImageRenderer
     */
    private function createPdfToImageRenderer()
    {
        return new PdfToImageRenderer('/usr/bin/gs', self::getTempDir());
    }

    /** @test */
    public function render_png_from_single_page_pdf()
    {
        $referenceImagePath = escapeshellarg($this->fixturesDir . '/single_page_expected.png');
        $sut = $this->createPdfToImageRenderer();

        $renderedFiles = $sut->render(new \SplFileInfo($this->fixturesDir . '/single_page.pdf'));

        $renderedImagePath = $renderedFiles[0]->getPathname();
        $this->assertCount(1, $renderedFiles);
        $this->assertInstanceOf('\SplFileInfo', $renderedFiles[0]);
        $this->assertEquals(self::getTempDir() . '/single_page_1.png', $renderedImagePath);
        $this->assertImagesEqual($renderedImagePath, $referenceImagePath);
    }

    /** @test */
    public function render_all_pages_from_a_multi_page_pdf()
    {
        $sut = $this->createPdfToImageRenderer();

        $renderedFiles = $sut->render(new \SplFileInfo($this->fixturesDir . '/three_pages.pdf'));

        $this->assertCount(3, $renderedFiles);

        $i = 1;
        foreach ($renderedFiles as $renderedFile) {
            $renderedImagePath = $renderedFile->getPathname();

            $this->assertInstanceOf('\SplFileInfo', $renderedFile);

            $expectedImagePath = self::getTempDir() . '/three_pages_' . $i . '.png';
            $this->assertEquals($expectedImagePath, $renderedImagePath);

            $referenceImagePath = $this->fixturesDir . '/three_pages_' . $i . '_expected.png';
            $this->assertImagesEqual($renderedImagePath, $referenceImagePath);

            $i++;
        }
    }

    /** @test */
    public function render_first_page_from_a_multi_page_pdf()
    {
        $sut = $this->createPdfToImageRenderer();

        $renderedFile = $sut->renderSinglePage(new \SplFileInfo($this->fixturesDir . '/three_pages.pdf'));

        $this->assertInstanceOf('\SplFileInfo', $renderedFile);

        $renderedImagePath = $renderedFile->getPathname();
        $expectedImagePath = self::getTempDir() . '/three_pages_1.png';
        $this->assertEquals($expectedImagePath, $renderedImagePath);

        $referenceImagePath = $this->fixturesDir . '/three_pages_1_expected.png';
        $this->assertImagesEqual($renderedImagePath, $referenceImagePath);
    }

    /** @test */
    public function render_second_page_from_a_multi_page_pdf()
    {
        $sut = $this->createPdfToImageRenderer();

        $renderedFile = $sut->renderSinglePage(new \SplFileInfo($this->fixturesDir . '/three_pages.pdf'), 2);

        $this->assertInstanceOf('\SplFileInfo', $renderedFile);

        $renderedImagePath = $renderedFile->getPathname();
        // Ghostscript starts counting rendered pages on 1, no matter what page it starts rendering.
        $expectedImagePath = self::getTempDir() . '/three_pages_1.png';
        $this->assertEquals($expectedImagePath, $renderedImagePath);

        $referenceImagePath = $this->fixturesDir . '/three_pages_2_expected.png';
        $this->assertImagesEqual($renderedImagePath, $referenceImagePath);
    }

    /** @test */
    public function render_second_and_third_page_from_a_multi_page_pdf()
    {
        $sut = $this->createPdfToImageRenderer();

        $renderedFiles = $sut->render(new \SplFileInfo($this->fixturesDir . '/three_pages.pdf'), 2, 3);

        $this->assertCount(2, $renderedFiles);

        // Ghostscript starts counting rendered pages on 1, no matter what page it starts rendering.
        $i = 1;
        foreach ($renderedFiles as $renderedFile) {
            $renderedImagePath = $renderedFile->getPathname();

            $this->assertInstanceOf('\SplFileInfo', $renderedFile);

            $expectedImagePath = self::getTempDir() . '/three_pages_' . $i . '.png';
            $this->assertEquals($expectedImagePath, $renderedImagePath);

            $referenceImagePath = $this->fixturesDir . '/three_pages_' . ($i + 1) . '_expected.png';
            $this->assertImagesEqual($renderedImagePath, $referenceImagePath);

            $i++;
        }
    }
}
