<?php
	/**
	 * 學習路徑之 xslt 高速產生器
	 *
	 * @since   2006/09/06
	 * @author  Wiseguy Liang
	 * @version $Id: manifest.xsl.php,v 1.1 2010/02/24 02:39:09 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/learn_path.php');
	
	while (@ob_end_clean());
	header('Content-Type: text/xml');
	header('Cache-Control: ');
	header('Pragma: ');
	header('Expires: ' . date('r', time() + 2592000)); // XSL 檔案一個月後才失效
	
	$designateLang = in_array($_GET['lang'], $sysAvailableChars) ? $_GET['lang'] : $sysSession->lang;
	
	$output = <<< EOB
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
							  xmlns:msxsl="urn:schemas-microsoft-com:xslt"
							  xmlns:wm="http://mycompany.com/mynamespace">
	<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes"/>
	<xsl:variable name="lang">{$designateLang}</xsl:variable>
	<msxsl:script language="JScript" implements-prefix="wm">
<![CDATA[
    var cnt = 1;
    function getUnitCnt(lang) {
        return '{$MSG['msg_unit_order'][$sysSession->lang]}'.replace('%num%', (cnt++)) + ':';
    }
	function stripTags(str)
	{
		return str.replace(/<[^>]+>/g, '').replace(/<(\w+)( [^>]*)?>([^<]*)<\/\\1>/ig, '$3');
	}
    
    function escapeHtml(text) {
        var map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

	function getTitle(node, lang) {
		var a = node.item(0).text.split('\t');

		if (lang == 'Big5')
			return a[0];
		else if (lang == 'GB2312')
			return (a.length > 1 && a[1] ? a[1] : a[0]);
        else if (lang == 'en')
			return (a.length > 2 && a[2] ? a[2] : a[0]);
        else if (lang == 'EUC-JP')
			return (a.length > 3 && a[3] ? a[3] : a[0]);
        else if (lang == 'user_define')
			return (a.length > 4 && a[4] ? a[4] : a[0]);
		else
			return a[0];
	}
]]>
	</msxsl:script>
	<xsl:template match="manifest/organizations/organization[@identifier=../@default or position()=1]">
		<xsl:apply-templates select="item"/>
	</xsl:template>

	<xsl:template match="item">
		<xsl:if test="not(@isvisible) or @isvisible != 'false'">
			<xsl:variable name="href" select="concat(/manifest/resources/resource[@identifier=current()/@identifierref]/@xml:base, /manifest/resources/resource[@identifier=current()/@identifierref]/@href)" />
			<div>
                <xsl:attribute name="class">box2 selector</xsl:attribute>
				<xsl:choose>
					<xsl:when test="count(./item) &lt; 1">
						<xsl:attribute name="id"><xsl:value-of select="@identifier"/></xsl:attribute>
						<div style="max-width: 75%; cursor:pointer;">
                            <xsl:attribute name="class">title</xsl:attribute>
							<xsl:choose>
								<xsl:when test="(not(@disabled) or @disabled != 'true') and \$href != '' and \$href != 'about:blank'">
                                    <xsl:attribute name="onclick">parent.showPanel(2); return launchActivity(this,'<xsl:value-of select="@identifier"/>','<xsl:value-of select="@target"/>');</xsl:attribute>
                                    <xsl:attribute name="title"><xsl:value-of select="wm:escapeHtml(wm:stripTags(wm:getTitle(./title[1], string(\$lang))))"/></xsl:attribute>
                                    <div>
                                        <xsl:attribute name="class">icon-node</xsl:attribute>
                                    </div>
                                    <xsl:value-of select="wm:getUnitCnt(string(\$lang))"/>
                                    <xsl:value-of select="wm:getTitle(./title[1], string(\$lang))"/>
								</xsl:when>
								<xsl:otherwise><xsl:value-of select="wm:getTitle(./title[1], string(\$lang))"/></xsl:otherwise>
							</xsl:choose>
						</div>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="id"><xsl:value-of select="@identifier"/></xsl:attribute>
						<span class="cssTbBlur" onmouseover="chBgc(this,true);" onmouseout="chBgc(this,false);">
							<a href="javascript:;" onclick="return expanding(this);">
								<img src="/theme/default/learn/icon-cc.gif" valign="absmiddle" border="0" style="margin-right: 0.5em"/>
							</a>
							<xsl:choose>
								<xsl:when test="(not(@disabled) or @disabled != 'true') and \$href != '' and \$href != 'about:blank'">
									<a>
										<xsl:attribute name="href">javascript:;</xsl:attribute>
										<xsl:attribute name="onclick">expanding(this.previousSibling,'none'); return launchActivity(this,'<xsl:value-of select="@identifier"/>','<xsl:value-of select="@target"/>');</xsl:attribute>
										<xsl:attribute name="class">cssAnchor</xsl:attribute>
										<xsl:attribute name="title"><xsl:value-of select="wm:stripTags(wm:getTitle(./title[1], string(\$lang)))"/></xsl:attribute>
										<xsl:value-of select="wm:getTitle(./title[1], string(\$lang))"/>
									</a>
								</xsl:when>
								<xsl:otherwise><xsl:value-of select="wm:getTitle(./title[1], string(\$lang))"/></xsl:otherwise>
							</xsl:choose>
						</span>
					</xsl:otherwise>
				</xsl:choose>
                <div>
                    <xsl:attribute name="class">operate</xsl:attribute>
                    <button>
                        <xsl:attribute name="style">display: none;</xsl:attribute>
                        <xsl:attribute name="class">btn btn-plane-white</xsl:attribute>
                        <xsl:attribute name="onclick">showExam('<xsl:value-of select="@identifier"/>'); return false;</xsl:attribute>
                        {$MSG['self_assessment'][$designateLang]}
                    </button>
                </div>
                <div>
                    <xsl:attribute name="class">content</xsl:attribute>
                    <div>
                        <xsl:attribute name="class">data1</xsl:attribute>
                        <div>
                            <xsl:attribute name="class">content</xsl:attribute>
                            <xsl:value-of select="concat(/manifest/resources/resource[@identifier=current()/@identifierref]/@xml:base, /manifest/resources/resource[@identifier=current()/@identifierref]/@intro)"/>
                        </div>
                    </div>
                </div>
				<xsl:if test="count(./item) &gt; 0"><div class="content"><div class="data1"><div class="content"><xsl:apply-templates select="item"/></div></div></div></xsl:if>
			</div>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
EOB;

	echo preg_replace(array('/>\s+</', '/\s+</', '/>\s+/'), array('><', '<', '>'), $output);
?>
