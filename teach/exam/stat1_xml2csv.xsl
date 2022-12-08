<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output encoding="UTF-8" method="text" media-type="text/plain" omit-xml-declaration="yes" indent="no"/>
	<xsl:template match="/table">
		<xsl:for-each select="tr[1]/td/table/tr">
			<xsl:call-template name="title_info"/>
		</xsl:for-each>
		<xsl:for-each select="tr[position()>2]">
			<xsl:call-template name="item_info"/>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="title_info">
		<xsl:value-of select="td[1]"/> : <xsl:value-of select="td[2]"/><br/>
	</xsl:template>
	
	<xsl:template name="item_info">
		<xsl:choose>
			<xsl:when test="count(td)=5">
				<br/>
				<xsl:value-of select="td[1]"/>,
				<xsl:value-of select="td[2]"/>,
				<xsl:value-of select="td[3]"/>,
				<xsl:value-of select="td[4]"/>,
				<xsl:value-of select="td[5]/table/tr/td[2]"/>,
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="td[1]"/>,
				<xsl:value-of select="td[2]"/>,
				<xsl:value-of select="td[3]/table/tr/td[2]"/>
				<xsl:if test="count(following-sibling::tr[1]/td)=3">,</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
