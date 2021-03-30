.PHONY: all config-files setup setup-db resources clean

RESOURCES := \
	web/css/site.css \
	web/js/fakedata.js \
	web/js/mynumber.js \
	web/js/polyfill.js \
	web/js/zipsearch.js

.PHONY: all
all: setup

.PHONY: config-files
config-files: config/cookie-secret.php

.PHONY: setup
setup: composer.phar config-files vendor node_modules setup-db resources

.PHONY: setup-db
setup-db: composer.phar config-files vendor
	./yii migrate/up --interactive=0

composer.phar:
	curl -fsSL 'https://getcomposer.org/installer' | php -- --stable

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
		composer.phar \
		node_modules \
		vendor

.PHONY: resources
resources: $(RESOURCES)

%.css: %.scss node_modules
	npx sass $< | \
		npx postcss --use autoprefixer | \
		npx cleancss | \
		cat > $@

%.js: %.es node_modules
	npx babel $< | npx uglifyjs --compress --mangle > $@

config/cookie-secret.php:
	php setup/config-cookie.php > $@

.PHONY: check-style
check-style: check-style-php

.PHONY: check-style-php
check-style-php: check-style-phpcs

.PHONY: check-style-phpcs
check-style-phpcs: vendor
	./vendor/bin/phpcs -p
