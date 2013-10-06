README
======

Requirements
------------

PdfUtil currently only consists of a renderer that renders PDF to PNG files. It is tested on PHP 5.4.20, although it should work on earlier versions down to PHP 5.3.x. It is designed to run on Linux/Unix, as it issues bash commands and processes their results.

Required for normal operations:

* Ghostscript
* Symfony Process Component (for issuing bash commands)

Required for running the tests:

* Imagemagick (for comparing the output with what was expected)

Usage
-----
Render all pages in a PDF file:

```php
use JUIT\PdfUtil\PdfToImageRenderer;

$renderer = new PdfToImageRenderer('/path/to/ghostscript', '/path/to/output_dir');
$files = $renderer->render(new SplFileInfo('/path/to/my_file.pdf'));

foreach ($files as $file) {
	// Do something with $file->getPathname() ...
}
```

Render a single page from a multipage PDF file:

```php
use JUIT\PdfUtil\PdfToImageRenderer;

$renderer = new PdfToImageRenderer('/path/to/ghostscript', '/path/to/output_dir');
$pageNumber = 3;
$file = $renderer->renderSinglePage(new SplFileInfo('/path/to/my_file.pdf'), $pageNumber);

// Do something with $file->getPathname() ...
```

Render a range of pages in a PDF file:

```php
use JUIT\PdfUtil\PdfToImageRenderer;

$renderer = new PdfToImageRenderer('/path/to/ghostscript', '/path/to/output_dir');
$firstPage = 2;
$lastPage = 5;
$file = $renderer->render(new SplFileInfo('/path/to/my_file.pdf'), $firstPage, $lastPage);

foreach ($files as $file) {
	// Do something with $file->getPathname() ...
}
```

