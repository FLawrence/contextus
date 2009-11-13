#!/usr/bin/python

import sys

from node import Node, Graph, ParseException

nodes = {}
graphs = {}

# Read in the node data
i=0
for line in sys.stdin:
	i += 1
	if line.startswith("#") or line.isspace() or len(line) == 0:
		continue

	try:
		if line.startswith("["):
			g = Graph(line.strip())
			graphs[g.id] = g
			sys.stderr.write("Read graph " + str(g) + "\n")
		else:
			n = Node(line.strip())
			nodes[n.id] = n
	except ParseException, ex:
		sys.stderr.write("%s at line %s\n" % (ex, i))

# Set links from nodes to graphs
for i, g in graphs.iteritems():
	g.set_links(nodes, graphs)

# Set node links
for i, n in nodes.iteritems():
	n.set_links(nodes)

# Work out which graphs have which parents
gcandidates = []
for i, g in graphs.iteritems():
	for dg in g.defgraphs:
		graphs[dg].parents.append(g)

for i, g in graphs.iteritems():
	if len(g.parents) == 0:
		gcandidates.append(i)

nodes_remaining = set(nodes.keys())

print """digraph foo {
size="7,100!";
page="7.75,11";
margin=0.2;
node [shape=diamond,style=filled,fillcolor=white];

"""

# Process the node data: first, print the nodes and graphs themselves
for g in gcandidates:
	sys.stderr.write("Outputting top-level graph " + str(g) + "\n")
	print graphs[g].dot(nodes, graphs, nodes_remaining)
for n in nodes_remaining:
	print nodes[n].dot()

print

# Second, print the node connections
for n in nodes.itervalues():
	print n.dot_arcs()
	
print "}"
