<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xs="http://www.w3.org/2001/XMLSchema" 
    exclude-result-prefixes="#all" version="2.0">
    
    <xsl:output method="xml"/>
<!--    <xsl:strip-space elements="*"/> -->

    <xsl:variable name="namespace" select="'tt'" />

    <xsl:template match="@*|node()">   
        <xsl:copy>        
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>   
    </xsl:template>
    
    
    <!-- xmlns:ta="http://contextus.net/resource/titus_andronicus/" -->  


    <xsl:template match="TEI.2">
        <xsl:text>
        </xsl:text><xsl:choose>     
        <xsl:when test="$namespace = 'ham'"><TEI.2 
            xmlns="http://www.tei-c.org/ns/2.0"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:foaf="http://xmlns.com/foaf/0.1/" 
            xmlns:dbpedia="http://dbpedia.org/resource/"  
            xmlns:ome="http://purl.org/ontomedia/core/expression"  
            xmlns:ham="http://contextus.net/resource/hamlet/"
        >  
            <xsl:apply-templates select="@*|node()"/>
        </TEI.2></xsl:when> 
        <xsl:when test="$namespace = 'ta'"><TEI.2 
            xmlns="http://www.tei-c.org/ns/2.0"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:foaf="http://xmlns.com/foaf/0.1/" 
            xmlns:dbpedia="http://dbpedia.org/resource/"   
            xmlns:ome="http://purl.org/ontomedia/core/expression"             
            xmlns:ta="http://contextus.net/resource/titus_andronicus/"
            >         
            <xsl:apply-templates select="@*|node()"/>
        </TEI.2></xsl:when> 
            <xsl:when test="$namespace = 'mnd'"><TEI.2 
                xmlns="http://www.tei-c.org/ns/2.0"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:foaf="http://xmlns.com/foaf/0.1/" 
                xmlns:dbpedia="http://dbpedia.org/resource/"   
                xmlns:ome="http://purl.org/ontomedia/core/expression"                 
                xmlns:mnd="http://contextus.net/resource/midsum_night_dream/"
                >         
                <xsl:apply-templates select="@*|node()"/>
            </TEI.2></xsl:when>    
            <xsl:when test="$namespace = 'tt'"><TEI.2 
                xmlns="http://www.tei-c.org/ns/2.0"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:foaf="http://xmlns.com/foaf/0.1/" 
                xmlns:dbpedia="http://dbpedia.org/resource/"  
                xmlns:ome="http://purl.org/ontomedia/core/expression"                 
                xmlns:tt="http://contextus.net/resource/tempest/"
                >         
                <xsl:apply-templates select="@*|node()"/>
            </TEI.2></xsl:when>  
            <xsl:when test="$namespace = 'rj'"><TEI.2 
                xmlns="http://www.tei-c.org/ns/2.0"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:foaf="http://xmlns.com/foaf/0.1/" 
                xmlns:dbpedia="http://dbpedia.org/resource/"           
                xmlns:rj="http://contextus.net/resource/r_and_j/"
                >         
                <xsl:apply-templates select="@*|node()"/>
            </TEI.2></xsl:when>  
            <xsl:when test="$namespace = 'maan'"><TEI.2 
                xmlns="http://www.tei-c.org/ns/2.0"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:foaf="http://xmlns.com/foaf/0.1/" 
                xmlns:dbpedia="http://dbpedia.org/resource/"  
                xmlns:ome="http://purl.org/ontomedia/core/expression"                 
                xmlns:maan="http://contextus.net/resource/much_ado/"
                >         
                <xsl:apply-templates select="@*|node()"/>
            </TEI.2></xsl:when> 
            <xsl:when test="$namespace = 'hal5'"><TEI.2 
                xmlns="http://www.tei-c.org/ns/2.0"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:foaf="http://xmlns.com/foaf/0.1/" 
                xmlns:dbpedia="http://dbpedia.org/resource/"   
                xmlns:ome="http://purl.org/ontomedia/core/expression"                 
                xmlns:hal5="http://contextus.net/resource/henryV/"
                >         
                <xsl:apply-templates select="@*|node()"/>
            </TEI.2></xsl:when>             
        </xsl:choose>            
    </xsl:template>
 
    <xsl:template match="/TEI.2/teiHeader/fileDesc//author">
        <xsl:variable name="author" select="translate(normalize-space(.),' ','_')" />      
        <author about="[dbpedia:{$author}]" property="foaf:name"><xsl:value-of select="normalize-space(.)" /></author>
    </xsl:template>
    
    <xsl:template match="/TEI.2/teiHeader/fileDesc//editor">
        <xsl:variable name="editor" select="translate(normalize-space(.),' ','_')" />
        <editor role="editor" about="[dbpedia:{$editor}]" property="foaf:name"><xsl:value-of select="normalize-space(.)" /></editor>
    </xsl:template>
    
    <xsl:template match="/TEI.2/text//castItem/role">
        <xsl:variable name="role" select="translate(normalize-space(.),' ','_')" />  
        <xsl:variable name="id" select="@id" />
        <role xml:id="{$id}" about="[{$namespace}:{$role}]" property="foaf:name"><xsl:value-of select="normalize-space(.)" /></role>
    </xsl:template> 
 
    <xsl:template match="/TEI.2/text/front/set">
        <xsl:variable name="set" select="translate(./p,' ','_')" />  
 <!--       
        <xsl:if test="contains(./p,':')">
            <xsl:variable name="set1" select="substring-after($set,':')" />           
        </xsl:if>
 -->      
        
        <xsl:variable name="set" select="normalize-space($set)" />
        
        <set about="[{$namespace}:{$set}]" type="location">
            <xsl:for-each select="./p">
                <p property="dc:title"><xsl:value-of select="normalize-space(.)" /></p>
             </xsl:for-each>
        </set>
        
        
    </xsl:template>

    <xsl:template match="/TEI.2/text/body//stage[@type='setting']">
        <xsl:variable name="setting" select="translate(normalize-space(.),' ','_')" />
        <stage about="[{$namespace}:{$setting}]" type="location"><xsl:value-of select="normalize-space(.)" /></stage>
    </xsl:template>
 
 
</xsl:stylesheet>
