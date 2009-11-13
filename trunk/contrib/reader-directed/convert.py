#!/usr/bin/python

import sys

from node import Graph, ParseException, parse_node

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
			n = parse_node(line.strip())
			nodes[n.id] = n
			sys.stderr.write("Read node " + str(n) + "\n")
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

# Split nodes if necessary
for g in gcandidates:
	graphs[g].split_nodes()

print """digraph foo {
size="7,100!";
page="7.75,11";
margin=0.2;
node [shape=diamond,style=filled,fillcolor=white];

"""

# Process the node data: print the graphs first, then any stragglers
for g in gcandidates:
	sys.stderr.write("Outputting top-level graph " + str(g) + "\n")
	print graphs[g].dot(nodes_remaining)
for n in nodes_remaining:
	print nodes[n].dot()
	print nodes[n].dot_arcs()[0]

print
	
print "}"
