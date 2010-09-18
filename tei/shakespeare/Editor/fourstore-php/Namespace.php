<?php

class FourStore_Namespace {

    protected static $_namespaces = array(
      'dc' => 'http://purl.org/dc/elements/1.1/',
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
      'xsd' => 'http://www.w3.org/2001/XMLSchema#'
    );

    public static function add($short, $long)
    {
        self::$_namespaces[$short] = $long;
    }

    public static function get($short)
    {
        return self::$_namespaces[$short];
    }

    public static function to_sparql() {
        $sparql = "";
        foreach(self::$_namespaces as $short => $long) {
            $sparql .= "PREFIX $short: <$long>\n";
        }
        return $sparql;
    }

    public static function to_turtle() {
        $turtle = "";
        foreach(self::$_namespaces as $short => $long) {
            $turtle .= "@prefix $short: <$long> .\n";
        }
        return $turtle;
    }


}

?>
