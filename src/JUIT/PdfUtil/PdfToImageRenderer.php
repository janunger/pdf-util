<?php

namespace JUIT\PdfUtil;

use Symfony\Component\Process\Process;

class PdfToImageRenderer
{
    /**
     * @var string
     */
    private $ghostscriptBinaryPath;

    /**
     * @var string
     */
    private $outputDir;

    public function __construct($ghostscriptBinaryPath, $outputDir)
    {
        $this->ghostscriptBinaryPath = $ghostscriptBinaryPath;
        $this->outputDir = $outputDir;
    }

    /**
     * @param \SplFileInfo $pdfFileInfo
     * @param int|null $firstPage
     * @param int|null $lastPage
     * @throws \RuntimeException
     * @return \SplFileInfo[]
     */
    public function render(\SplFileInfo $pdfFileInfo, $firstPage = null, $lastPage = null)
    {
        $outputFileNamePattern = $this->createOutputFileNamePattern($pdfFileInfo);
        $shellCommand = $this->createShellCommand($pdfFileInfo, $outputFileNamePattern, $firstPage, $lastPage);

        $process = new Process($shellCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        return $this->extractOutputFileInfos($process->getOutput(), $outputFileNamePattern);
    }

    /**
     * @param \SplFileInfo $pdfFile
     * @param int $pageNumber
     * @return \SplFileInfo
     */
    public function renderSinglePage(\SplFileInfo $pdfFile, $pageNumber = 1)
    {
        $files = $this->render($pdfFile, $pageNumber, $pageNumber);

        return $files[0];
    }

    /**
     * @param \SplFileInfo $pdfFile
     * @param string $outputFileNamePattern
     * @param int|null $firstPage
     * @param int|null $lastPage
     * @return string
     */
    private function createShellCommand(\SplFileInfo $pdfFile, $outputFileNamePattern, $firstPage, $lastPage)
    {
        $shellCommand =
            $this->ghostscriptBinaryPath . " "
            . "-dSAFER -dBATCH -dNOPAUSE "
            . "-sDEVICE=png16m "
            . "-dTextAlphaBits=4 "
            . "-dGraphicsAlphaBits=4 "
            . "-dMaxBitmap=500000000 "
        ;

        if (null !== $firstPage) {
            $shellCommand .= "-dFirstPage=$firstPage ";
        }
        if (null !== $lastPage) {
            $shellCommand .= "-dLastPage=$lastPage ";
        }

        $shellCommand .=
            "-r200 "
            . "-sOutputFile=" . escapeshellarg($outputFileNamePattern) . " "
            . escapeshellarg($pdfFile->getRealPath())
        ;

        return $shellCommand;
    }

    /**
     * @param \SplFileInfo $pdfFile
     * @return string
     */
    private function createOutputFileNamePattern(\SplFileInfo $pdfFile)
    {
        $fileName = $pdfFile->getBasename();
        $fileNameWithoutExtension = substr($fileName, 0, strrpos($fileName, '.'));

        return $this->outputDir . '/' . $fileNameWithoutExtension . '_%d.png';
    }

    /**
     * @param string $result
     * @param string $outputFileNamePattern
     * @return \SplFileInfo[]
     */
    private function extractOutputFileInfos($result, $outputFileNamePattern)
    {
        $lines = explode("\n", $result);
        $fileInfos = array();
        $pageNumber = 1;

        foreach ($lines as $line) {
            if (!preg_match('/^Page \d+$/', $line)) {
                continue;
            }

            // GS does NOT use the actual page number in its filenames. It starts with 1 for the first rendered page.
            $filePath = sprintf($outputFileNamePattern, $pageNumber);
            $fileInfos[] = new \SplFileInfo($filePath);

            $pageNumber++;
        }

        return $fileInfos;
    }
}
