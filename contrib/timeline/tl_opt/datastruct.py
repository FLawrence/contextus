import random

class Character(object):
	def __init__(self, name, uri):
		self.name = name
		self.uri = uri
		self.events = []

	def __str__(self):
		return self.name

class Event(object):
	def __init__(self, eid, *params):
		self.name = eid
		self.char_names = list(params)
		self.char_names.sort()

	def __str__(self):
		return self.name

	def same_chars_as(self, other):
		return self.char_names == other.char_names

	def link_chars(self, characters):
		"""Create and link up the characters and events at all intersections"""
		self.chars = []
		for chn, ch in characters.iteritems():
			ce = ChEv(ch, self)
			if ch.name in self.char_names:
				ce.link = True
			self.chars.append(ce)
			ch.events.append(ce)
		self.corder = self.chars[:] # Take a copy in character order
		#random.shuffle(self.chars)
		#self.chars.sort(lambda a, b: -cmp(a.link, b.link))
		self.renumber()

	def renumber(self):
		"""Renumber the characters at this event to reflect their order"""
		for i, ce in enumerate(self.chars):
			ce.index = i

	def get_grouping_badness(self):
		"""Compute a badness value for the grouping of characters that
		actually appear in this event.

		Note that this measure is flat w.r.t. neighbour swaps. It
		probably shouldn't be.
		"""
		first = None
		last = None
		total = 0
		for i, ce in enumerate(self.chars):
			if ce.link:
				total += 1
				if first is None:
					first = i
				last = i
		if first is None:
			return 0
		return last - first + 1 - total

class ChEv(object):
	def __init__(self, ch, ev):
		self.char = ch
		self.event = ev
		self.index = None
		self.link = False # Does this character actually appear in this event?
