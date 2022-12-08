<?php
	/**
     * 檔案說明
     *	作業預覽
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @subpackege
     * @author      Edi Chen <edi@sun.net.tw>
     * @copyright   2000-2005 SunNet Tech. INC.
     * @version     CVS: $Id: homework_preview.php,v 1.1 2010/02/24 02:39:07 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2008-04-10
     */
// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/homework_learn.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
// {{{ 函式庫引用 end
// {{{ 主程式 begin
	$content = dbGetOne('WM_qti_' . QTI_which . '_test', 'notice as content', 'exam_id=' . intval($_SERVER['argv'][0]));
	if (!$content) die('peer not exist!');
	showXHTML_head_B('');
	showXHTML_head_E();

	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/peer.css");
//	showXHTML_script('include', '/lib/xmlextras.js');

  echo '<body style="height: auto;">
            <div style="width: 90%; margin: auto auto;">
                <ul class="bar" id="peer-page-title">
                    <li class="left">
                        <span>'. $MSG['homework_content'][$sysSession->lang] . '</span>
                    </li>
                </ul>
                <div class="navbar-form"></div>
                <div class="box box-padding-t-1 box-padding-lr-3" style="padding-bottom: 20px;">
                      <ul class="bar">
                          <li align="left">
                              <span class="title">' . nl2br($content) . '</span>
                          </li>
                      </ul>
                </div>
            </div>
        </body>';

	$js = <<< EOF
	var examDetail = XmlDocument.create();
	var xmlHttp    = XmlHttp.create();

	examDetail.loadXML('{$content}');
	xmlHttp.open('POST', 'homework_display.php?preview=true', false);

	var ret  = xmlHttp.send(examDetail);
	if (ret == false)
	{
		document.write('System Error!');
	}
	else
	{
		document.write(xmlHttp.responseText);
	}
EOF;
	// showXHTML_script('inline', $js);
// {{{ 主程式 end