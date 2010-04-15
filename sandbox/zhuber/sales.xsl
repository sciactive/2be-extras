<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="iso-8859-1" indent="no"/>
	<xsl:template match="/">
		<hr />
		<xsl:for-each select="sales/sale">
		<xsl:value-of select="timestamp"/>
		<div style="border: dotted 1px; padding-left: 50px; width: 50%;">
			<h2>
				<xsl:value-of select="total"/> ( <xsl:value-of select="salesrep"/> )
			</h2>
			<h3 style="font-weight: normal;">
				Sold to <xsl:value-of select="customer"/>
			</h3>
			<ul>
			<xsl:for-each select="items/item">
				<li><xsl:value-of select="price"/> - <xsl:value-of select="name"/></li>
			</xsl:for-each>
			</ul>
		</div>
		<hr />
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>