.PHONY: all config-files setup setup-db resources

RESOURCES := \
	web/css/site.css \
	web/js/mynumber.js

NPMBIN := $(shell npm bin)

all: setup

config-files: config/cookie-secret.php

setup: composer.phar config-files vendor node_modules setup-db resources

setup-db: composer.phar config-files vendor
	./yii migrate/up

composer.phar:
	curl -sL 'https://getcomposer.org/installer' | php -- --stable
	touch -t 201601010000.00 $@

vendor: composer.lock composer.phar
	php composer.phar install --prefer-dist
	touch -r $< $@

composer.lock: composer.json composer.phar
	php composer.phar update -vvv
	touch -r $< $@

node_modules: package-lock.json
	npm install
	@touch $@

package-lock.json: package.json
	npm update
	@touch $@

resources: $(RESOURCES)

%.css: %.scss node_modules
	npx node-sass $< | \
		npx postcss --use autoprefixer | \
		npx cleancss | \
		cat > $@

%.js: %.es node_modules
	npx babel --presets=latest $< | \
		npx uglifyjs --compress --mangle | \
		cat > $@

config/cookie-secret.php:
	php setup/config-cookie.php > $@
