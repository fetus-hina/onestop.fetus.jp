TTF_FILES := ipaexg.ttf ipaexm.ttf

FONT_FILES := \
	$(TTF_FILES:.ttf=.ctg.z) \
	$(TTF_FILES:.ttf=.php) \
	$(TTF_FILES:.ttf=.z)

FONT_PATHS := $(addprefix vendor/tecnickcom/tcpdf/fonts/, $(FONT_FILES))

.PHONY: all
all: $(FONT_PATHS)

.PHONY: clean
clean:
	rm -rf vendor

vendor/tecnickcom/tcpdf/fonts/ipaexg.ctg.z: ../ipaexg.ttf vendor
	vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i $<

vendor/tecnickcom/tcpdf/fonts/ipaexm.ctg.z: ../ipaexm.ttf vendor
	vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i $<

%.php: %.ctg.z
%.z: %.ctg.z

composer.phar:
	curl -fsSL 'https://getcomposer.org/installer' | php -- --stable

vendor: composer.lock composer.phar
	./composer.phar install --prefer-dist
	@touch $@
