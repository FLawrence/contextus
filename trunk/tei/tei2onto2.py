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
omj = Namespace("http://purl.org/ontomedia/ext/events/travel#")

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
	castItems = tree.findall('/text/body/div1/castList//castItem')
	for castItem in castItems:
		actorNode = castItem.find('actor')
		roleNode = castItem.find('role')
		id = roleNode.get("{http://www.w3.org/XML/1998/namespace}id")
		
		#print("Found castItem!")

		actor = None
		role = None

		# Check to see if we already have an entry
		if(roleNode != None and roleNode.get("about")):
			role = extractCURIEorURI(graph, roleNode.get("about"))
			cast[id] = role
			graph.add((role, RDF.type, omb['Character']))
			#print("Adding id " + id + " to " + role)
		
		if(actorNode != None and actorNode.get("about")):
			actor = extractCURIEorURI(graph, actorNode.get("about"))
			graph.add((actor, RDF.type, omb['Being']))

		if actor != None and role != None:
			graph.add((actor, omb['portrays'], role))
			graph.add((role, omb['portrayed-by'], actor))

	eventCount = 1
	prior_event = None
	sceneItems = tree.findall('/text/body/div1/div2')
	for sceneItem in sceneItems:
		
		#print("Found sceneItems!")
		
		# Work out the location of this scene
		location = None
		stageItems = sceneItem.findall("stage")
		for stageItem in stageItems:
			if stageItem.get("type") == "location":
				# The RDFa parser doesn't handle the type - so we can grab that here.
				if stageItem.get("typeof") and stageItem.get("about"):
					type = extractCURIEorURI(graph, stageItem.get("typeof"))
					location = extractCURIEorURI(graph, stageItem.get("about"))
					graph.add((location, RDF.type, type))
				elif stageItem.get("about"):
					type = extractCURIEorURI(graph, "[loc:Space]")
					location = extractCURIEorURI(graph, stageItem.get("about"))
					graph.add((location, RDF.type, type))		
					
				#print("Adding location type: " + type + " (" + location + ")")


		if cast:
			# Work out a list of all cast in a given section
			currentCast = list()
			previousCast = list()

		# Iterate through elements within stageItem
			# Find speaker events and add to list of current cast for inclusion in social event
			# Find reference events and add to ongoing social event ?
			# Find stage events
				# If event is an entrance then
					# create social event for people talking before entrance
					# create travel event i.e. entrance
					# add new arrival to current cast list
				# If event is exit event then
					# create social event for people talking before exit
					# create travel event i.e. exit
						# if leavers are not named directly the calculate who is leaving
					# remove leavers from current cast list
			# If reach end of scene then create social event with current cast list
			
			#Also need to check if social event before exit has same composition as social event after exit since then they should be merged
			
		event = ns['event/'+str(eventCount)]
		group = ns['group/'+str(eventCount)]				
					
					
		for node in sceneItem.getiterator():
			#print("Node: " + node.tag)	
			if node.tag == "sp":
				id = node.get("who")
				if id and cast:
					currentCast.append(cast[id[1:]])
			elif node.tag == "stage":
				if node.get("type") == "entrance":		
				
					# Add Social Event if there are people in the CurrentCast list
				
				
					# Add Travel Event
					graph.add((event, RDF.type, omj['Travel']))

					#print("Found entrence event!")
					if location:
						graph.add((event, ome['to'], location))		
						
					involved = stageItem.get("about")
					
					if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
						involved = involved[1:-1]
						
					chunks = involved.split()
					
					chunk_count = len(chunks)
					
					if chunk_count > 1:
						type = extractCURIEorURI(graph, "[omb:Group]")
						graph.add((group, RDF.type, type))		
					
					for chunk in chunks:
						striped = chunk.strip()
						peep = extractCURIEorURI(graph, striped)
						
						if chunk_count > 1:
							graph.add((group, ome['contains'], peep))
						else:
							graph.add((event, ome['has-subject-entity'], peep))
						
					if chunk_count > 1:
						graph.add((event, ome['has-subject-entity'], group))
	
					if(prior_event):
						graph.add((event, ome['follows'], prior_event))
						graph.add((prior_event, ome['precedes'], event))
	
					prior_event = event					

					eventCount = eventCount + 1
					event = ns['event/'+str(eventCount)]
					group = ns['group/'+str(eventCount)]				
		
	

				
	print graph.serialize(format='xml')		
	
	
			
"""			
			#if stageItem.get("type") == "exit":	
				event = ns['event/'+str(eventCount)]
				eventCount = eventCount + 1
				graph.add((event, RDF.type, omj['Travel']))
				#print("Found entrence event!")
				if location:
					graph.add((event, ome['to'], location))		
					
				involved = stageItem.get("about")
				
				if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
					involved = involved[1:-1]
					
				if(contains(involved, "-all"))	
					
				elif(contains(involved, "!"))	
				
				else
					chunks = involved.split()
					
					if(contains(chunks[0], ":"))			
"""			
		
	

		
				
"""
		speechItems = sceneItem.findall('sp')

		if cast:
			# Work out a list of all cast in this scene
			sceneCast = list()
			for speechItem in speechItems:
				id = speechItem.get("who")
				if id:
					sceneCast.append(cast[id[1:]])



		# Build up the events
		for speechItem in speechItems:
			event = ns['event/'+str(eventCount)]
			eventCount = eventCount + 1
			graph.add((event, RDF.type, ome['Social']))
			if location:
				graph.add((event, oml['is-located-in'], location))
				

			id = speechItem.get("who")
			if id and cast:
				castUri = cast[id[1:]]
				graph.add((event, ome['has-subject-entity'], castUri))
				for castMember in sceneCast:
					graph.add((event, ome['involves'], castMember))
					graph.add((castMember, ome['involved-in'], event))

			refItems = speechItem.findall("l/rs")
			for refItem in refItems:
				about = extractCURIEorURI(graph, refItem.get("about"))
				graph.add((event, ome["refers-to"], about))
			
			refItems = speechItem.findall("p/rs")
			for refItem in refItems:
				about = extractCURIEorURI(graph, refItem.get("about"))
				graph.add((event, ome["refers-to"], about))

			if(prior_event):
				graph.add((event, ome['follows'], prior_event))
				graph.add((prior_event, ome['precedes'], event))


			prior_event = event

"""	



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

