<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns:tei="http://www.tei-c.org/ns/2.0"
    exclude-result-prefixes="#all" version="2.0" >
    
    <xsl:output method="xml"/>
    <!--    <xsl:strip-space elements="*"/> -->
    
    <xsl:variable name="namespace" select="'ta'" />
    <xsl:variable name="travel-event" select="'[ome:involves'" /> 
    
    <xsl:template match="@*|node()">   
        <xsl:copy>        
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>   
    </xsl:template>
  
 <!--   
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>
-->
    <xsl:template match="/tei:TEI.2/text/body//stage[@type='entrance']">                   
        <xsl:if test="count(child::rs) > 0"> 
            <stage type="entrance">
                <xsl:attribute name="about">
                    <xsl:value-of select="$travel-event" />
                    <xsl:for-each select="child::rs">
                        <xsl:value-of select="concat(' ',substring(@about, 2, string-length(@about) - 2))"/>
                    </xsl:for-each>
                    <xsl:value-of select="']'" />
                 </xsl:attribute> 
                 <xsl:apply-templates select="@*|node()"/>                    
            </stage>  
        </xsl:if>
    </xsl:template> 

    <xsl:template match="/tei:TEI.2/text/body//stage[@type='exit']" name="exit">
        <xsl:if test="count(child::rs) > 0"> 
            <stage type="exit">
                <xsl:attribute name="about">
                    <xsl:value-of select="$travel-event" />
                    
                    <!-- default exit node (lists everyone) -->
                    
                    <xsl:if test="contains(., 'all except')=false() and contains(.,'all but')=false()">  
                    <!-- <xsl:value-of select="'first if'" />   -->                       
                        <xsl:for-each select="child::rs">
                            <xsl:value-of select="concat(' ',substring(@about, 2, string-length(@about) - 2))"/>
                        </xsl:for-each>                                                  
                    </xsl:if>
                    
                    <!-- exceptions (lists people before 'but'|'except' and people afterwards as not included) -->
                    
                    <xsl:if test="contains(., 'all except') or contains(., 'all but')">
                        <!-- Line contains 'all but' -->
                        <xsl:if test="contains(., 'all but')">
                            
                            <!-- If substring of parent before 'but' contains the text of the child node then include it -->  
                            <xsl:for-each select="child::rs">
                                <xsl:if test="contains(substring-before(.., 'but'), .)">
                                    <xsl:value-of select="concat(' ',substring(@about, 2, string-length(@about) - 2))"/>
                                </xsl:if>
                            </xsl:for-each>   
   
                            <!-- If substring of parent after 'but' contains the text of the child node then include it -->                               
                            <xsl:value-of select="' !('" />
                            <xsl:for-each select="child::rs">
                                <xsl:if test="contains(substring-after(.., 'but'), .)">
                                     <xsl:value-of select="concat(' ',substring(@about, 2, string-length(@about) - 2))"/>
                                </xsl:if>
                            </xsl:for-each>                       
                            <xsl:value-of select="')'" />
                        </xsl:if>   

                        <!-- line contains 'all except' -->
                        <xsl:if test="contains(., 'all except')">
                            
                            <!-- If substring of parent before 'except' contains the text of the child node then include it -->
                            <xsl:for-each select="child::rs">
                                <xsl:if test="contains(substring-before(.., 'except'), .)">
                                    <xsl:value-of select="concat(' ',substring(@about, 2, string-length(@about) - 2))"/>
                                </xsl:if>
                            </xsl:for-each>   
                            
                            <!-- If substring of parent after 'except' contains the text of the child node then include it -->                               
                            <xsl:value-of select="' !('" />
                            <xsl:for-each select="child::rs">
                                <xsl:if test="contains(substring-after(.., 'except'), .)">
                                    <xsl:value-of select="concat(' ',substring(@about, 2, string-length(@about) - 2))"/>
                                </xsl:if>
                            </xsl:for-each>                       
                            <xsl:value-of select="')'" />
                        </xsl:if> 

                    </xsl:if>                   
                   <xsl:value-of select="']'" />
                </xsl:attribute> 
                <xsl:apply-templates select="@*|node()"/>               
            </stage>  
        </xsl:if>
  
        <!-- exit node with no characters specified within it (lists current speaker if 'Exit' and 'all' if 'Exeunt') -->
  
        <xsl:if test="count(child::rs) = 0">            
            <xsl:if test="contains(., 'Exeunt')=false()">
                <stage type="exit">
                    <xsl:attribute name="about">
                        <xsl:value-of select="$travel-event" />
                        <xsl:for-each select="ancestor::sp">
                            <xsl:value-of select="concat(' ',substring(@who, 2))"/>
                        </xsl:for-each>
                        <xsl:value-of select="']'" />
                    </xsl:attribute> 
                    <xsl:apply-templates select="@*|node()"/>             
                </stage>   
            </xsl:if>      
            
            <xsl:if test="contains(., 'Exeunt')">
                <stage about="{$travel-event}-all]" type="exit" ><xsl:apply-templates select="@*|node()"/></stage>
            </xsl:if>
        </xsl:if>          
        
    </xsl:template>    

    <xsl:template match="/tei:TEI.2/text/body//stage[count(@*) = 0]">
        
        <xsl:if test="contains(., 'Exeunt') or contains(., 'Exit')">
            <xsl:call-template name="exit" />
        </xsl:if>
        
        <xsl:if test="contains(., 'Exeunt')=false() and contains(., 'Exit')=false()">
            <xsl:apply-templates select="@*|node()"/>
        </xsl:if>
        
    </xsl:template>

<!--
    <xsl:template name="complication">
        <xsl:param name="exceptions" />
        <xsl:value-of select="$exceptions" />
    </xsl:template>
-->

</xsl:stylesheet>
