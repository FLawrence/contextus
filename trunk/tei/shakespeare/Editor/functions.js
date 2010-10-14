
function setupChooser ( )
{
	document.editForm.namedEntityList.options.length = 0;

	triples = store.getTriples();
	var index = 0;
	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getP() == nameLabel)
		{
			var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
			document.editForm.namedEntityList.options[index] = option;
			index++;
		}
	}
	
	updateFields();
}



function updateFields ( )
{
	item = store.findTriple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value, nameLabel);

//	document.editForm.namedEntityName.value = item.getO();

	document.getElementById('namedEntityID').innerHTML = item.getS();

	createPropertyTable(store, item.getS());
	checkFields();
}
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<!DOCTYPE outline PUBLIC "-//omnigroup.com//DTD OUTLINE 3.0//EN" "http://www.omnigroup.com/namespace/OmniOutliner/xmloutline-v3.dtd">
<outline xmlns="http://www.omnigroup.com/namespace/OmniOutliner/v3"><style-attribute-registry><style-attribute version="1" key="font-fill" group="font" name="fill color" class="color"><color w="0"/></style-attribute><style-attribute version="0" key="font-italic" group="font" name="italic" class="bool">no</style-attribute><style-attribute version="0" key="font-size" group="font" name="size" class="number">12</style-attribute><style-attribute version="0" key="font-weight" group="font" name="weight" class="number">5</style-attribute><style-attribute version="0" key="text-background-color" group="text" name="background color" class="color"><color w="0" a="0"/></style-attribute><style-attribute version="1" key="underline-color" group="underline" name="color" class="color"><color w="0"/></style-attribute><style-attribute version="1" key="underline-style" group="underline" name="style" class="enum"><enum-name-table default-value="0"><enum-name-table-element value="0" name="none"/><enum-name-table-element value="1" name="single"/><enum-name-table-element value="2" name="thick"/><enum-name-table-element value="9" name="double"/></enum-name-table></style-attribute></style-attribute-registry><named-styles><named-style id="dTxs0OyvwFy" name="Highlight" display-order="0"><style><value key="text-background-color"><color r="1" g="1" b="0.4"/></value></style></named-style><named-style id="e-d2km_g8b9" name="Citation" display-order="1"><style><value key="underline-color"><color r="0" g="0" b="1"/></value><value key="underline-style">thick</value></style></named-style><named-style id="hE7e-EYWNsQ" name="Emphasis" display-order="2"><style><value key="font-italic">yes</value></style></named-style></named-styles><settings><page-adornment><first-page-headers is-active="yes"><header location="center"><text><p><run><lit><cell variable="OOSectionTitleVariableIdentifier"/></lit></run></p></text></header></first-page-headers></page-adornment><print-info><print-info-key name="OOScaleDocumentToFitPageWidth" type="boolean">true</print-info-key></print-info></settings><editor content-size="{828, 870}" is-spellchecking-enabled="yes"><drawer palette-height="167"/><selected-rows ids="dFnOVTk0Lr7"/><selected-columns ids="jFqHUGd_Dg1"/><selected-characters range="{5, 0}"/></editor><columns><column id="dTpN3JwYpBn" type="text" width="18" minimum-width="18" maximum-width="18" text-export-width="1" is-note-column="yes"><style><value key="font-fill"><color r="0.33" g="0.33" b="0.33"/></value><value key="font-italic">yes</value><value key="font-size">11</value></style><title><text><p/></text></title></column><column id="jFqHUGd_Dg1" type="text" width="792" minimum-width="13" maximum-width="1000000" text-export-width="72" is-outline-column="yes"><title><text><p><run><lit>DHO Events/Outreach (meeting 13/10/2010)</lit></run></p></text></title></column></columns><root><style><value key="font-weight">9</value><value key="underline-style">single</value></style><style><value key="font-weight">9</value></style><item id="dL-2-4CeZ_A" expanded="yes"><values><text><p><run><lit>Individual Research Projects</lit></run></p></text></values><children><item id="ffH2k2dBdt2"><values><text><p><run><lit>Everyone to propose and complete one</lit></run></p></text></values></item><item id="eZI9_5sqwHK"><values><text><p><run><lit>To potentially explore your own research area or report in scholarly way on your experience with technologies/methods/techniques...</lit></run></p></text></values></item><item id="eobMXy48TnL" expanded="yes"><values><text><p><run><lit>Suggest that this should be voluntary for all staff</lit></run></p></text></values><children><item id="dFnOVTk0Lr7"><values><text><p><run><lit>Katie</lit></run></p></text></values></item><item id="c6vPhIQswEK"><values><text><p><run><lit>Faith</lit></run></p></text></values></item><item id="p26BuYKS4zT"><values><text><p><run><lit>Susan</lit></run></p></text></values></item><item id="llq73A7M42P"><values><text><p><run><lit>Shawn : Visualising Results from NLP of Text for Historical Analysis</lit></run></p></text></values></item><item id="pmRbdiy5jvn"><values><text><p><run><lit>Randall</lit></run></p></text></values></item><item id="c4QzhFx5Q_p"><values><text><p><run><lit>Paolo</lit></run></p></text></values></item><item id="lSJfy5gLhlV"><values><text><p><run><lit>Niall?</lit></run></p></text></values></item></children></item><item id="fS0Dp3WN7eA"><values><text><p><run><lit>Project based on your own work and work that you do with the intention ...</lit></run></p></text></values><children><item id="l-EhiWhqL2V" expanded="yes"><values><text><p><run><lit>Output:</lit></run></p></text></values><children><item id="fXxY-aG2CTD"><values><text><p><run><lit>Published on DHO website</lit></run></p></text></values></item><item id="k1CVXXUXUoa"><values><text><p><run><lit>Externally published</lit></run></p></text></values></item><item id="aXE2Ka5Q2Yx"><values><text><p><run><lit>Possibility of presenting information in a talk at the Academy or partner institution</lit></run></p></text></values></item></children></item></children></item><item id="duPHd5kQJ5E"><values><text><p><run><lit>Blocking time in staff schedules to complete projects</lit></run></p></text></values></item><item id="aq8wUFzg1m_"><values><text><p><run><lit>Timing would be to have results start to become available in the January 2011 and schedule presentation subsequently</lit></run></p></text></values></item><item id="jjPpw0Sa4dK"><values><text><p><run><lit>Need to stagger presentation and publishing</lit></run></p></text></values></item></children></item><item id="gmeDqPtuG0e" expanded="yes"><values><text><p><run><lit>White Paper</lit></run></p></text></values><children><item id="aZ4mJWqd40w" expanded="yes"><values><text><p><run><lit>Collaborative editing process for completing this? and to reflect on this process as part of the paper itself</lit></run></p></text></values><children><item id="m7ewCSYiwwZ"><values><text><p><run><lit>e.g. Anthologize, digress.it, etc.</lit></run></p></text></values></item></children></item><item id="lf_WLTKUG0B"><values><text><p><run><lit>Discussion of headers</lit></run></p></text></values></item><item id="hoj6pR66skI"><values><text><p><run><lit>Focus: &quot;What can people learn from the DHO&apos;s experiences?&quot;</lit></run></p></text></values></item><item id="fwDPVMhDcYR"><values><text><p><run><lit>Timeline: to be completed 2 weeks in advance of DHO Event for pre-circulation</lit></run></p></text></values></item></children></item><item id="cgLzGImOU5G" expanded="yes"><values><text><p><run><lit>DHO Event</lit></run></p></text></values><children><item id="aD2NvzjqN7P"><values><text><p><run><lit>Pre-circulate white paper</lit></run></p></text></values></item><item id="hye9mOAYnps"><values><text><p><run><lit>Use white paper as a talking document</lit></run></p></text></values></item><item id="dXHQUOq2QPd"><values><text><p><run><lit>Round-table feel to the event</lit></run></p></text></values></item><item id="kngzaQpRL26"><values><text><p><run><lit>Talks centred around a.) everything that can be learned, b.) assembling a white paper</lit></run></p></text></values></item><item id="flyseLATtij"><values><text><p><run><lit>Theme: celebrating DH and the work of DHO</lit></run></p></text></values></item><item id="dgloVUoPAIc"><values><text><p><run><lit>Ultimate goal of event: produce final white paper</lit></run></p></text></values></item><item id="fFakHoiRyhh" expanded="yes"><values><text><p><run><lit>Invite...</lit></run></p></text></values><children><item id="kcXm1n7R9Av"><values><text><p><run><lit>IAB</lit></run></p></text></values></item><item id="hP_x2vjczMn"><values><text><p><run><lit>Management Board</lit></run></p></text></values></item><item id="oEVyxJ3Y-qb"><values><text><p><run><lit>Consultative Committee</lit></run></p></text></values></item><item id="k6IZi8QhJtf"><values><text><p><run><lit>Int&apos;l contacts, i.e. Ray</lit></run></p></text></values></item><item id="hyEVSyvhUUP"><values><text><p><run><lit>Past speakers at DHO events</lit></run></p></text></values></item><item id="lEQ_FwE1iuH"><values><text><p><run><lit>King&apos;s College London/ University College London</lit></run></p></text></values></item><item id="mGGWPXOje5X"><values><text><p><run><lit>Local DH people</lit></run></p></text></values></item></children></item></children></item><item id="iEO2UiDARuB" expanded="yes"><values><text><p><run><lit>DRAPIer Case Studies</lit></run></p></text></values><children><item id="pjocOR3jZRe" expanded="yes"><values><text><p><run><lit>In advance of Consultative Committee Meeting on 26/10, programmatic team to create a template for case studies</lit></run></p></text></values><children><item id="pd8Q5A6dF_G"><values><text><p><run><lit>Using AHDS case studies as a reference to develop template</lit></run></p></text></values></item></children></item><item id="b3oIksGUKv_"><values><text><p><run><lit>At Consultative Committee Meeting on 26/10, give the Committee Members the case template, ask them to nominate a project from DRAPIer and put them in touch with the DHO.  </lit></run></p></text></values></item><item id="iXdbyZ5fgQw"><values><text><p><run><lit>DHO programmatic team to come up with a process for being in touch with projects, processing/editing their information and compiling cases</lit></run></p></text></values></item><item id="gXLK3aJqrXC" expanded="yes"><values><text><p><run><lit>Output:</lit></run></p></text></values><children><item id="e-K8nwToSiM"><values><text><p><run><lit>10-12 case studies from various institutions</lit></run></p></text></values></item><item id="l6loYw838cM"><values><text><p><run><lit>Both ADR projects to have case studies in addition to this</lit></run></p></text></values></item></children></item></children></item></root></outline>



function updateName ( index )
{
	var triples = store.getTriples();

	item = triples[index];

	var indexName = 'editProperty' + index;

	item.setO(document.propertyTableForm[indexName].value);
	document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].text = item.getO();

	document.getElementById('namedEntityID').innerHTML = item.getS();

	createPropertyTable(store, item.getS());
	checkFields();

	document.propertyTableForm[indexName].focus()
}



function checkFields ( )
{
	var addedString = "";
	var changedString = "";
	var deletedString = "";

	var triples = store.getTriples();

	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getState() == 'added')
		{
			addedString += triples[i].getS() + "|" + triples[i].getP() + "|" + triples[i].getO() + "\n";
		}

		if (triples[i].getState() == 'changed')
		{
			changedString += triples[i].getS() + "|" + triples[i].getP() + "|" + triples[i].getO() + "|" + triples[i].getOriginalO() + "\n";
		}

		if (triples[i].getState() == 'deleted')
		{
			deletedString += triples[i].getS() + "|" + triples[i].getP() + "|" + triples[i].getO() + "\n";
		}
	}

	document.editForm.addedTriples.value = addedString;
	document.editForm.changedTriples.value = changedString;
	document.editForm.deletedTriples.value = deletedString;

	document.editForm.saveButton.disabled = !store.isChanged();
}



function createPropertyTable ( store, subject )
{
	var triples = store.getTriples();

	var propertyNames = [];
	var propertyCounts = [];

	var table = '<form onsubmit="return false;" name="propertyTableForm"><table><tr><th>Property</th><th>Value</th><th></th></tr>'
	for (i = 0; i < triples.length; i++)
	{
		if ((triples[i].getS() == subject) && (triples[i].getState() != 'deleted'))
		{
//			foundTriple = originalStore.findTriple(triples[i].getS(), triples[i].getP());

			for (j = 0; j < properties.length; j++)
			{
				if ((properties[j].module + properties[j].property) == triples[i].getP())
				{
					var propertyFound = 0;
					for (k = 0; k < propertyNames.length; k++)
					{
						if (propertyNames[k] == triples[i].getP())
						{
							propertyFound = 1;
							propertyCounts[k]++;
						}
					}
					if (propertyFound == 0)
					{
						propertyNames[propertyNames.length] = triples[i].getP();
						propertyCounts[propertyNames.length] = 1;
					}


					var classAttribute = '';
					var edit = triples[i].getO();
					var button = '';

					if (triples[i].getState() == 'changed')
					{
						classAttribute = ' class="changed"';
					}

					if (properties[j].expected == 'L')
					{
						if (((properties[j].module + properties[j].property) == nameLabel) && (propertyFound == 0))
						{
							edit = '<input name="editProperty' + i + '" value="' + edit + '" onkeyup="updateName(' + i + ');"/>';
						}
						else
						{
							edit = '<input name="editProperty' + i + '" value="' + edit + '"/>';
						}
					}


					if ((properties[j].min == 0) || (propertyFound == 1))
					{
						button = '<button onclick="removeProperty(' + i + '); return false;">delete</button>';
					}
					else
					{
						button = '<button disabled="true">delete</button>';
					}
	
					table += '<tr><td>' + triples[i].getP() + '</td><td' + classAttribute + '>' + edit + '</td><td>' + button + '</td></tr>';
				}
			}	

		}	
	}

	table += '<tr><td><select name="propertyList" onchange="updateNewObjectField("property");">';
	table += '<option value="please wait..." />';
	table += '</select></td><td><span id="propertyObjectField"><select name="propertyEntityList"><option value="" name="please wait..."/></select></span></td><td><button onclick="addProperty("property");">add</button></td></tr>';
	
	if (pageType == "location")
	{
		table += '<tr><td><select name="geoLinkList" onchange="updateNewObjectField("geoLink");">';
		table += '<option value="please wait..." />';
		table += '</select></td><td><span id="geoLinkObjectField"><select name="geoLinkEntityList"><option value="" name="please wait..."/></select></span></td><td><button onclick="addProperty("geoLink");">add</button></td></tr>';
	}

	table += '</table></form>'

	document.getElementById('propertyTable').innerHTML = table;

	if(pageType == "character")
	{
		document.propertyTableForm.propertyList.options.length = 0;
		var property = new Option('is', 'http://purl.org/ontomedia/core/expression#is', false, false);
		document.propertyTableForm.propertyList.options[0] = property;
		var property = new Option('is-shadow-of', 'http://purl.org/ontomedia/core/expression#is-shadow-of', false, false);
		document.propertyTableForm.propertyList.options[1] = property;
		var property = new Option('name', nameLabel, false, false);
		document.propertyTableForm.propertyList.options[2] = property;
	}
	else if (pageType == "location")
	{
		document.propertyTableForm.propertyList.options.length = 0;
		var property = new Option('is', 'http://purl.org/ontomedia/core/expression#is', false, false);
		document.propertyTableForm.propertyList.options[0] = property;
		var property = new Option('is-shadow-of', 'http://purl.org/ontomedia/core/expression#is-shadow-of', false, false);
		document.propertyTableForm.propertyList.options[1] = property;
		var property = new Option('label', nameLabel, false, false);
		document.propertyTableForm.propertyList.options[2] = property;
		
		var property = new Option('is-part-of', 'http://signage.ecs.soton.ac.uk/ontologies/location#is-part-of', false, false);
		document.propertyTableForm.geoLinkList.options[0] = property;	
		var property = new Option('adjacent-to', 'http://signage.ecs.soton.ac.uk/ontologies/location#adjacent-to', false, false);
		document.propertyTableForm.geoLinkList.options[1] = property;			
	}

	document.propertyTableForm.entityList.options.length = 0;
	var index = 0;
	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getS() == document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value)
		{
			continue;
		}

		if (triples[i].getP() == nameLabel)
		{
			var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
			document.propertyTableForm.entityList.options[index] = option;
			index++;
		}
	}
}


function updateNewObjectField ( field )
{
	var newProperty = document.propertyTableForm[field . 'List'].options[document.propertyTableForm[field . 'List'].selectedIndex].value;

	for (j = 0; j < properties.length; j++)
	{
		if ((properties[j].module + properties[j].property) == newProperty)
		{
			if (properties[j].expected == 'L')
			{
				document.getElementById(field . 'ObjectField').innerHTML = '<input name="' . field . 'ObjectText" />';
			}
			else
			{
				document.getElementById(field . 'ObjectField').innerHTML = '<select name="' . field . 'EntityList"><option value="" name="please wait..."/></select>';

				document.propertyTableForm.entityList.options.length = 0;
				var index = 0;
				for (i = 0; i < triples.length; i++)
				{
					if (triples[i].getS() == document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value)
					{
						continue;
					}
		
					if (triples[i].getP() == nameLabel)
					{
						var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
						document.propertyTableForm.entityList.options[index] = option;
						index++;
					}
				}
			}
		}
	}
}


function addProperty ( fieldname )
{
	var newProperty = document.propertyTableForm[fieldname . 'List'].options[document.propertyTableForm[fieldname . 'List'].selectedIndex].value;
	var triple;

	for (j = 0; j < properties.length; j++)
	{
		if ((properties[j].module + properties[j].property) == newProperty)
		{
			if (properties[j].expected == 'L')
			{
				triple = new Triple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value,
						document.propertyTableForm[fieldname . 'List'].options[document.propertyTableForm[fieldname . 'List'].selectedIndex].value,
						document.propertyTableForm.newObjectText.value, '');
			}
			else
			{
				triple = new Triple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value,
						document.propertyTableForm[fieldname . 'List'].options[document.propertyTableForm[fieldname . 'List'].selectedIndex].value,
						document.propertyTableForm.entityList.options[document.propertyTableForm.entityList.selectedIndex].value, '');
			}
		}
	}
	store.add(triple);
	createPropertyTable(store, triple.getS());
	checkFields();
}


function removeProperty ( propertyIndex )
{
	store.deleteByIndex(propertyIndex);
	updateFields();
}


function alertStore ( store )
{
	triples = store.getTriples();
	var index = 0;
	for (i = 0; i < triples.length; i++)
	{
		alert(	"S=" + triples[i].getS() + "\n" +
			"P=" + triples[i].getP() + "\n" +
			"O=" + triples[i].getO())
	}
}