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

<xsl:template match="programlisting|screen" mode="internal">
  <!--<xsl:param name="opt"/>-->
  <xsl:param name="co-tagin"/>
  <xsl:param name="rnode" select="/"/>

  <xsl:variable name="opt">
    <!-- skip empty endlines
    <xsl:if test="$literal.lines.showall='1'">
      <xsl:text>linenos=true,</xsl:text>
    </xsl:if> -->
	<!--<xsl:text>frame=single,framesep=2mm,</xsl:text>-->
    <!-- TeX/verb delimiters if tex or formatting is embedded (like <co>s) -->
    <xsl:if test="$co-tagin!=''">
      <xsl:call-template name="listing-delim">
        <xsl:with-param name="tagin" select="$co-tagin"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:variable>

  <xsl:variable name="language">
    <!-- language option is only for programlisting -->
	<xsl:choose>
      <xsl:when test="@language">
        <xsl:value-of select="@language"/>
      </xsl:when>
	  <xsl:otherwise>
        <xsl:text>text</xsl:text>
      </xsl:otherwise>
	</xsl:choose>
  </xsl:variable>

  <xsl:variable name="env" select="'minted'"/>
<!-- manni , borland , vs -->
  <xsl:text>&#10;\usemintedstyle{manni}&#10;\begin{mintedbox}&#10;\begin{</xsl:text>
  <xsl:value-of select="$env"/>
  <xsl:text>}</xsl:text>
  <!--<xsl:text>[tabsize=4,framesep=0pt,xleftmargin=\FrameSep,xrightmargin=\FrameSep,</xsl:text>-->
  <xsl:text>[texcl=false,mathescape=false,</xsl:text>
  <xsl:if test="$language='php'">
    <xsl:text>firstline=2,</xsl:text>
  </xsl:if>
  <xsl:value-of select="$opt"/>
  <xsl:text>]</xsl:text>
  <xsl:text>{</xsl:text>
  <xsl:value-of select="$language"/>
  <xsl:text>}</xsl:text>
  <!-- some text just after the open tag must be put on a new line -->
  <xsl:if test="not(contains(.,'&#10;')) or
                string-length(normalize-space(
                  substring-before(.,'&#10;')))&gt;0">
    <xsl:text>&#10;</xsl:text>
  </xsl:if>
  <xsl:if test="$language='php'">
    <xsl:text>&lt;?php&#10;</xsl:text>
  </xsl:if>
  <xsl:apply-templates mode="latex.programlisting">
    <xsl:with-param name="co-tagin" select="$co-tagin"/>
    <xsl:with-param name="rnode" select="$rnode"/>
  </xsl:apply-templates>
  <xsl:text>&#10;\end{</xsl:text>
  <xsl:value-of select="$env"/>
  <xsl:text>}&#10;\end{mintedbox}&#10;</xsl:text>
</xsl:template>

</xsl:stylesheet>
