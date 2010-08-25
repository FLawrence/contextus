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
	groupCount = 1
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
			speakers = list()
		

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
		group = ns['group/'+str(groupCount)]	
		
		first = True
		refersTo = list()
		#parent = None
		speakerNodes = list()
					
		for node in sceneItem.getiterator():
			#print("Node: " + node.tag)	
			if node.tag == "sp":
				id = node.get("who")
				if id and cast:
					speakers.append(cast[id[1:]])	
					speakerNodes.append(node)
					if cast[id[1:]] not in currentCast:
						currentCast.append(cast[id[1:]])
					
			elif node.tag == "stage":
				if node.get("type") == "entrance":		
				
					# Add Social Events for all the people who spoke since the last break (if there were any)
					
					update = list()
					update = getSocial(graph, ns, speakers, speakerNodes, cast, currentCast, eventCount, event, prior_event, location)
					eventCount = update[0]
					prior_event = update[1]
					
					event = ns['event/'+str(eventCount)]
					
					speakers = list()
					speakerNodes = list()
					
				
					# Add Travel Event
					
					graph.add((event, RDF.type, omj['Travel']))
					
					#print("Entrance event. GroupCount: " + str(groupCount) + ", EventCount: "  + str(eventCount) + ", current cast count: "  + str(len(currentCast)))	

					#print("Found entrence event!")
					if location:
						graph.add((event, ome['to'], location))		
						
					involved = node.get("about")
					
					if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
						involved = involved[1:-1]
						
					chunks = involved.split()
					
					chunk_count = len(chunks)
					
					if chunk_count > 1:
						type = extractCURIEorURI(graph, "[omb:Group]")
						graph.add((group, RDF.type, type))
					
					for chunk in chunks:
						striped = chunk.strip()
						
						if(len(striped) > 0 and striped[0] == "[" and striped[-1] == "]"):
							striped = striped[1:-1]
							currentCast.append(cast[striped])
						
						if chunk_count > 1:
							graph.add((group, ome['contains'], cast[striped]))
						else:
							#print("Adding person as subject-entity to entry event "   + str(eventCount))
							graph.add((event, ome['has-subject-entity'], cast[striped]))
						
					if chunk_count > 1:
						graph.add((event, ome['has-subject-entity'], group))	
						#print("Adding group as subject-entity to entry event "   + str(eventCount))
						groupCount = groupCount + 1
						group = ns['group/'+str(groupCount)]	
	
					if(prior_event):
						graph.add((event, ome['follows'], prior_event))
						graph.add((prior_event, ome['precedes'], event))
	
					prior_event = event					

					eventCount = eventCount + 1
					event = ns['event/'+str(eventCount)]
								
				if node.get("type") == "exit":		
					
					# Add Social Events for all the people who spoke since the last break (if there were any)
					update = list()
					update = getSocial(graph, ns, speakers, speakerNodes, cast, currentCast, eventCount, event, prior_event, location)
					eventCount = update[0]
					prior_event = update[1]
					
					event = ns['event/'+str(eventCount)]
					
					speakers = list()
					speakerNodes = list()
					
					# Add Travel Event
				
					graph.add((event, RDF.type, omj['Travel']))

					#print("Found entrence event!")
					if location:
						graph.add((event, ome['from'], location))		
						
					involved = node.get("about")	
					
					if involved.strip() == "" or "-all" in involved:
						# Remove everyone
												
						#print("Exit all. GroupCount: " + str(groupCount) + ", EventCount: "  + str(eventCount) + ", current cast count: "  + str(len(currentCast)))	
						
						#for peep in currentCast:	
						#	print(peep)
						
						if currentCast > 1:							
							type = extractCURIEorURI(graph, "[omb:Group]")
							graph.add((group, RDF.type, type))
														
						
						for peep in currentCast:	
							if currentCast > 1:
								graph.add((group, ome['contains'], peep))
							else:
								#print("Adding person as subject-entity to exuant event "   + str(eventCount))
								graph.add((event, ome['has-subject-entity'], peep))							

						if currentCast > 1:
							graph.add((event, ome['has-subject-entity'], group))	
							#print("Adding group as subject-entity to exuant event "   + str(eventCount))
							groupCount = groupCount + 1
							group = ns['group/'+str(groupCount)]	
						
						currentCast = list()
					
					elif "!" in involved:
						#print("Exit except some. GroupCount: " + str(groupCount) + ", EventCount: "  + str(eventCount) + ", current cast count: "  + str(len(currentCast)))	
						
						if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
							involved = involved[1:-1]	
							
						involved = involved.strip()	
						
						if(len(involved) > 0 and involved[0] == "!" and involved[1] == "(" and involved[-1] == ")"):
							involved = involved[2:-1]	
						
						#print("involved: " + involved)
						
						striped = involved.strip()	
						
						chunks = striped.split()
						
						staying = list()
						going = list()
						
						for player in currentCast:
							if player in chunks:
								staying.append(player)
							else:
								going.append(player)
								
						going_count = len(going)	
						
						if going_count > 1:
							type = extractCURIEorURI(graph, "[omb:Group]")
							graph.add((group, RDF.type, type))	
							
						for ghost in going:							
							#print("ghost: " + ghost)
							
							
							if ghost in currentCast:
								currentCast.remove(ghost)
								#print("Current cast count: "  + str(len(currentCast)))	
							
							if chunk_count > 1:
								graph.add((group, ome['contains'], ghost))
							else:
								#print("Adding person as subject-entity to exit event "   + str(eventCount))
								graph.add((event, ome['has-subject-entity'], ghost))
							
						if going_count > 1:
							graph.add((event, ome['has-subject-entity'], group))	
							#print("Adding group as subject-entity to exit event "   + str(eventCount))
							groupCount = groupCount + 1
							group = ns['group/'+str(groupCount)]	
	
									
					else:
						#print("Exit some. GroupCount: " + str(groupCount) + ", EventCount: "  + str(eventCount) + ", current cast count: "  + str(len(currentCast)))	
						
						if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
							involved = involved[1:-1]	
							
						striped = involved.strip()							
						chunks = striped.split()
						
						#print("striped: " + striped)
				
						chunk_count = len(chunks)
					
						if chunk_count > 1:
							type = extractCURIEorURI(graph, "[omb:Group]")
							graph.add((group, RDF.type, type))
						
						for chunk in chunks:							
							#print("chunk: " + chunk)	
								
							ghost = cast[chunk]
							
							#print("ghost: " + ghost)
							
							if ghost in currentCast:
								currentCast.remove(ghost)
								#print("Current cast count: "  + str(len(currentCast)))	
							
							if chunk_count > 1:
								graph.add((group, ome['contains'], ghost))
							else:
								#print("Adding person as subject-entity to exit event "   + str(eventCount))
								graph.add((event, ome['has-subject-entity'], ghost))
							
						if chunk_count > 1:
							graph.add((event, ome['has-subject-entity'], group))	
							#print("Adding group as subject-entity to exit event "   + str(eventCount))
							groupCount = groupCount + 1
							group = ns['group/'+str(groupCount)]	

	
						
						
					if(prior_event):
						graph.add((event, ome['follows'], prior_event))
						graph.add((prior_event, ome['precedes'], event))
	
					prior_event = event					

					eventCount = eventCount + 1
					event = ns['event/'+str(eventCount)]
					
			#elif node.tag == "rs":	
			#	#print("Found rs node")
			#	if parent:
			#		#print("Parent type is " + parent.tag)
			#		if parent.tag == "p" or  parent.tag == "l":
			#			refersTo.append(node.get("about"))
						
			#parent = node
				

		# Add Social Events for all the people who spoke since the last break (if there were any)
		#print("Final section of scene, currentCast:" + str(len(currentCast)) + " sperkers: " + str(len(speakers)))
		update = list()
		update = getSocial(graph, ns, speakers, speakerNodes, cast, currentCast, eventCount, event, prior_event, location)
		eventCount = update[0]
		prior_event = update[1]
		
		event = ns['event/'+str(eventCount)]
		group = ns['group/'+str(groupCount)]
			
		speakers = list()
		speakerNodes = list()
		currentCast = list()
		
		
		
		
	print graph.serialize(format='xml')		
	
	
			
"""			
			#if stageItem.get("type") == "exit":	
				event = ns['event/'+str(eventCount)]
				eventCount = eventCount + 1
				graph.add((event, RDF.type, omj['Travel']))
				#print("Found entrence event!")
				if location:
					graph.add((event, ome['from'], location))		
					
				involved = stageItem.get("about")
				
				if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
					involved = involved[1:-1]
					
				if(strip(involved) == "" or contains(involved, "-all"))	
					
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

def getSocial(graph, ns, speakers, speakerNodes, cast, currentCast, eventCount, event, prior_event, location):

	# Add Social Events for all the people who spoke since the last break (if there were any)
	
	speakerCount = 0
					
	if len(speakers) > 1:
		for speaker in speakers:
			if len(currentCast) > 1:
				graph.add((event, RDF.type, ome['Social']))						
			elif len(currentCast) == 1 and first:
				graph.add((event, RDF.type, ome['Introduction']))						
			elif len(currentCast) == 1:
				graph.add((event, RDF.type, ome['Event']))
				
			parent = None	
			if speakerNodes[speakerCount]:
				
				node = speakerNodes[speakerCount]
				
				#print("Start iteration")
				for subnode in node.getiterator():	
					if subnode.tag == "rs":	
						#print("Found rs node, about:" + subnode.get("about") + " parent:" + str(parent))
						if parent is not None :
							#print("Parent type is " + parent.tag)
							if parent.tag == "p" or  parent.tag == "l" or  parent.tag == "rs":
								#about = extractCURIEorURI(graph, subnode.get("about"))
								
								if(len(subnode.get("about")) > 0 and subnode.get("about")[0] == "[" and subnode.get("about")[-1] == "]"):
									reffed = subnode.get("about")[1:-1]	
									
									#print("reffed: " + reffed)
									peep = cast[reffed]
									graph.add((event, ome["refers-to"], peep))
						#else:
						#	print("No parent")
								
					parent = subnode
			
			speakerCount +=1 				
				
			if len(currentCast) > 0:
			
				graph.add((event, ome['has-subject-entity'], speaker))
			
				if location:
					graph.add((event, oml['is-located-in'], location))	
					
				for castMember in currentCast:
					graph.add((event, ome['involves'], castMember))
					graph.add((castMember, ome['involved-in'], event))					


				if(prior_event):
					graph.add((event, ome['follows'], prior_event))
					graph.add((prior_event, ome['precedes'], event))

				prior_event = event	
				first = False

				eventCount = eventCount + 1							
				event = ns['event/'+str(eventCount)]	
	
	return [eventCount, prior_event]



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
