<?xml version='1.0' encoding="iso-8859-1"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

<!-- Override templates -->
<xsl:import href="custom-stylesheet.xsl"/>

<!-- Layout Parameters -->
<xsl:param name="latex.class.options">letterpaper</xsl:param>

<!-- Hide the list of collaborators, revhistory, examples -->
<xsl:param name="doc.collab.show">0</xsl:param>
<xsl:param name="latex.output.revhistory">0</xsl:param>
<xsl:param name="doc.lot.show"></xsl:param>

<!-- Table formats: allow formal tables to span pages -->
<xsl:param name="table.in.float" select="'0'"/>

<!-- DocBook like description -->
<xsl:param name="term.breakline">1</xsl:param>

<!-- Don't hyphenate terms -->
<xsl:param name="monoseq.hyphenation">nohyphen</xsl:param>

<!-- Break lines after terms of a varlist -->
<xsl:param name="term.breakline">1</xsl:param>

<!-- Fonts -->
<xsl:param name="xetex.font">
  <xsl:text>\setmainfont{Linux Libertine O}
</xsl:text>
  <xsl:text>\setsansfont{Linux Biolinum O}
</xsl:text>
  <xsl:text>\setmonofont[Scale=MatchLowercase,HyphenChar=None]{FreeMono}
</xsl:text>
</xsl:param>

<!-- Really Great Sets
<xsl:param name="xetex.font">
  <xsl:text>\setmainfont[Scale=1.1]{Adobe Garamond Pro}
</xsl:text>
  <xsl:text>\setsansfont[Scale=MatchLowercase]{Linux Biolinum O}
</xsl:text>
  <xsl:text>\setmonofont[Scale=MatchLowercase,HyphenChar=None]{DejaVu Sans Mono}
</xsl:text>
</xsl:param>

<xsl:param name="xetex.font">
  <xsl:text>\setmainfont{Linux Libertine O}
</xsl:text>
  <xsl:text>\setsansfont{Linux Biolinum O}
</xsl:text>
  <xsl:text>\setmonofont[Scale=MatchLowercase,HyphenChar=None]{FreeMono}
</xsl:text>
</xsl:param>
-->

<!-- Great Sets
<xsl:param name="xetex.font">
  <xsl:text>\setmainfont{Times New Roman}
</xsl:text>
  <xsl:text>\setsansfont{Lucida Sans}
</xsl:text>
  <xsl:text>\setmonofont[HyphenChar=None]{Courier New}
</xsl:text>
</xsl:param>

<xsl:param name="xetex.font">
  <xsl:text>\setmainfont{Minion Pro}
</xsl:text>
  <xsl:text>\setsansfont{DejaVu Sans}
</xsl:text>
  <xsl:text>\setmonofont[HyphenChar=None]{Courier New}
</xsl:text>
</xsl:param>

<xsl:param name="xetex.font">
  <xsl:text>\setmainfont{Minion Pro}
</xsl:text>
  <xsl:text>\setsansfont[Scale=MatchLowercase]{Myriad Pro}
</xsl:text>
  <xsl:text>\setmonofont[Scale=MatchLowercase,HyphenChar=None]{Courier New}
</xsl:text>
</xsl:param>
-->

<!-- Good Sets
<xsl:param name="xetex.font">
  <xsl:text>\setmainfont{Lucida Bright}
</xsl:text>
  <xsl:text>\setsansfont[Scale=MatchLowercase]{Lucida Sans}
</xsl:text>
  <xsl:text>\setmonofont[Scale=MatchLowercase,HyphenChar=None]{Lucida Console}
</xsl:text>
</xsl:param>
-->

</xsl:stylesheet>
