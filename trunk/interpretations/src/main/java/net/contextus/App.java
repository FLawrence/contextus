package net.contextus;

import com.hp.hpl.jena.query.*;
import com.hp.hpl.jena.rdf.model.RDFNode;
import com.hp.hpl.jena.graph.Node;

import de.fuberlin.wiwiss.ng4j.Quad;
import de.fuberlin.wiwiss.ng4j.NamedGraphSet;
import de.fuberlin.wiwiss.ng4j.impl.NamedGraphSetImpl;
import de.fuberlin.wiwiss.ng4j.sparql.NamedGraphDataset;

import java.util.Iterator;
import java.io.*;

public class App 
{
    public static void main( String[] args ) throws Exception
    {
		NamedGraphSet graphset = new NamedGraphSetImpl();
		graphset.read(new FileInputStream(new File("examples/test.trig")), "TRIG", "http://contextus.net/");
		NamedGraphDataset dataset = new NamedGraphDataset(graphset);
	
		dumpAll(Node.ANY, graphset);

		// Select all interpretation graphs
		Query graphFetch = QueryFactory.create("PREFIX stories: <http://purl.org/ontology/stories/> PREFIX dc: <http://purl.org/dc/elements/1.1/> SELECT ?graph WHERE { ?int a stories:Interpretation; dc:creator <http://www.thecollectedmike.com/#me>; stories:asserts ?graph }");
		QueryExecution qe = QueryExecutionFactory.create(graphFetch, dataset);
		ResultSet results = qe.execSelect();

		// Fetch all the statements in these graphs
		while (results.hasNext()) {
				QuerySolution result = results.nextSolution();
				RDFNode graph = result.get("graph");
				Iterator it = graphset.findQuads(Node.createURI(graph.toString()), Node.ANY, Node.ANY, Node.ANY);
				while(it.hasNext())
				{
					Quad q = (Quad)it.next();
					Quad newQuad = new Quad(Node.createURI("http://contextus.net/"), q.getSubject(), q.getPredicate(), q.getObject());

					System.out.println("Inserting "+newQuad);
					graphset.addQuad(newQuad);
				}
		}

		dumpAll(Node.createURI("http://contextus.net/"), graphset);
		graphset.write(System.out, "TRIG", "http://contextus.net/");
    }

	public static void dumpAll(Node graph, NamedGraphSet graphSet)
	{
		System.out.println("--- Dumping: "+graph);
		Iterator it = graphSet.findQuads(graph, Node.ANY, Node.ANY, Node.ANY);
		while(it.hasNext())
		{
			Quad q = (Quad)it.next();
			System.out.println(q);
		}
		System.out.println("--- End dump");

	}
}
