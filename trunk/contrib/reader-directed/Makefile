SOURCES := starship-traveller forest-of-doom city-of-thieves
PDFTARGETS := $(addsuffix .pdf,$(SOURCES))
PSTARGETS := $(addsuffix .ps,$(SOURCES))

all: $(PDFTARGETS) $(PSTARGETS)

.PRECIOUS: %.dot
#.GOLLUM: birthday-present

%.dot: %.raw convert.py node.py
	./convert.py <$< >$@

%.pdf: %.ps
	ps2pdf $<

%.ps: %.dot
	dot -Tps $< >$@.tmp
	psselect -r $@.tmp $@
	rm $@.tmp
