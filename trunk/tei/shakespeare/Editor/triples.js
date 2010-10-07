/*

	Javascript mini-library for handling Triples

	Author: $Author$
	Last Change: $LastChangedDate$ (Rev: $Rev$)

*/

function Property ( newProperty, newModule, newObject, newSubject, newMin, newMax )
{
	this.property = newProperty;
	this.module = newModule;
	this.object = newObject;
	this.subject = newSubject;
	this.min = newMin;
	this.max = newMax;
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


