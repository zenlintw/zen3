<?php
/**
 * 圖表類別
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Edi Chen <edi@sun.net.tw>
 * @copyright   2000-2007 SunNet Tech. INC.
 * @version     CVS: $Id: wm_graph.php,v 1.1 2010/02/24 02:39:34 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2007-04-13
 */

// {{{ 函式庫引用 begin
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
if (PHP_VERSION >= '5') {
    require_once(sysDocumentRoot . '/lib/jpgraph_to_php5/src/jpgraph.php');
    require_once(sysDocumentRoot . '/lib/jpgraph_to_php5/src/jpgraph_bar.php');
    require_once(sysDocumentRoot . '/lib/jpgraph_to_php5/src/jpgraph_iconplot.php');
} else {
    require_once(sysDocumentRoot . '/lib/jpgraph/src/jpgraph.php');
    require_once(sysDocumentRoot . '/lib/jpgraph/src/jpgraph_bar.php');
    require_once(sysDocumentRoot . '/lib/jpgraph/src/jpgraph_iconplot.php');
}
// }}} 函式庫引用 end

Class WMGraph
{
    var $entity; // 實體
    var $rotate; // 是否旋轉圖表
    var $width;
    var $height;
    
    /**
     * Contructor
     * @param int $width graph width
     * @param int $height graph height
     * @param boolean $rotate if rotate
     */
    function WMGraph($width = 450, $height = 300, $rotate = false)
    {
        global $sysSession;
        $this->entity = new Graph($width, $height, auto);
        $this->entity->SetScale('textint');
        $this->entity->SetBackgroundImage(sprintf('%s/theme/%s/%s/g-bg.gif', sysDocumentRoot, $sysSession->theme, $sysSession->env), BGIMG_FILLFRAME); // 圖表背景色
        $this->entity->SetFrame(true, 'white'); // 圖表的外框顏色
        $this->entity->SetShadow(); // 設定圖表外框陰影
        $this->entity->ygrid->SetFill(true, '#FFFFFF', '#F8F8F8'); // 設定圖表內容內框顏色
        $this->rotate = $rotate; // 設定圖表是否旋轉90度
        $this->width  = $width;
        $this->height = $height;
        if ($rotate) {
            $this->entity->Set90AndMargin();
            $this->entity->yaxis->SetPos('max'); // 如果旋轉90度, 把Y軸位置放到底下
        }
    }
    
    /**
     * 設定圖表名稱
     * @param string $title 名稱
     */
    function setGraphTitle($title)
    {
        global $sysSession;
        if ($sysSession->lang == 'Big5') {
            $this->entity->title->Set(iconv('UTF-8', 'Big5', $title));
            $this->entity->title->SetFont(FF_BIG5, FS_NORMAL, 16);
        } else if ($sysSession->lang == 'GB2312') {
            $this->entity->title->Set(iconv('UTF-8', 'GB2312', $title));
            $this->entity->title->SetFont(FF_SIMSUN, FS_BOLD, 16);
        } else
            $this->entity->title->Set($title);
        
        $this->entity->title->SetColor('purple');
        
        $w = floor($this->entity->title->GetWidth($this->entity->img) * 0.686);
        if ($this->rotate) {
            $ix   = (($this->width - $this->height) >> 1) + 6;
            $iy   = (($this->height + $w) >> 1) + 80; // 30 = 小圖寬度 + 與 title 的距離
            $icon = new IconPlot(sprintf('%s/theme/%s/teach/graph.gif', sysDocumentRoot, $sysSession->theme), $ix, $iy, 0.8);
        } else {
            $ix   = (($this->width - $w) >> 1) - 80; // 36 = 小圖寬度 + 與 title 的距離
            $icon = new IconPlot(sprintf('%s/theme/%s/teach/graph.gif', sysDocumentRoot, $sysSession->theme), $ix, 4, 0.8);
        }
        $this->entity->Add($icon);
    }
    
    /**
     * 設定圖表名稱
     * @param string $title 名稱
     */
    function setGraphSubTitle($title)
    {
        global $sysSession;
        if ($sysSession->lang == 'Big5') {
            $this->entity->subtitle->Set(iconv('UTF-8', 'Big5', $title));
            $this->entity->subtitle->SetFont(FF_BIG5, FS_NORMAL, 12);
        } else if ($sysSession->lang == 'GB2312') {
            $this->entity->subtitle->Set(iconv('UTF-8', 'GB2312', $title));
            $this->entity->subtitle->SetFont(FF_SIMSUN, FS_BOLD, 12);
        } else
            $this->entity->subtitle->Set($title);
    }
    
    /**
     * 設定X軸名稱
     * @param string $title 名稱
     */
    function setXaxisTitle($title, $margin = '20')
    {
        global $sysSession;
        if ($sysSession->lang == 'Big5') {
            $this->entity->xaxis->SetTitle(iconv('UTF-8', 'Big5', $title));
            $this->entity->xaxis->title->SetFont(FF_BIG5, FS_NORMAL, 12);
        } else if ($sysSession->lang == 'GB2312') {
            $this->entity->xaxis->SetTitle(iconv('UTF-8', 'GB2312', $title));
            $this->entity->xaxis->title->SetFont(FF_SIMSUN, FS_BOLD, 12);
        } else
            $this->entity->xaxis->SetTitle($title);
        
        $this->entity->xaxis->title->SetColor('darkgreen'); // X軸名稱顏色
        
        $this->entity->xaxis->title->SetAngle($this->rotate ? 90 : 0);
        $this->entity->xaxis->title_adjust = $this->rotate ? 'middle' : 'high';
        $this->entity->xaxis->SetTitleMargin($margin);
    }
    
    /**
     * 設定Y軸名稱
     * @param string $title 名稱
     */
    function setYaxisTitle($title, $margin = '20')
    {
        global $sysSession;
        if ($sysSession->lang == 'Big5') {
            $this->entity->yaxis->SetTitle(iconv('UTF-8', 'Big5', $title));
            $this->entity->yaxis->title->SetFont(FF_BIG5, FS_NORMAL, 12);
            $this->entity->yaxis->title->SetAngle($this->rotate ? 0 : 90);
        } elseif ($sysSession->lang == 'GB2312') {
            $this->entity->yaxis->SetTitle(iconv('UTF-8', 'GB2312', $title));
            $this->entity->yaxis->title->SetFont(FF_SIMSUN, FS_BOLD, 12);
            $this->entity->yaxis->title->SetAngle($this->rotate ? 0 : 270);
        } else
            $this->entity->yaxis->title->Set($title);
            $this->entity->yaxis->title->SetAngle($this->rotate ? 0 : 270);
        
        $this->entity->yaxis->title->SetColor('darkgreen'); // Y軸名稱顏色
        $this->entity->yaxis->title_adjust = $this->rotate ? 'high' : 'middle';
        $this->entity->yaxis->SetTitleMargin($margin);
        if ($this->rotate) { // 讓Y軸座標可以往下點
            $this->entity->yaxis->SetLabelAlign('center', 'top');
            $this->entity->yaxis->SetLabelMargin(-5);
        }
    }
    
    /**
     * 設定X軸資料
     * @param array $data X軸資料
     */
    function setXaxisData($data)
    {
        global $sysSession;
        if ($sysSession->lang == 'Big5') {
            foreach ($data as $k => $v)
                $data[$k] = iconv('UTF-8', 'Big5', $v);
            $this->entity->xaxis->SetFont(FF_BIG5, FS_NORMAL, 10);
        } else if ($sysSession->lang == 'GB2312') {
            foreach ($data as $k => $v)
                $data[$k] = iconv('UTF-8', 'GB2312', $v);
            $this->entity->xaxis->SetFont(FF_SIMSUN, FS_BOLD, 10);
        }
        $this->entity->xaxis->SetTickLabels($data);
    }
    
    /**
     * 設定Y軸資料
     * @param array $data Y軸資料
     */
    function setYaxisData($data)
    {
        global $sysSession;
        $bplot = new BarPlot($data);
        $this->entity->Add($bplot);
        if ($sysSession->env == 'academic')
            $bplot->SetFillGradient('#7070B8', '#ffffff', GRAD_MIDVER);
        else
            $bplot->SetFillGradient('limegreen', 'lightyellow', GRAD_MIDVER);
        $bplot->SetShadow();
        $bplot->value->Show();
        $bplot->value->SetFormat('%d');
        $bplot->value->SetFont(FF_ARIAL, FS_NORMAL);
        $bplot->value->SetColor('black', 'navy');
    }
    
    /**
     * 匯出圖表
     */
    function draw()
    {
        $this->entity->Stroke();
    }
    
    /**
     * 設定圖表邊界
     * @param int $left left margin
     * @param int $right right margin
     * @param int $top top margin
     * @param int $bottom bottom margin
     */
    function setMargin($left, $right, $top, $bottom)
    {
        if ($this->rotate)
            $this->entity->Set90AndMargin($left, $right, $top, $bottom);
        else
            $this->entity->img->SetMargin($left, $right, $top, $bottom);
    }
}