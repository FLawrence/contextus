/*

	Javascript mini-library for handling Triples

	Author: $Author$
	Last Change: $LastChangedDate$ (Rev: $Rev$)

*/
var debug = false;

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

function Triple ( newS, newP, newO, originalO )
{
	this.s = newS;
	this.p = newP;
	this.o = newO;
	this.originalO = originalO;

	if (this.o != this.originalO)
	{
		this.state = 'added';
	}
	else
	{
		this.state = 'unchanged';
	}
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

Triple.prototype.getOriginalO = function ( )
{
	return this.originalO;
};

Triple.prototype.getState = function ( )
{
	return this.state;
};

Triple.prototype.setO = function ( newO )
{
	this.o = newO;
	
	if (this.state == 'added') return;
	
	if (this.o != this.originalO)
	{
		this.state = 'changed';
	}
	else
	{
		this.state = 'unchanged';
	}
};

Triple.prototype.setState = function ( newState )
{
	this.state = newState;
};










function TripleStore ( )
{
	this.triples = [];
};



TripleStore.prototype.getTriples = function ( )
{
	return this.triples;
};



TripleStore.prototype.set = function ( newTriple )
{
	var index = this.findIndex(newTriple);

	if (index == -1)	
	{
		index = this.triples.length;
	}
	
	this.triples[index] = newTriple;
};



TripleStore.prototype.add = function ( newTriple )
{
	index = this.triples.length;
	this.triples[index] = newTriple;
};



TripleStore.prototype.deleteByIndex = function ( deadIndex )
{
	if (this.triples[deadIndex].getState() == 'added')
	{
		this.triples.splice(deadIndex,1);
	}
	else
	{
		this.triples[deadIndex].setState('deleted');
	}
};



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
};



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
};

TripleStore.prototype.findTriples = function ( queryS, queryP, queryO )
{
    var triplesFound = [];
    var tfIndex = 0;
    
	for (searchIndex = 0; searchIndex < this.triples.length; searchIndex++)
	{
		if (((queryS == '*') || (this.triples[searchIndex].getS() == queryS)) &&
		    ((queryP == '*') || (this.triples[searchIndex].getP() == queryP)) &&
		    ((queryO == '*') || (this.triples[searchIndex].getO() == queryO)))
		{
			if (this.triples[searchIndex].getState() != 'deleted')
		    	triplesFound[tfIndex++] = this.triples[searchIndex];
		}
	}
	
	if (debug == true)
	   alert('TripleStore.findTriples:\nSearched for [' + queryS + '] [' + queryP + '] [' + queryO + '], found ' + triplesFound.length + ' results');

	return triplesFound;
};

TripleStore.prototype.findTripleIndexes = function ( queryS, queryP, queryO )
{
    var indexesFound = [];
    var tfIndex = 0;
    
	for (searchIndex = 0; searchIndex < this.triples.length; searchIndex++)
	{
		if (((queryS == '*') || (this.triples[searchIndex].getS() == queryS)) &&
		    ((queryP == '*') || (this.triples[searchIndex].getP() == queryP)) &&
		    ((queryO == '*') || (this.triples[searchIndex].getO() == queryO)))
		{
			if (this.triples[searchIndex].getState() != 'deleted')
		    	indexesFound[tfIndex++] = searchIndex;
		}
	}

	return indexesFound;
};

TripleStore.prototype.isChanged = function ( )
{
	for (cI = 0; cI < this.triples.length; cI++)
	{
		if (this.triples[cI].state != 'unchanged') return true;
	}

	return false;
};

TripleStore.prototype.isSubjectChanged = function ( subject )
{
	for (cI = 0; cI < this.triples.length; cI++)
	{
		if (this.triples[cI].getS() != subject) continue;
		if (this.triples[cI].state != 'unchanged') return true;
	}

	return false;
};