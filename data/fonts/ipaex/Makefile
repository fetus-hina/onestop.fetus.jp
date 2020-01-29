VERSION := 00201

TTF_FILES := \
	ipaexg.ttf \
	ipaexm.ttf

FONT_TARGETS := \
	$(TTF_FILES:.ttf=.ctg.z) \
	$(TTF_FILES:.ttf=.php) \
	$(TTF_FILES:.ttf=.z)

TARGETS := LICENSE README $(TTF_FILES) $(FONT_TARGETS)

.PHONY: all
all: $(TARGETS)

.PHONY: clean
clean:
	rm -rf ipafont.zip $(FONT_TARGETS)
	make -C tcpdf clean

.PHONY: dist-clean
dist-clean: clean
	rm -rf $(TARGETS)

$(TTF_FILES): ipafont.zip
	unzip -o -j $< "*/$@"
	@touch $@

LICENSE: ipafont.zip
	unzip -p $< "*/*License_Agreement_*.txt" > $@
	@touch $@

README: ipafont.zip
	unzip -p $< "*/Readme*.txt" > $@
	@touch $@

ipafont.zip:
	curl -fsSL -o $@ https://ipafont.ipa.go.jp/IPAexfont/IPAexfont$(VERSION).zip

$(FONT_TARGETS): tcpdf
	cp tcpdf/vendor/tecnickcom/tcpdf/fonts/$@ $@
	@touch $@

.PHONY: tcpdf
tcpdf:
	make -C $@
