<?php
	/**
	 * 點名結果列表
	 *
	 * @since   2006/09/06
	 * @author  Wiseguy Liang
	 * @version $Id: pick_user.php,v 1.1 2010/02/24 02:40:30 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	
	while (@ob_end_clean());
	header('Content-Type: text/xml');
	header('Cache-Control: ');
	header('Pragma: ');
	header('Expires: ' . date('r', time() + 2592000)); // XSL 檔案一個月後才失效
	echo <<< EOB
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes"/>
	<xsl:template match="manifest">
		<table class="cssTable" id="resTable" cellspacing="1" cellpadding="3" width="760" align="center" border="0">
			<tbody>
				<tr class="cssTrEvn">
					<td class="cssTd" align="left" colspan="3">
						<input class="cssBtn" id="btnSel1" onclick="selfunc('resList')" type="button" value="{$MSG['select_cancel'][$sysSession->lang]}"/>
					</td>
					<td class="cssTd" align="left" colspan="6">
						<input class="cssBtn" id="btnSend1" type="submit" value="{$MSG['rs_btn_send_mail'][$sysSession->lang]}"/>
					</td>
				</tr>
				<tr class="cssTrHead">
					<td class="cssTd" title="{$MSG['rs_th_checkbox_title'][$sysSession->lang]}" align="middle">
						<input id="ck" onclick="selfunc('resList')" type="checkbox" checked="checked" value="" name="ck" exclude="true"/>
					</td>
					<td class="cssTd" title="{$MSG['rs_th_no_title'][$sysSession->lang]}" align="middle" width="30">{$MSG['rs_th_no_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_name_title'][$sysSession->lang]}" align="middle">{$MSG['rs_th_name_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_last_login_title'][$sysSession->lang]}" align="middle" width="120">{$MSG['rs_th_last_login_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_last_study_title'][$sysSession->lang]}" align="middle" width="120">{$MSG['rs_th_last_study_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_login_title'][$sysSession->lang]}" align="middle" width="66">{$MSG['rs_th_login_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_study_title'][$sysSession->lang]}" align="middle" width="66">{$MSG['rs_th_study_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_post_title'][$sysSession->lang]}" align="middle" width="66">{$MSG['rs_th_post_title'][$sysSession->lang]}</td>
					<td class="cssTd" title="{$MSG['rs_th_chat_title'][$sysSession->lang]}" align="middle" width="66">{$MSG['rs_th_chat_title'][$sysSession->lang]}</td>
				</tr>
				<xsl:apply-templates select="user"/>
				<tr class="cssTrEvn">
					<td align="left" colspan="3">
						<input class="cssBtn" id="btnSel2" onclick="selfunc('resList')" type="button" value="{$MSG['select_cancel'][$sysSession->lang]}" name="btnSel2"/>
					</td>
					<td align="left" colspan="6">
						<input class="cssBtn" id="btnSend2" type="submit" value="{$MSG['rs_btn_send_mail'][$sysSession->lang]}" name="btnSend2"/>
					</td>
				</tr>
			</tbody>
		</table>
	</xsl:template>
	<xsl:template match="user">
		<tr class="cssTrEvn">
			<xsl:attribute name="class"><xsl:choose><xsl:when test="position() mod 2 = 1">cssTrOdd</xsl:when><xsl:otherwise>cssTrEvn</xsl:otherwise></xsl:choose></xsl:attribute>
			<td align="middle">
				<input onclick="chgCheckbox('resList');" type="checkbox" checked="checked" name="user[]">
					<xsl:attribute name="value"><xsl:value-of select="username"/></xsl:attribute>
				</input>
			</td>
			<td align="middle">
				<xsl:value-of select="position()"/>
			</td>
			<td nowrap="nowrap">
				<xsl:attribute name="title"><xsl:value-of select="username"/>(<xsl:value-of select="realname"/>)</xsl:attribute>
				<div class="c1"><xsl:value-of select="username"/>(<xsl:value-of select="realname"/>)</div>
			</td>
			<td>
				<xsl:value-of select="last_login"/>
			</td>
			<td>
				<xsl:value-of select="last_lesson"/>
			</td>
			<td align="right">
				<xsl:value-of select="login_times"/>
			</td>
			<td align="right">
				<xsl:value-of select="lesson_times"/>
			</td>
			<td align="right">
				<xsl:value-of select="post_times"/>
			</td>
			<td align="right">
				<xsl:value-of select="dsc_times"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>
EOB;
?>
