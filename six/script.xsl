<?xml version='1.0'?>
<xsl:stylesheet version="1.0" 
	xmlns:sc="http://contextus.net/six/"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:dc="http://purl.org/dc/elements/1.1/">
<xsl:template match="sc:script">
  <html>
  <head>
    <link rel='stylesheet' media='screen' href='http://contextus.net/six/script.css' type='text/css' />
    <xsl:value-of select="sc:info/dc:title" />
  </head>
  <body>
  <div class="script">
    <xsl:apply-templates />
  </div>
  </body>
  </html>
</xsl:template>

<xsl:template match="sc:info">
  <div class="front">
  <div class="title"><xsl:value-of select="dc:title" /></div>
  <xsl:text>by</xsl:text>
  <div class="creator"><xsl:value-of select="dc:creator" /></div>
  <div class="description"><xsl:value-of select="dc:description" /></div>
  <div class="date"><xsl:value-of select="dc:date" /></div>
  </div>
</xsl:template>

<xsl:template match="sc:transition">
<div class="transition">
<xsl:choose>
<xsl:when test="@type='fadeout'">FADE OUT:</xsl:when>
<xsl:when test="@type='fadein'">FADE IN:</xsl:when>
</xsl:choose>
</div>
</xsl:template>

<xsl:template match="sc:camera">
  <div class="camera">CLOSE UP ON <span class="target"><xsl:value-of select="sc:target" /></span><xsl:text> - </xsl:text><span class="direction"><xsl:value-of select="sc:direction" /></span></div>
</xsl:template>

<xsl:template match="sc:dialogue">
  <div class="dialogue">
    <div class="speaker"><xsl:value-of select="@speaker" /><xsl:if test="@vo='true'"> (V.O.)</xsl:if><xsl:if test="@ctd='true'"> (CONT'D)</xsl:if><xsl:if test="@os='true'"> (O.S.)</xsl:if></div>
    <xsl:if test="@paren"><div class="parenthetical">(<xsl:value-of select="@paren" />)</div></xsl:if>
    <div class="dialogue_text"><xsl:value-of select="current()" /></div>
  </div>
</xsl:template>

<xsl:template match="sc:direction">
 <div class="direction">
   <xsl:value-of select="." />
 </div>
</xsl:template>

<xsl:template match="sc:script/sc:location">
 <div class="location">
   <xsl:variable name="p" select="@pos" />
   <xsl:choose><xsl:when test="$p = 'ext'">EXT. </xsl:when> <xsl:when test="$p = 'int'">INT. </xsl:when></xsl:choose><xsl:value-of select="translate(current(), 'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')" /><xsl:if test="@time"><xsl:text>&#x20;&#x20;&#x20;</xsl:text><xsl:value-of select="translate(@time, 'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')" /></xsl:if>
 </div>
</xsl:template>

</xsl:stylesheet>
