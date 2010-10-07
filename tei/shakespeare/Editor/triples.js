/*

	Javascript mini-library for handling Triples

	Author: $Author$
	Last Change: $LastChangedDate$ (Rev: $Rev$)

*/

function Property ( newProperty, newModule, newObject, newSubject, newMin, newMax, newExpected )
{
	this.property = newProperty;
	this.module = newModule;
	this.object = newObject;
	this.subject = newSubject;
	this.min = newMin;
	this.max = newMax;
	this.expected = newExpected;
}

function Triple ( newS, newP, newO )
{
	this.s = newS;
	this.p = newP;
	this.o = newO;
}

Triple.prototype.set = function ( newS, newP, newO )
{
	this.s = newS;
	this.p = newP;
	this.o = newO;
};

Triple.prototype.getS = function ( )
{
	return this.s;
};

Triple.prototype.getP = function ( )
{
	return this.p;
};

Triple.prototype.getO = function ( )
{
	return this.o;
};

Triple.prototype.setO = function ( newO )
{
	this.o = newO;
};



function TripleStore ( )
{
	this.triples = [];
}

TripleStore.prototype.getTriples = function ( )
{
	return this.triples;
}

TripleStore.prototype.set = function ( newTriple )
{
	var index = this.findIndex(newTriple);

	if (index == -1)	
	{
		index = this.triples.length;
	}
	
	this.triples[index] = newTriple;
}

TripleStore.prototype.add = function ( newTriple )
{
	index = this.triples.length;
	this.triples[index] = newTriple;
}

TripleStore.prototype.deleteByIndex = function ( deadIndex )
{
	this.triples.splice(deadIndex,1);
}

TripleStore.prototype.findIndex = function ( queryTriple )
{
	for (i = 0; i < this.triples.length; i++)
	{
		if ((this.triples[i].getS() == queryTriple.getS()) &&
		    (this.triples[i].getP() == queryTriple.getP()) )
		{
			return i;
		}
	}

	return -1;
}

TripleStore.prototype.findTriple = function ( queryS, queryP )
{
	for (searchIndex = 0; searchIndex < this.triples.length; searchIndex++)
	{
		if ((this.triples[searchIndex].getS() == queryS) &&
		    (this.triples[searchIndex].getP() == queryP) )
		{
			return this.triples[searchIndex];
		}
	}

	return null;
}

TripleStore.prototype.getAllTriplesBySubject = function ( subject )
{
	var relevantTriples = [];

	for (searchIndex = 0; searchIndex < this.triples.length; searchIndex++)
	{
		if (this.triples[searchIndex].getS() == subject)
		{
			relevantTriples[relevantTriples.length] = this.triples[searchIndex];
		}
	}

	return relevantTriples;
}


TripleStore.prototype.getOverlappingTriples = function ( otherStore, subject )
{
	var thisRelevantTriples = this.getAllTriplesBySubject(subject);
	var otherRelevantTriples = otherStore.getAllTriplesBySubject(subject);
	var overlappingTriples = [];

	for (cI = 0; cI = thisRelevantTriples.length; cI++)
	{
		for (cI2 = 0; cI2 = otherRelevantTriples.length; cI2++)
		{
			if ((thisRelevantTriples[cI].getS() == otherRelevantTriples[cI2].getS()) &&
			    (thisRelevantTriples[cI].getP() == otherRelevantTriples[cI2].getP()) &&
			    (thisRelevantTriples[cI].getO() == otherRelevantTriples[cI2].getO()))
			{
				overlappingTriples[overlappingTriples.length] = thisRelevantTriples[cI];
				otherRelevantTriples.splice(cI2,1);
				break;
			}
		}
	}

	return overlappingTriples;
}

TripleStore.prototype.getNonOverlappingTriples = function ( otherStore )
{
	var otherTriples = [];
	for (cI = 0; cI = otherStore.triples.length; cI++)
	{
		otherTriples[otherTriples.length] = otherStore.triples[cI];
	}

	for (cI = 0; cI = triples.length; cI++)
	{
		for (cI2 = 0; cI2 = otherTriples.length; cI2++)
		{
			if ((triples[cI].getS() == otherTriples[cI2].getS()) &&
			    (triples[cI].getP() == otherTriples[cI2].getP()) &&
			    (triples[cI].getO() == otherTriples[cI2].getO()))
			{
				otherTriples.splice(cI2,1);
				break;
			}
		}
	}

	return otherTriples;
}