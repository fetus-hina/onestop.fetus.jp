.PHONY: all config-files setup setup-db resources clean

RESOURCES := \
	resources/js/fakedata.js \
	resources/js/mynumber.js \
	resources/js/zipsearch.js \
	web/favicon \
	web/favicon.ico

.PHONY: all
all: setup

.PHONY: config-files
config-files: config/cookie-secret.php config/git-revision.php

.PHONY: setup
setup: composer.phar config-files vendor node_modules setup-db resources

.PHONY: setup-db
setup-db: composer.phar config-files vendor
	./yii migrate/up --interactive=0

composer.phar:
ifeq (, $(shell which composer 2>/dev/null))
	curl -fsSL 'https://getcomposer.org/installer' | php -- --stable
else
	ln -sf `which composer` $@
endif

vendor: composer.lock composer.phar
	php composer.phar install --prefer-dist
	@touch $@

node_modules: package-lock.json
	npm clean-install
	@touch $@

.PHONY: clean
clean:
	rm -rf \
		$(RESOURCES) \
		.browserslistrc \
		composer.phar \
		node_modules \
		vendor

.PHONY: resources
resources: $(RESOURCES)

%.js: %.es node_modules .browserslistrc
	npx babel $< | npx terser -c -m -f ascii_only=true -o $@

web/favicon: node_modules
	ln -sf ../node_modules/@fetus-hina/fetus.css/dist/favicon $@
	@touch $@

web/favicon.ico: web/favicon
	ln -sf favicon/favicon.ico $@
	@touch $@

config/cookie-secret.php:
	php setup/config-cookie.php > $@

.PHONY: config/git-revision.php
config/git-revision.php:
	php setup/git-revison.php > $@

.PHONY: check-style
check-style: check-style-php check-style-js

.PHONY: check-style-php
check-style-php: check-style-phpcs check-style-phpstan

.PHONY: check-style-phpcs
check-style-phpcs: vendor
	./vendor/bin/phpcs -p

.PHONY: check-style-phpstan
check-style-phpstan: config-files vendor
	./vendor/bin/phpstan analyze --memory-limit=1G || true

.PHONY: check-style-js
check-style-js: node_modules
	npx semistandard 'resources/**/*.es'

.PHONY: test
test: composer.phar config-files vendor node_modules resources
	./tests/bin/yii migrate/fresh --interactive=0 --compact=1
	./vendor/bin/codecept run unit

.browserslistrc:
	curl -fsSL -o $@ 'https://raw.githubusercontent.com/twbs/bootstrap/v5.1.0/.browserslistrc'

bin/dep:
	curl -fsSL -o $@ 'https://deployer.org/releases/v6.8.0/deployer.phar'
	chmod +x $@
