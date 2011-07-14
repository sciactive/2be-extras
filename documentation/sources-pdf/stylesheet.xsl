<?xml version='1.0'?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

<xsl:template match="informalexample/bridgehead">
  <xsl:text>\begin{center}&#10;</xsl:text>
    <xsl:text>\textbf{\captionof{example}{</xsl:text>
    <!--<xsl:text>\captionof{example}{</xsl:text>-->
      <xsl:apply-templates/>
    <!--<xsl:text>}&#10;</xsl:text>-->
    <xsl:text>}}&#10;</xsl:text>
  <xsl:text>\end{center}&#10;</xsl:text>
</xsl:template>

<!--
<xsl:template match="informalexample/bridgehead">
  <xsl:text>\caption</xsl:text>
  <xsl:apply-templates select="." mode="format.title"/>
  <xsl:call-template name="label.id">
    <xsl:with-param name="object" select="parent::informalexample"/>
  </xsl:call-template>
</xsl:template>
-->

</xsl:stylesheet>
