<?php

/** @var $loader \Composer\Autoload\ClassLoader */
$loader = require __DIR__ . '/../vendor/autoload.php';

$loader->add('JUIT\PdfUtil\Test\\', __DIR__ . '/');

\JUIT\PdfUtil\Test\EndToEndTestCase::setTempDir(__DIR__ . '/var/tmp');
