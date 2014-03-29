<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" version="1.0">
	<xsl:output method="html"/>
	<xsl:template match="/menu">
		<xsl:if test="php:function('_2be_depend_xml', depends)">
			<p><xsl:value-of select="name" /></p>
			<ul class="dropdown dropdown-horizontal">
				<xsl:choose>
					<xsl:when test="position = 'main_menu' or position = 'header' or position = 'footer'">
						<xsl:attribute name="class">dropdown dropdown-horizontal</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="class">dropdown dropdown-vertical</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:apply-templates select="entry" />
			</ul>
		</xsl:if>
	</xsl:template>
	<xsl:template match="entry">
		<xsl:if test="php:function('_2be_depend_xml', depends)">
			<li>
				<a>
					<xsl:if test="href">
						<xsl:attribute name="href"><xsl:value-of select="href" /></xsl:attribute>
					</xsl:if>
					<xsl:if test="onclick">
						<xsl:attribute name="onclick"><xsl:value-of select="onclick" /></xsl:attribute>
					</xsl:if>
					<xsl:value-of select="name" />
				</a>
				<xsl:if test="entry">
					<ul>
						<xsl:apply-templates select="entry" />
					</ul>
				</xsl:if>
			</li>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
