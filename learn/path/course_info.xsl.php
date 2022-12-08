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
    
    var nums = 0;
    
    function cal() {
        
        nums = nums+1;
        return nums;
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
		<ul style="list-style: none;"><xsl:apply-templates select="item"/></ul>
	</xsl:template>

	<xsl:template match="item">
	    
	    
		<xsl:if test="not(@isvisible) or @isvisible != 'false'">
			<xsl:variable name="href" select="concat(/manifest/resources/resource[@identifier=current()/@identifierref]/@xml:base, /manifest/resources/resource[@identifier=current()/@identifierref]/@href)" />

			<li style="margin-top: 10px;">
				<xsl:attribute name="id"><xsl:value-of select="@identifier"/></xsl:attribute>
					<div class="course-set-circle"></div>	
				    <div class="course-set breakword"><xsl:value-of select="wm:getTitle(./title[1], string(\$lang))"/></div>
			</li>
			
			<xsl:variable name="num" select="wm:cal()"/>
			
			<xsl:if test="\$num &lt; count(../item)"><div class="course-set-line"></div></xsl:if>
			
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
EOB;

	echo preg_replace(array('/>\s+</', '/\s+</', '/>\s+/'), array('><', '<', '>'), $output);
?>
