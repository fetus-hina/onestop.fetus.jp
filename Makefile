.PHONY: all config-files setup setup-db resources

RESOURCES := \
	web/css/site.css

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

node_modules: package.json
	npm install
	touch -r $< $@

resources: $(RESOURCES)

%.css: %.scss node_modules
	$(NPMBIN)/node-sass $< | \
		$(NPMBIN)/postcss --use autoprefixer --autoprefixer.browsers 'last 2 versions,> 5%,firefox ESR' | \
		$(NPMBIN)/cleancss --skip-import | \
		cat > $@

%.js: %.es node_modules
	$(NPMBIN)/babel --presets=latest $< | \
		$(NPMBIN)/uglifyjs --compress --mangle | \
		cat > $@

config/cookie-secret.php:
	php setup/config-cookie.php > $@
