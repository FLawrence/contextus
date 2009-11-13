import random

_shapes = { "d": "diamond",
			"l": "box",
			"c": "hexagon",
			"r": "circle"
			}

_good_colours = [ "white", "cyan", "green" ]
_bad_colours = [ "yellow", "red" ]

_bad_colours.reverse()
_colours = _good_colours + _bad_colours

class ParseException(Exception):
	def __init__(self, *args, **kargs):
		Exception.__init__(self, *args, **kargs)

class Node(object):
	"""Represents a node in the graph, including an ID, an optional
	name, the type of the node (combat, decision, random), and the
	nodes it links to.
	"""

	def __init__(self, line):
		self.id = ""
		self.name = ""
		self.type = ""
		self.rdup = 0
		self.goodness = 0
		self.deflinks = []
		self.graphs = []

		goodness = ""
		nextlink = ""
		
		state = "id"
		for c in line:
			if state == "id":
				if c == "=":
					state = "name"
				elif c.isspace():
					state = "type"
				elif c.isdigit():
					self.id += c
				else:
					raise ParseException("Parse error: non-digit paragraph ID")
			elif state == "name":
				if c == '"':
					state = "namestring"
				elif c.isspace():
					state = "type"
				else:
					self.name += c
			elif state == "namestring":
				if c == '"':
					state = "name"
				else:
					self.name += c
			elif state == "type":
				if c in "dDcClL":
					self.type = c.lower()
				elif c in "rR":
					self.type = c.lower()
					self.deflinks = [ self.id ]
					self.id += "r"
					self.name = " "
				elif c == "-":
					self.goodness -= 1
				elif c == ":" and self.type == "r":
					self.rdup += 1
				elif c == "+":
					self.goodness += 1
				elif c == "/":
					state = "goodness"
				elif c.isspace():
					state = "links"
				else:
					raise ParseException("Parse error: unidentified type")
			elif state == "goodness":
				if c.isdigit() or (c == "-" and goodness == ""):
					goodness += c
				elif c.isspace():
					state = "links"
				else:
					raise ParseException("Non-integer goodness")
			elif state == "links":
				if c.isspace():
					self.deflinks.append(nextlink)
					nextlink = ""
				elif c.isdigit() or c == 'r':
					nextlink += c
				else:
					raise ParseException("Parse error: non-digit link ID")

		if goodness != "":
			self.goodness = int(goodness)
		if nextlink != "":
			self.deflinks.append(nextlink)
		if self.type == "r" and self.rdup > 0:
			self.id += str(self.rdup)

	def set_links(self, nodes):
		self.links = []
		for n in self.deflinks:
			self.links.append(nodes[n])

	def dot(self):
		shape = _shapes[self.type]
		fill = _colours[self.goodness]
		label = ""
		if self.name != "":
			label = ',label="%s"' % (self.name,)
		rv = "n%s [shape=%s,fillcolor=%s%s];" % (self.id, shape, fill, label)
			
		return rv

	def dot_arcs(self):
		rv = ""
		for t in self.links:
			rv += "n%s -> n%s;\n" % (self.id, t.id)
		return rv

	def __str__(self):
		return '%s="%s" %s/%d %s' % (self.id, self.name, self.type, self.goodness, " ".join(self.links))


class Graph(object):
	def __init__(self, line):
		self.id = ""
		self.defcells = []
		self.defgraphs = []
		self.parents = []

		newid = ""
		groupid = ""
		
		state = "id"
		
		for c in line:
			if state == "id":
				if c == "[":
					pass
				elif c == "=":
					state = "list"
				else:
					self.id += c
			elif state == "list":
				if c == "[":
					state = "group"
				elif c == "]":
					state = "complete"
				elif c.isspace():
					self.defcells.append(newid)
					newid = ""
				elif c.isdigit() or c == "r":
					newid += c
				else:
					raise ParseException("Parse error: non-integer group member")
			elif state == "group":
				if c == "]":
					self.defgraphs.append(groupid)
					groupid = ""
					state = "groupend"
				else:
					groupid += c
			elif state == "groupend":
				if c.isspace():
					state = "list"
				elif c == "]":
					state = "complete"
				else:
					raise ParseException("Parse error: junk after group name end")
			elif state == "complete":
				raise ParseException("Parse error: junk after group definition")
			else:
				raise ParseException("Unknown state!")

		if newid != "":
			self.defcells.append(newid)
		if groupid != "":
			self.defgraphs.append(groupid)

	def set_links(self, nodes, graphs):
		self.cells = []
		for n in self.defcells:
			self.cells.append(nodes[n])
			nodes[n].graphs.append(self)
			
		self.graphs = []
		for g in self.defgraphs:
			self.graphs.append(graphs[g])

	def dot(self, nodes, graphs, nodes_remaining, depth=0, drawn=set()):
		rv = "subgraph cluster_%s {\n" % (random.randint(0, 1<<31))
		rv += """label="%s";
		style=filled;
		fillcolor=grey%s0;
		color=transparent;
		""" % (self.id, 9-depth)
		
		for n in self.cells:
			rv += "%s\n" % (n.dot(),)
			nodes_remaining.remove(n.id)
		for g in self.graphs:
			rv += "%s\n" % (g.dot(nodes, graphs, nodes_remaining, depth+1, drawn),)
		rv += "}"
		return rv

	def __str__(self):
		return "[%s=%s]" % (self.id, " ".join(self.defcells + ["[%s]" % x for x in self.defgraphs]))
