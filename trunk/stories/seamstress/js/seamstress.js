var sparqler = new SPARQL.Service("http://contextus.net/sparql/");
sparqler.setRequestHeader("Accept", "application/json");
sparqler.setPrefix("stories", "http://purl.org/ontology/stories/");
sparqler.setPrefix("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
sparqler.setPrefix("rdfs", "http://www.w3.org/2000/01/rdf-schema#");

function findObjects(uri, keywords, callback)
{
    var query = sparqler.createQuery();
	var queryText = "SELECT * WHERE {?s rdf:type stories:Story; rdfs:label ?label; rdfs:comment ?comment.  FILTER (regex(str(?s), '"+uri+"') && (regex(?label, '"+keywords+"') || regex(?comment, '"+keywords+"'))) } LIMIT 10";
    query.query(queryText, {
        failure: function(xhr) {
		$("#finder_error").show();  
	},
        success: callback
    });    
}
