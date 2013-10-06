#!/bin/sh

rm -fr tests/var/reports/coverage/* && \
bin/phpunit --coverage-html tests/var/reports/coverage -c tests/phpunit.xml.dist tests
