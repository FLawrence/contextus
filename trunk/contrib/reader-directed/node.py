import sys
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

def parse_node(line):
	"""Parse a line of input and return a node object"""
	nid = ""
	name = ""
	ntype = ""
	goodness = ""
	ngoodness = 0
	nextlink = ""
	links = []
		
	state = "id"
	for c in line:
		if state == "id":
			if c == "=":
				state = "name"
			elif c.isspace():
				state = "type"
			elif c.isdigit():
				nid += c
			else:
				raise ParseException("Parse error: non-digit paragraph ID")
		elif state == "name":
			if c == '"':
				state = "namestring"
			elif c.isspace():
				state = "type"
			else:
				name += c
		elif state == "namestring":
			if c == '"':
				state = "name"
			else:
				name += c
		elif state == "type":
			if c in "dDcClL":
				ntype = c.lower()
			elif c == "-":
				ngoodness -= 1
			elif c == "+":
				ngoodness += 1
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
				links.append(nextlink)
				nextlink = ""
			elif c.isdigit():
				nextlink += c
			else:
				raise ParseException("Parse error: non-digit link ID")

	if goodness != "":
		ngoodness = int(goodness)
	if nextlink != "":
		links.append(nextlink)

	return Node(nid, ntype, name, ngoodness, links)

class Node(object):
	"""Represents a node in the graph, including an ID, an optional
	name, the type of the node (combat, decision, random), and the
	nodes it links to.
	"""

	def __init__(self, nid, ntype="d", name=" ", ngoodness=0, links=[]):
		self.id = nid
		self.name = name
		self.type = ntype
		self.goodness = ngoodness
		self.deflinks = links
		self.graphs = []
		self.links = []

	def set_links(self, nodes):
		self.links = []
		for n in self.deflinks:
			self.links.append(nodes[n])

	def dot(self, graph=None):
		if self.type == "r":
			return "n%s [shape=circle,fillcolor=white,fixedsize=true,width=0.2,height=0.2,label=\" \"];\n" % (self.id,)

		shape = _shapes[self.type]
		fill = _colours[self.goodness]
		label = ""
		if self.name != "":
			label = ',label="%s"' % (self.name,)
		rv = "n%s [shape=%s,fillcolor=%s%s];\n" % (self.id, shape, fill, label)
		
		return rv

	def dot_arcs(self, graph=None):
		inside = ""
		outside = ""
		for t in self.links:
			arc = "n%s -> n%s;\n" % (self.id, t.id)
			if graph is None or graph in t.graphs:
				inside += arc
			else:
				outside += arc
		return (inside, outside)

	def replace_link(self, old, new):
		"""Replace links to "old" with links to "new"."""
		try:
			i = self.deflinks.index(old.id)
			self.deflinks[i] = new.id
		except ValueError:
			pass

		try:
			i = self.links.index(old)
			self.links[i] = new
		except ValueError:
			pass

	def __repr__(self):
		return '%s="%s" %s/%d %s' % (self.id, self.name, self.type, self.goodness, " ".join(self.deflinks))


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
				elif c.isdigit():
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

	def split_nodes(self, depth=0):
		# Count the number of external destinations that we have in
		# this group, and generate new aggregator nodes within the
		# group if we need to
		sys.stderr.write("[%d] Splitting nodes for graph %s\n" % (depth, self.id,))
		
		destinations = {}
		dmap = {}
		all_nodes = self.all_nodes()
		sys.stderr.write("[%d] Graph contains nodes: %s\n" % (depth, " ".join([n.id for n in all_nodes])))
		for n in all_nodes:
			for d in n.links:
				if d not in all_nodes:
					destinations.setdefault(d.id, []).append(n)
					dmap[d.id] = d

		sys.stderr.write("[%d] Destinations: %s\n" % (depth, str(destinations)))

		for d, sources in destinations.iteritems():
			if len(sources) > 1:
				# If we have more than one source within the group
				# going to this external destination,

				# create a new internal destination
				sys.stderr.write("[%d] Splitting node %s:\n" % (depth, d))
				newname = "%s_%s" % (d, random.randint(0, 1<<31))
				newnode = Node(newname, "r")
				self.defcells.append(newnode.id)
				self.cells.append(newnode)
				newnode.deflinks.append(d)
				newnode.links.append(dmap[d])
				sys.stderr.write("[%d] New node is %s (dot='%s')\n" % (depth, str(newnode), newnode.dot()))

				# Then redirect all of the sources to that one instead
				for s in sources:
					sys.stderr.write("[%d] Updating links for %s\n" % (depth, s.id))
					s.replace_link(dmap[d], newnode)

		# Finally, do the same again for all of our subgraphs
		for g in self.graphs:
			sys.stderr.write("[%d] Dropping into subgraph %s\n" % (depth, str(g)))
			g.split_nodes(depth+1)

		sys.stderr.write("[%d] Done\n" % (depth,))


	def dot(self, nodes_remaining, depth=0, drawn=set()):
		rv = "subgraph cluster_%s {\n" % (random.randint(0, 1<<31))
		rv += """label="%s";
		style=filled;
		fillcolor=grey%s0;
		color=transparent;
		""" % (self.id, 9-depth)

		# Draw the nodes in this graph
		oarcs = ""
		for n in self.cells:
			rv += "%s\n" % (n.dot(),)
			inside, outside = n.dot_arcs(self)
			rv += inside
			oarcs += outside
			try:
				nodes_remaining.remove(n.id)
			except KeyError:
				pass
		# Draw the subgraphs in this graph
		for g in self.graphs:
			rv += "%s\n" % (g.dot(nodes_remaining, depth+1, drawn),)
		rv += "}\n"
		rv += "# Arcs outside this group:\n"
		rv += oarcs
		rv += "# - done\n"
		return rv

	def all_nodes(self):
		rv = []
		rv += self.cells
		for g in self.graphs:
			rv += g.all_nodes()
		return rv

	def __str__(self):
		return "[%s=%s]" % (self.id, " ".join(self.defcells + ["[%s]" % x for x in self.defgraphs]))
