FONT_TARGETS := \
	ocrb_aizu_1_1.ctg.z \
	ocrb_aizu_1_1.php \
	ocrb_aizu_1_1.z 
TARGETS := OCRB_aizu_1_1.ttf $(FONT_TARGETS)

.PHONY: all
all: $(TARGETS)

.PHONY: clean
clean:
	rm -rf $(FONT_TARGETS)
	make -C tcpdf clean

.PHONY: dist-clean
dist-clean: clean
	rm -rf $(TARGETS)

OCRB_aizu_1_1.ttf:
	curl -fsSL -o $@ 'https://www.city.aizuwakamatsu.fukushima.jp/_files/00155090/OCRB_aizu_1_1.ttf'

$(FONT_TARGETS): tcpdf
	cp tcpdf/vendor/tecnickcom/tcpdf/fonts/$@ $@
	@touch $@

.PHONY: tcpdf
tcpdf:
	make -C $@
