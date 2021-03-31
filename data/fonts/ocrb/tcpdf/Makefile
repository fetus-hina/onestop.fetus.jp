FONT_DATA := \
	vendor/tecnickcom/tcpdf/fonts/ocrb_aizu_1_1.ctg.z \
	vendor/tecnickcom/tcpdf/fonts/ocrb_aizu_1_1.php \
	vendor/tecnickcom/tcpdf/fonts/ocrb_aizu_1_1.z

.PHONY: all
all: $(FONT_DATA)

.PHONY: clean
clean:
	rm -rf vendor

$(FONT_DATA): ../OCRB_aizu_1_1.ttf vendor
	vendor/tecnickcom/tcpdf/tools/tcpdf_addfont.php -i $<

vendor: composer.lock composer.phar
	./composer.phar install --prefer-dist
	@touch $@

composer.phar:
	curl -fsSL 'https://getcomposer.org/installer' | php -- --stable
