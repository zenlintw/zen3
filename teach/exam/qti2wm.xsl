<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:wm="http://www.sun.net.tw/WisdomMaster">
	<xsl:output indent="no" method="xml" encoding="UTF-8"/>

	<xsl:template match="/">
		<xsl:for-each select="*[name()='questestinterop'][1]">
			<xsl:copy>
				<xsl:attribute name="threshold_score"><xsl:value-of select="@threshold_score"/></xsl:attribute>
				<xsl:apply-templates select="item | section"/>
			</xsl:copy>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="item | section">
		<xsl:choose>
			<xsl:when test="name()='item'">
				<xsl:call-template name="section_child"/>
			</xsl:when>
			<xsl:when test="name()='section'">
				<xsl:copy>
					<xsl:attribute name="id"><xsl:value-of select="@ident"/></xsl:attribute>
					<xsl:for-each select="node()">
						<xsl:call-template name="section_child"/>
					</xsl:for-each>
				</xsl:copy>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="section_child">
		<xsl:choose>
			<xsl:when test="name()='item'">
				<xsl:copy>
					<xsl:attribute name="id"><xsl:value-of select="@ident"/></xsl:attribute>
					<xsl:attribute name="score"><xsl:value-of select="./resprocessing/outcomes/decvar[1]/@defaultval"/></xsl:attribute>
					&lt;td&gt;<xsl:value-of select="@title"/>&lt;/td&gt;
					&lt;td&gt;<xsl:choose>
						<xsl:when test="./presentation//response_lid[@rcardinality='Single']/render_choice/response_label[@ident='T']">WM_ITEM_TYPE[1]</xsl:when>
						<xsl:when test="./presentation//response_lid[@rcardinality='Single']/render_choice">WM_ITEM_TYPE[2]</xsl:when>
						<xsl:when test="./presentation//response_lid[@rcardinality='Multiple']/render_choice">WM_ITEM_TYPE[3]</xsl:when>
						<xsl:when test="./presentation//response_str/render_fib[@prompt='Box']">WM_ITEM_TYPE[5]</xsl:when>
						<xsl:when test="./presentation//*[name()='response_str' or name()='response_num']/render_fib">WM_ITEM_TYPE[4]</xsl:when>
						<xsl:when test="./presentation//response_grp/render_extension">WM_ITEM_TYPE[6]</xsl:when>
					</xsl:choose>&lt;/td&gt;
					&lt;td&gt;<xsl:value-of select="number(concat(0, ./wm:class/wm:version))"/>&lt;/td&gt;
					&lt;td&gt;<xsl:value-of select="number(concat(0, ./wm:class/wm:volume))"/>&lt;/td&gt;
					&lt;td&gt;<xsl:value-of select="number(concat(0, ./wm:class/wm:chapter))"/>&lt;/td&gt;
					&lt;td&gt;<xsl:value-of select="number(concat(0, ./wm:class/wm:paragraph))"/>&lt;/td&gt;
					&lt;td&gt;<xsl:value-of select="number(concat(0, ./wm:class/wm:section))"/>&lt;/td&gt;
					&lt;td&gt;<xsl:value-of select="number(concat(0, ./wm:hardlevel))"/>&lt;/td&gt;</xsl:copy>
			</xsl:when>
			<xsl:otherwise>
				<xsl:copy>
					<xsl:apply-templates select="node() | @*"/>
				</xsl:copy>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="node() | @*">
		<xsl:copy>
			<xsl:apply-templates select="node() | @*"/>
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet>
