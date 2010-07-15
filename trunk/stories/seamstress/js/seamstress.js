var sparqler = new SPARQL.Service("http://contextus.net/sparql/");


function findObjects(uri, keywords)
{
    alert("URI: "+uri+" keywords: "+keywords);
    var query = sparqler.createQuery();
    query.query("SELECT * WHERE {?s ?p ?o} LIMIT 10", {
        failure: function(xhr) { alert(xhr.statusText); },
        success: function(json) { alert("woo"); }
    });    
}
