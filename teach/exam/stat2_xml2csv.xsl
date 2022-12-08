<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output encoding="UTF-8" method="text" media-type="text/plain" omit-xml-declaration="yes" indent="no"/>
	<xsl:template match="/div">
		<xsl:for-each select="table/tr[2]/td/form/table/tr[position()>1]">
			<xsl:call-template name="title_info"/>
		</xsl:for-each>
		<xsl:for-each select="table[2]/tr">
			<xsl:call-template name="item_info"/>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="title_info">
		<xsl:value-of select="td[1]"/> : <xsl:value-of select="td[2]"/><br/>
	</xsl:template>
	
	<xsl:template name="item_info">
	    <xsl:value-of select="td[1]"/>
		<xsl:for-each select="td[position()>1]">
			<xsl:choose>
				<xsl:when test=".=''">,0</xsl:when>
				<xsl:when test=".='O'">,1</xsl:when>
				<xsl:otherwise>,<xsl:value-of select="."/></xsl:otherwise>
			</xsl:choose>
			<xsl:if test="@colspan">
				%COLSPAN<xsl:value-of select="@colspan"/>%
			</xsl:if>
		</xsl:for-each>
		<br/>
	</xsl:template>
</xsl:stylesheet>
