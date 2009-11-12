#!/usr/bin/python

import xml.etree.ElementTree as ET
import rdflib
import re
import urlparse
import sys
import getopt

from rdflib import Namespace
from rdflib import Literal
from rdflib import BNode
from rdflib import URIRef
from rdflib import RDF
from rdflib import ConjunctiveGraph

foaf = Namespace("http://xmlns.com/foaf/0.1/")
dc = Namespace("http://purl.org/dc/elements/1.1/")
dbpedia = Namespace("http://dbpedia.org/resource/")
ome = Namespace("http://purl.org/ontomedia/core/expression#")
omb = Namespace("http://purl.org/ontomedia/ext/common/being#")
oml = Namespace("http://signage.ecs.soton.ac.uk/ontologies/location#")

#### The following nabbed from RDFaParser to handle CURIEs

_urifixer = re.compile('^([A-Za-z][A-Za-z0-9+-.]*://)(/*)(.*?)')

def _urljoin(base, uri):
	uri = _urifixer.sub(r'\1\3', uri)
	return urlparse.urljoin(base, uri)

def extractCURIEorURI(graph, resource):
	if(len(resource) > 0 and resource[0] == "[" and resource[-1] == "]"):
		resource = resource[1:-1]

	# resolve prefixes
	# TODO: check whether I need to reverse the ns_contexts
	if(resource.find(":") > -1):
		rpre,rsuf = resource.split(":", 1)
		for prefix, ns in graph.namespaces(): 
			if prefix == rpre:
				resource = ns + rsuf

	# TODO: is this enough to check for bnodes?
	if(len(resource) > 0 and resource[0:2] == "_:"):
		return BNode(resource[2:])

	return URIRef(resolveURI(resource))

def resolveURI(uri):
	return _urljoin('' or '', uri)

#### End nabbage

def usage():
	print "Usage: ./tei2onto.py [-t|--teifile=]teifile.xml [-n|--namespace=]http://mynamespace.com/"
	return

def convert(teifile, namespace):
	#graph_uri = "http://contextus.net/resource/blue_velvet/"
	ns = Namespace(namespace)

	graph = ConjunctiveGraph()
	graph.load(teifile, format="rdfa")

	tree = ET.parse(teifile)
	cast = dict()
	castItems = tree.findall('/castList/castItem')
	for castItem in castItems:
		actorNode = castItem.find('actor')
		roleNode = castItem.find('role')
		id = roleNode.get("{http://www.w3.org/XML/1998/namespace}id")

		actor = None
		role = None

		# Check to see if we already have an entry
		if(roleNode.get("about")):
			role = extractCURIEorURI(graph, roleNode.get("about"))
			cast[id] = role
			graph.add((role, RDF.type, omb['Character']))
		
		if(actorNode.get("about")):
			actor = extractCURIEorURI(graph, actorNode.get("about"))
			graph.add((actor, RDF.type, omb['Being']))

		if actor and role:
			graph.add((actor, omb['portrays'], role))
			graph.add((role, omb['portrayed-by'], actor))

	eventCount = 1
	prior_event = None
	sceneItems = tree.findall('/div1')
	for sceneItem in sceneItems:
		
		# Work out the location of this scene
		location = None
		stageItems = sceneItem.findall("stage")
		for stageItem in stageItems:
			if stageItem.get("type") == "location":
				# The RDFa parser doesn't handle the type - so we can grab that here.
				type = extractCURIEorURI(graph, stageItem.get("typeof"))
				location = extractCURIEorURI(graph, stageItem.get("about"))
				graph.add((location, RDF.type, type))

		speechItems = sceneItem.findall('sp')

		# Work out a list of all cast in this scene
		sceneCast = list()
		for speechItem in speechItems:
			id = speechItem.get("who")
			sceneCast.append(cast[id[1:]])

		# Build up the events
		for speechItem in speechItems:
			event = ns['event/'+str(eventCount)]
			eventCount = eventCount + 1
			graph.add((event, RDF.type, ome['Social']))
			graph.add((event, oml['is-located-in'], location))
				
			id = speechItem.get("who")
			castUri = cast[id[1:]]
			graph.add((event, ome['has-subject-entity'], castUri))
			for castMember in sceneCast:
				graph.add((event, ome['involves'], castMember))
				graph.add((castMember, ome['involved-in'], event))

			refItems = speechItem.findall("l/rs")
			for refItem in refItems:
				about = extractCURIEorURI(graph, refItem.get("about"))
				graph.add((event, ome["refers-to"], about))

			if(prior_event):
				graph.add((event, ome['follows'], prior_event))
				graph.add((prior_event, ome['precedes'], event))


			prior_event = event

	print graph.serialize(format='n3')

def main(argv):
	try:
		opts, args = getopt.getopt(argv, "ht:n:", ["help", "teifile=", "namespace="])
	except getopt.GetoptError:
		usage()
		sys.exit(2)

	teifile = None
	namespace = None
	for o,a in opts:
		if o in ("-t", "--teifile"):
			teifile = a
		elif o in ("-n", "--namespace"):
			namespace = a
		else:
			usage()
			sys.exit(2)

	if teifile == None or namespace == None:
		usage()
		sys.exit(2)

	convert(teifile, namespace)

if __name__ == "__main__":
	main(sys.argv[1:])

