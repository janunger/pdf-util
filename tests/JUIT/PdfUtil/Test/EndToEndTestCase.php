<?php

namespace JUIT\PdfUtil\Test;

use Symfony\Component\Process\Process;

class EndToEndTestCase extends \PHPUnit_Framework_TestCase
{
    private static $tempDir = '';

    /**
     * @return string
     */
    public static function getTempDir()
    {
        return self::$tempDir;
    }

    /**
     * @param string $tempDir
     */
    public static function setTempDir($tempDir)
    {
        self::$tempDir = $tempDir;
    }

    protected function setUp()
    {
        $this->flushTempDir();
    }

    protected function flushTempDir()
    {
        $this->runShellCommand('rm -fr ' . self::getTempDir() . '/*');
    }

    protected function assertImagesEqual($renderedImagePath, $referenceImagePath)
    {
        $diffPdfPath = self::getTempDir() . '/diff.pdf';
        $diffBmpPath = self::getTempDir() . '/diff.bmp';

        $this->renderDiffPdf($renderedImagePath, $referenceImagePath, $diffPdfPath);
        $this->renderDiffBmp($diffBmpPath, $diffPdfPath);

        $this->assertEquals(
            '74ab373396b8c6dd4ca9322fd6edae66',
            $this->calculateHash($diffBmpPath),
            'Failed asserting that two images are equal.'
        );
    }

    /**
     * @param string $command
     * @return string
     * @throws \RuntimeException
     */
    protected function runShellCommand($command)
    {
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * @param string $renderedImagePath
     * @param string $referenceImagePath
     * @param string $diffPdfPath
     */
    protected function renderDiffPdf($renderedImagePath, $referenceImagePath, $diffPdfPath)
    {
        $this->runShellCommand(
            'compare -verbose -debug coder -log "%u %m:%l %e" '
            . $referenceImagePath
            . ' ' . $renderedImagePath
            . ' -compose src ' . $diffPdfPath
        );
    }

    /**
     * @param string $diffBmpPath
     * @param string $diffPdfPath
     */
    protected function renderDiffBmp($diffBmpPath, $diffPdfPath)
    {
        $this->runShellCommand(
            'gs -o ' . $diffBmpPath
            . ' -r72'
            . ' -g595x842'
            . ' -sDEVICE=bmp256'
            . ' ' . $diffPdfPath
        );
    }

    /**
     * @param string $filePath
     * @return string
     * @throws \RuntimeException
     */
    protected function calculateHash($filePath)
    {
        $output = $this->runShellCommand('md5sum ' . $filePath);

        $matches = array();
        if (!preg_match('/^([\da-f]{32}) /', $output, $matches)) {
            throw new \RuntimeException("Unexpected output: '$output'");
        }

        return $matches[1];
    }
}
