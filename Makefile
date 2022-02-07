.PHONY: all config-files setup setup-db resources clean

RESOURCES := \
	resources/css/site.css \
	resources/js/fakedata.js \
	resources/js/mynumber.js \
	resources/js/zipsearch.js \
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
	ln -s `which composer` $@
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

%.css: %.scss node_modules .browserslistrc
	npx sass $< | npx postcss --no-map --use autoprefixer --use cssnano -o $@

%.js: %.es node_modules .browserslistrc
	npx babel $< | npx terser -c -m -f ascii_only=true -o $@

web/favicon.ico:
	curl -o $@ -fsSL https://fetus.jp/favicon.ico

config/cookie-secret.php:
	php setup/config-cookie.php > $@

.PHONY: config/git-revision.php
config/git-revision.php:
	php setup/git-revison.php > $@

.PHONY: check-style
check-style: check-style-php check-style-js check-style-css

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
	npx semistandard --global=jQuery --global=bootstrap 'resources/**/*.es'

.PHONY: check-style-css
check-style-css: node_modules
	npx stylelint 'resources/**/*.scss'

.PHONY: test
test: composer.phar config-files vendor node_modules resources
	./tests/bin/yii migrate/fresh --interactive=0 --compact=1
	/usr/bin/env XDEBUG_MODE=coverage \
		./vendor/bin/codecept run unit \
			--coverage \
			--coverage-html=./web/coverage/ \
			--coverage-text=./runtime/coverage/coverage.txt \
			--coverage-xml=./runtime/coverage/coverage.xml

.browserslistrc:
	curl -fsSL -o $@ 'https://raw.githubusercontent.com/twbs/bootstrap/v5.1.0/.browserslistrc'

bin/dep:
	curl -fsSL -o $@ 'https://deployer.org/releases/v6.8.0/deployer.phar'
	chmod +x $@
