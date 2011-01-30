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
from rdflib import RDFS
from rdflib import ConjunctiveGraph

foaf = Namespace("http://xmlns.com/foaf/0.1/")
dc = Namespace("http://purl.org/dc/elements/1.1/")
dbpedia = Namespace("http://dbpedia.org/resource/")
ome = Namespace("http://purl.org/ontomedia/core/expression#")
omb = Namespace("http://purl.org/ontomedia/ext/common/being#")
oml = Namespace("http://purl.org/ontomedia/core/space#")
omj = Namespace("http://purl.org/ontomedia/ext/events/travel#")

#### The following nabbed from RDFaParser to handle CURIEs

_urifixer = re.compile('^([A-Za-z][A-Za-z0-9+-.]*://)(/*)(.*?)')


def _urljoin(base, uri):
	uri = _urifixer.sub(r'\1\3', uri)
	return urlparse.urljoin(base, uri)

def extractCURIEorURI(graph, resource, o=""):

	#print "Resource input: " + resource	

	if(len(resource) > 0 and resource[0] == "[" and resource[-1] == "]"):
		resource = resource[1:-1]

	input_ns = ""

	for prefix, ns in graph.namespaces(): 
		if prefix == "default":
			input_ns = ns

	# resolve prefixes
	# TODO: check whether I need to reverse the ns_contexts
	if(resource.find(":") > -1):
		rpre,rsuf = resource.split(":", 1)
		for prefix, ns in graph.namespaces(): 
			#print("Prefix: " + prefix + ", ns: " + ns + ", rpre: " + rpre )
			if prefix == rpre:
				if ns in input_ns:				
					resource = input_ns + rsuf
					s = URIRef(input_ns + o)
					#print "S: " + str(s)

				else:
					resource = ns + rsuf
					s = URIRef(ns + o)
					#print "S: " + str(s)					


				#print "O: " + str(o)
				p = graph.value(s, None, o)
				#print "P: " + str(p)				
				
				if s != None and p != None and o != None:
			    		graph.remove((s, p, o))
					s = URIRef(resolveURI(resource))		
					graph.add((s, p, o))				
					
	#print "Resource output: " + resource				

	# TODO: is this enough to check for bnodes?
	if(len(resource) > 0 and resource[0:2] == "_:"):
		return BNode(resource[2:])

	#print("Namespace returning: " + resource)
	return URIRef(resolveURI(resource))

def resolveURI(uri):
	return _urljoin('' or '', uri)

#### End nabbage

def usage(error):
	print "(Error " + str(error) +  ") Usage: ./tei2onto.py [-t|--teifile=]teifile.xml [-n|--namespace=]http://mynamespace.com/ [-p|--perseusid=]docid"
	return

def convert(teifile, namespace):
	#graph_uri = "http://contextus.net/resource/blue_velvet/"
	
	ns = Namespace(namespace)

	graph = ConjunctiveGraph()
	graph.load(teifile, format="rdfa")
	
	graph.bind("default", ns)
	
	to_update = ""

	for prefix, nsuri in graph.namespaces(): 
		#print("prefix: " + str(prefix) + " - " + str(nsuri))
		if nsuri in ns:
			to_update = nsuri
			
	for s, p, o in graph:
#    		print s, p, o
    		if to_update != "" and to_update in s:
    			graph.remove((s, p, o))
			s = URIRef(s.replace(to_update, ns))			
			graph.add((s, p, o))
	
	act = ""
	scene = ""
	line = ""
	char = 0
	loc = 0
	
	
	#timeline = ns['timeline/narrative']
	#graph.add((timeline, RDF.type, ome['Timeline']))

	tree = ET.parse(teifile)
	cast = dict()
	
	titleNode = tree.find('//title')
	
	castItems = tree.findall('/text/body/div1/castList//castItem')
	for castItem in castItems:
		actorNode = castItem.find('actor')
		roleNode = castItem.find('role')

		if roleNode != None:
			id = roleNode.get("{http://www.w3.org/XML/1998/namespace}id")
		
		#print("Found castItem!")

		actor = None
		role = None

		# Check to see if we already have an entry
		if(roleNode != None and roleNode.get("about")):		

			charname = roleNode.get("about")
			
			if(charname.find(":") > -1):
				nmsp,nom = charname.split(":", 1)		
				charcode =  "character/" + str(char)
				charref = nmsp + ":" + charcode + "]"
				role = extractCURIEorURI(graph, charref,nom[0:-1])
				char += 1		
				#print("1:" + charname + ": adding id " + id + " to " + role)
			else:
				role = extractCURIEorURI(graph, charname)
				#print("2:" + charname + ": adding id " + id + " to " + role)

			cast[id] = role
			graph.add((role, RDF.type, omb['Character']))
			#print(charname + ": adding id " + id + " to " + role)
		
		if(actorNode != None and actorNode.get("about")):
			actor = extractCURIEorURI(graph, actorNode.get("about"))
			graph.add((actor, RDF.type, omb['Being']))

		if actor != None and role != None:
			graph.add((actor, omb['portrays'], role))
			graph.add((role, omb['portrayed-by'], actor))

	eventCount = 1
	groupCount = 1
	prior_event = None
	
	actItems = tree.findall('/text/body/div1')
	ref = ""
	
	for actItem in actItems:
	
		if actItem.get("type") == "act":
			act = actItem.get("n")
		
		sceneItems = actItem.findall('div2')
		
		for sceneItem in sceneItems:
			
			#print("Found sceneItems!")
			
			if sceneItem.get("type") == "scene":
				scene = sceneItem.get("n")		
			
			# Work out the location of this scene
			location = None
			stageItems = sceneItem.findall("stage")
			
			#internalnum = 1
			stagenum = 0
			speechnum = 1
			
			for stageItem in stageItems:
				if stageItem.get("type") == "location":
					# The RDFa parser doesn't handle the type - so we can grab that here.
					
					if stageItem.get("about") != None:
						locname = stageItem.get("about")
					
						# Adding location type/oml:space for location
						if stageItem.get("typeof") and stageItem.get("about"):
							type = extractCURIEorURI(graph, stageItem.get("typeof"))
							#print "1. Location: " + str(location) + " Type: " + str(type)
						elif stageItem.get("about"):	
							#print "2. Location: " + str(locname)											
							type = extractCURIEorURI(graph, oml['Space'])						
						
						
						# Get location value and add rdfs:label is location is not using the TEI value
						if(locname.find(":") > -1):
							nmsp,nom = locname.split(":", 1)		
							loccode =  "location/" + str(loc)
							locref = nmsp + ":" + loccode + "]"
							location = extractCURIEorURI(graph, locref, nom[0:-1])
							loc += 1
							graph.add((location, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(nom[0:-1])))
						else:
							location = extractCURIEorURI(graph, stageItem.get("about"))
						
						# Add location to graph
						graph.add((location, RDF.type, type))	
					else:
						location = ""
					
						
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
			
			refersTo = list()
			#parent = None
			speakerNodes = list()
			speakerRef = list()
			
			xpointer = "http://www.perseus.tufts.edu/hopper/xmlchunk?doc=Perseus:text:"  + str(perseusid) + ":act=" + str(act) + ":scene=" + str(scene)
			stagecount = 0
			stage_array = list()
						
			for node in sceneItem.getiterator():
				#print("Node: " + node.tag)	
				
				
				"""
				if node.tag == "lb":
					if node.get("ed") == "F1":
						line = node.get("n")	
						if titleNode != None:
							ref = titleNode.text + " " + str(act) + "." + str(scene) + "." + str(line)	
						else:
							ref = str(act) + "." + str(scene) + "." + str(line)
							
						#xpointer = "http://www.perseus.tufts.edu/hopper/xmlchunk?doc=Perseus:text:"  + str(perseusid) + ":act=" + str(act) + ":scene=" + str(scene) + "#xpointer(//lb[@ed='F1' and @n='" + str(line)	 + "'])"
						xpointer = "http://www.perseus.tufts.edu/hopper/xmlchunk?doc=Perseus:text:"  + str(perseusid) + ":act=" + str(act) + ":scene=" + str(scene)
						#print("Ref: " + xpointer)
				"""		
						
				if node.tag == "sp":
					id = node.get("who")
					
					if id and cast:
						speakers.append(cast[id[1:]])	
						speakerNodes.append(node)
						
						if perseusid == None:
							speakerRef.append(ref)
						else:
							#speechRef = xpointer + "#xpointer(//lb[@ed='F1' and @n='" + str(int(line) + 1) + "']/ancestor::sp)"
							speechRef  = xpointer + "#xpointer(//div2/sp[" + str(speechnum) + "])";
							speakerRef.append(speechRef)
						#print("Line ref: " + ref)
						
						if cast[id[1:]] not in currentCast:
							currentCast.append(cast[id[1:]])
							
					#internalnum = 1
					speechnum += 1
					stagecount = 0
					
					
					previousl = 0
					
					for subnode in node.getiterator():
						if subnode.tag == "l":
							previousl += 1
						
						if subnode.tag == "stage":
							#print ("Stagecount: " + str(stagecount) + " Previousl: " + str(previousl) + "\n")
							stage_array.append(previousl)
							stagecount += 1
							
					
						
				elif node.tag == "stage":
					
					if stagecount > 0:
						s_max = len(stage_array)
						diff = s_max - stagecount
						
						#if diff == 0:
						#	stagenum += 1
					
						entRef = xpointer + "#xpointer(//div2/sp[" + str(speechnum - 1) + "]/l[" + str(stage_array[diff]) +"]/stage)";
						#internalnum += 1
						stagecount -= 1
					else:
						stagenum += 1
						entRef = xpointer + "#xpointer(//div2/stage[" + str(stagenum) +"])";				
					
					if node.get("type") == "entrance":		
					
						# Add Social Events for all the people who spoke since the last break (if there were any)
						
						update = list()
						update = getSocial(graph, ns, speakers, speakerNodes, speakerRef, cast, currentCast, eventCount, event, prior_event, location)
						eventCount = update[0]
						prior_event = update[1]
						
						event = ns['event/'+str(eventCount)]
						
						speakers = list()
						speakerNodes = list()
						speakerRef = list()
					
						# Add Travel Event
						
						graph.add((event, RDF.type, omj['Travel']))
						
						if perseusid == None:
							graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), Literal(ref)))
						else:
							#entRef = xpointer + "#xpointer(//lb[@ed='F1' and @n='" + str(line) + "']/following-sibling::*[1]/self::stage)"
							graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), URIRef(entRef)))
						
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
							#type = extractCURIEorURI(graph, "[omb:Group]")
							#graph.add((group, RDF.type, type))
							graph.add((group, RDF.type, omb['Group']))
							
						event_label = ""	
						en = 1
						
						for chunk in chunks:
							striped = chunk.strip()
							
							if(len(striped) > 0 and striped[0] == "[" and striped[-1] == "]"):
								striped = striped[1:-1]
								currentCast.append(cast[striped])								
							
							if chunk_count > 1:
								graph.add((group, ome['contains'], cast[striped]))
								
								if en == chunk_count:
									event_label = event_label[0:-2] + " and " + striped
									graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(event_label + " arrive")))
								elif en < chunk_count:
									event_label += striped + ", "									
									
							else:
								#print("Adding person as subject-entity to entry event "   + str(eventCount))
								graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(striped + " arrives")))
								graph.add((event, ome['has-subject-entity'], cast[striped]))
								
							en += 1
									
							
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
						update = getSocial(graph, ns, speakers, speakerNodes, speakerRef, cast, currentCast, eventCount, event, prior_event, location)
						eventCount = update[0]
						prior_event = update[1]
						
						event = ns['event/'+str(eventCount)]
						
						speakers = list()
						speakerNodes = list()
						speakerRef = list()
						
						# Add Travel Event
					
						graph.add((event, RDF.type, omj['Travel']))		
						
						if perseusid == None:
							graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), Literal(ref)))
						else:
							#exitRef = xpointer
							#graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), URIRef(exitRef)))
							graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), URIRef(entRef)))
	
						#print("Found entrence event!")
						if location != None:
							graph.add((event, ome['from'], location))		
							
						involved = node.get("about")	
						
						if involved.strip() == "" or "-all" in involved:
							# Remove everyone
													
							#print("Exit all. GroupCount: " + str(groupCount) + ", EventCount: "  + str(eventCount) + ", current cast count: "  + str(len(currentCast)))	
							
							#for peep in currentCast:	
							#	print(peep)
							
							if len(currentCast) > 1:							
								#type = extractCURIEorURI(graph, "[omb:Group]")
								#graph.add((group, RDF.type, type))
								graph.add((group, RDF.type, omb['Group']))
															
							event_label = ""
							en = 1
							
							for peep in currentCast:	
								short_ref = ""
								for key, value in cast.iteritems():
									if peep == value:	
										short_ref = key
							
								if len(currentCast) > 1:
									graph.add((group, ome['contains'], peep))
									
									if en == len(currentCast):
										event_label = event_label[0:-2] + " and " + short_ref
										graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(event_label + " leave")))	
									elif en < len(currentCast):
										event_label += short_ref + ", "
																	
								else:
									#print("Adding person as subject-entity to exuant event "   + str(eventCount))
									graph.add((event, ome['has-subject-entity'], peep))
									graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(short_ref + " leaves")))
									
								en += 1
	
							if len(currentCast) > 1:
								graph.add((event, ome['has-subject-entity'], group))	
								#print("Adding group as subject-entity to exuant event "   + str(eventCount))
								groupCount = groupCount + 1
								group = ns['group/'+str(groupCount)]	
							
							currentCast = list()
						
						elif "!" in involved:
							#print("Exit except some. GroupCount: " + str(groupCount) + ", EventCount: "  + str(eventCount) + ", current cast count: "  + str(len(currentCast)))	
							
							#print("Event: " + involved);
							
							if(len(involved) > 0 and involved[0] == "[" and involved[-1] == "]"):
								involved = involved[1:-1]	
								
							involved = involved.strip()	
							
							if(len(involved) > 0 and involved[0] == "!" and involved[1] == "(" and involved[-1] == ")"):
								involved = involved[2:-1]	
							
							#print("involved: " + involved)
							
							striped = involved.strip()	
							
							c_ids = striped.split()
							
							chunks = list()
							
							for stay in c_ids:
								#print("Staying: " + cast[stay])
								chunks.append(cast[stay])							
							
							staying = list()
							going = list()
							
							for player in currentCast:
								#print("Player: " + player)							
								if player in chunks:
									staying.append(player)
								else:
									going.append(player)
									
							going_count = len(going)	
							
							if going_count > 1:
								#type = extractCURIEorURI(graph, "[omb:Group]")
								#graph.add((group, RDF.type, type))	
								graph.add((group, RDF.type, omb['Group']))
								

							event_label = ""
							en = 1
								
							for ghost in going:							
								#print("ghost: " + ghost)
								
								short_ref = ""
								for key, value in cast.iteritems():
									if ghost == value:	
										short_ref = key
										
										
								if ghost in currentCast:
									currentCast.remove(ghost)
									#print("Current cast count: "  + str(len(currentCast)))	
								
								if going_count > 1:
									graph.add((group, ome['contains'], ghost))
									
									if en == len(going):
										event_label = event_label[0:-2] + " and " + short_ref
										graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(event_label + " leave")))	
									elif en < len(going):
										event_label += short_ref + ", "	
										
								else:
									#print("Adding person as subject-entity to exit event "   + str(eventCount))
									graph.add((event, ome['has-subject-entity'], ghost))
									graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(short_ref + " leaves")))
									
								en += 1
								
								
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
								#type = extractCURIEorURI(graph, "[omb:Group]")
								#graph.add((group, RDF.type, type))
								graph.add((group, RDF.type, omb['Group']))
								
								
							event_label = ""
							en = 1								
							
							for chunk in chunks:							
								#print("chunk: " + chunk)			
									
								ghost = cast[chunk]
								
								#print("ghost: " + ghost)
								
								if ghost in currentCast:
									currentCast.remove(ghost)
									#print("Current cast count: "  + str(len(currentCast)))	
								
								if chunk_count > 1:
									graph.add((group, ome['contains'], ghost))
									
									if en == len(currentCast):
										event_label = event_label[0:-2] + " and " + chunk
										graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(event_label + " leave")))	
									elif en < len(currentCast):
										event_label += chunk + ", "										
									
								else:
									#print("Adding person as subject-entity to exit event "   + str(eventCount))
									graph.add((event, ome['has-subject-entity'], ghost))
									graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(chunk + " leaves")))
									
								en += 1	
								
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
			update = getSocial(graph, ns, speakers, speakerNodes, speakerRef, cast, currentCast, eventCount, event, prior_event, location)
			eventCount = update[0]
			prior_event = update[1]
			
			event = ns['event/'+str(eventCount)]
			group = ns['group/'+str(groupCount)]
				
			speakers = list()
			speakerNodes = list()
			currentCast = list()
			speakerRef = list()
		
		
		
	print graph.serialize(format='xml')		
	

def getSocial(graph, ns, speakers, speakerNodes, speakerRef, cast, currentCast, eventCount, event, prior_event, location):

	# Add Social Events for all the people who spoke since the last break (if there were any)
	
	#print("---")
	first = True	
	
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
						
				
			if len(currentCast) > 0:
			
				graph.add((event, ome['has-subject-entity'], speaker))
				
				for key, value in cast.iteritems():
					if speaker == value:				
						graph.add((event, rdflib.URIRef('http://www.w3.org/2000/01/rdf-schema#label'), Literal(key + " speaks")))
			
				if location:
					graph.add((event, oml['is-located-in'], location))	
					
				for castMember in currentCast:
					graph.add((event, ome['involves'], castMember))
					graph.add((castMember, ome['involved-in'], event))					


				if(prior_event):
					graph.add((event, ome['follows'], prior_event))
					graph.add((prior_event, ome['precedes'], event))
					
				lineRef = speakerRef[speakerCount]	
				#print("LineRef: " + lineRef)
				
				#Xpointer reference to Perseus
				
				if lineRef != "":
					if perseusid == None:
						graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), Literal(lineRef)))
					else:
						graph.add((event, rdflib.URIRef("http://www.w3.org/2000/01/rdf-schema#seeAlso"), URIRef(lineRef)))					

				prior_event = event	
				first = False

				eventCount = eventCount + 1							
				event = ns['event/'+str(eventCount)]	
			
			speakerCount +=1 	
	
	return [eventCount, prior_event]



def main(argv):
	try:
		opts, args = getopt.getopt(argv, "ht:n:p:", ["help", "teifile=", "namespace=", "perseusid="])
	except getopt.GetoptError:
		usage("1")
		sys.exit(2)

	teifile = None
	namespace = None
	global perseusid
	perseusid = None
	
	for o,a in opts:
		#print("o: " + str(o) + ", a: " + str(a))
		if o in ("-t", "--teifile"):
			teifile = a
		elif o in ("-n", "--namespace"):
			namespace = a
		elif o in ("-p", "--perseusid"):
			perseusid = a	
			#print("perseusid: " + str(perseusid))
		else:
			usage("2")
			sys.exit(2)

	if teifile == None or namespace == None:
		usage("3")
		sys.exit(2)
		
	convert(teifile, namespace)	

if __name__ == "__main__":
	main(sys.argv[1:])

