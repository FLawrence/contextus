var sparqler = new SPARQL.Service("http://contextus.net:7000/sparql/");


function findObjects(uri, keywords)
{
    var query = sparqler.createQuery();
    query.query("SELECT * WHERE {?s ?p ?o} LIMIT 10", {
        failure: function(xhr) { alert(xhr); },
        success: function(json) { alert("woo"); }
    });    
}
