# Makefile
# 
# Common Makefile for web projects.
# 
# @author		Michał Pałys-Dudek <michal@michaldudek.pl>
# @link			TBD
# @version		0.1.1
# @date			24.09.2015
# ---------------------------------------------------------------------------
# 
# MD Symfony Project.

.PHONY: help all install install_dev clear assets warmup run prepublish postpublish css js watch test lint qa report docs noop composer composer_dev workers workers_start assets assets_install cache cache_file cache_app knit_indexes phpunit phpcs phpcs_test phpcs_fix phpcs_test_fix phpmd jslint npm_dev

# Variables
# ---------------------------------------------------------------------------

# Current path.
CWD=`pwd`

# Previous release path.
PREVIOUS_PATH?=$(CWD)

# Current (new) release path.
CURRENT_PATH?=$(CWD)

# Targets
# ---------------------------------------------------------------------------

help:
	@echo ""
	@echo "Following commands are available:"
	@echo "(this is summary of the main commands,"
	@echo " but for more fine-grained commands see the Makefile)"
	@echo ""
	@echo "     make help           : This info."
	@echo ""
	@echo " Installation:"
	@echo "     make install        : Installs all dependencies for production."
	@echo "     make install_dev    : Installs all dependencies (including dev dependencies)"
	@echo "     make clear          : Clears any build artifacts, caches, installed packages, etc."
	@echo "     make assets         : Installs / prepares / builds the frontend assets."
	@echo "     make warmup         : Warms up the application."
	@echo "     make run            : Runs / restarts the application."
	@echo "     make stop           : Stops the application."
	@echo ""
	@echo " Deployment:"
	@echo "     make prepublish     : Runs everything that needs to be ran before publishing the app."
	@echo "     make postpublish    : Runs everything that needs to be ran after the app has been published."
	@echo ""
	@echo " Development:"
	@echo "     make css            : Build CSS."
	@echo "     make js             : Build JavaScript."
	@echo "     make watch          : Watch files for changes and trigger appropriate tasks on changes."
	@echo ""
	@echo " Quality Assurance:"
	@echo "     make test           : Run tests."
	@echo "     make lint           : Lint the code."
	@echo "     make qa             : Run tests, linters and any other quality assurance tool."
	@echo "     make report         : Build reports about the code / the project / the app."
	@echo "     make docs           : Build docs."
	@echo ""

# alias for help
all: help

# Installation
# ---------------------------------------------------------------------------

# Installs all dependencies for production.
install: composer build

# Installs all dependencies (including dev dependencies)
install_dev: composer_dev npm_dev build

# Updates all dependencies (including dev dependencies)
update: composer_update npm_update build

# Builds the application
build: bootstrap_php assets_install

# Clears any build artifacts, caches, installed packages, etc.
clear: cache

# Warms up the application.
warmup: noop

# Runs / restarts the application.
run: vagrant

# Stops the application
stop: vagrant_stop

# Deployment
# ---------------------------------------------------------------------------

# Runs everything that needs to be ran before publishing the app.
prepublish: install

# Runs everything that needs to be ran after the app has been published.
postpublish: warmup

# Development
# ---------------------------------------------------------------------------

# Build CSS.
css:
	@gulp less

# Build JavaScript.
js:
	@gulp js

# Watch files for changes and trigger appropriate tasks on changes.
watch:
	@gulp watch

# Quality Assurance
# ---------------------------------------------------------------------------

# Run tests.
test: phpunit

# Lint the code.
lint: phpcs phpcs_test phpmd jslint

# Run tests, linters and any other quality assurance tool.
qa: security test lint

# Build reports about the code / the project / the app.
report: phpunit_report

# Build docs.
docs: noop

# Misc
# ---------------------------------------------------------------------------

noop:
	@echo "Nothing to do."

# End of interface
# ----------------

# ---------------------------------------------------------------------------
# App specific commands
# ---------------------------------------------------------------------------

# install Composer dependencies for production
composer:
	composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# install Composer dependencies for development
composer_dev:
	composer install --no-interaction --prefer-dist

# update Composer dependencies
composer_update:
	composer update

bootstrap_php:
	php vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php "$(CWD)/.cache" "$(CWD)/src"

# install NPM dependencies for development
npm_dev:
	npm install

# update NPM dependencies
npm_update:
	npm update

# installs application assets
assets_install:
	php console assets:install --no-debug

# clears known file cache
cache_file:
	rm -rf .cache

# clears app cache
cache_app:
	php console cache:clear --env=prod

# clears all caches
cache: cache_app cache_file

# check for security updates
security:
	php app/console security:check

# run the PHPUnit tests
phpunit:
	php ./vendor/bin/phpunit

phpunit_report:
	php ./vendor/bin/phpunit --coverage-html resources/coverage

# run PHPCS on the source code and show any style violations
phpcs:
	php ./vendor/bin/phpcs --standard="phpcs.xml" src

# run PHPCBF to auto-fix code style violations
phpcs_fix:
	php ./vendor/bin/phpcbf --standard="phpcs.xml" src

# run PHPCS on the test code and show any style violations
phpcs_test:
	php ./vendor/bin/phpcs --standard="phpcs.xml" tests

# run PHPCBF on the test code to auto-fix code style violations
phpcs_test_fix:
	php ./vendor/bin/phpcbf --standard="phpcs.xml" tests

# Run PHP Mess Detector on the source code
phpmd:
	php ./vendor/bin/phpmd src text ./phpmd.xml

# lint the JavaScript
jslint:
	gulp js:lint

# starts the web server 
vagrant:
	vagrant up

# stops the web server
vagrant_stop:
	vagrant suspend