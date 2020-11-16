.PHONY: all config-files setup setup-db resources clean

RESOURCES := \
	web/css/site.css \
	web/js/fakedata.js \
	web/js/mynumber.js \
	web/js/polyfill.js \
	web/js/zipsearch.js

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
	npm ci
	@touch $@

package-lock.json: package.json
	@rm -rf $@ node_modules
	npm update
	@touch $@

clean:
	rm -rf $(RESOURCES)

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
