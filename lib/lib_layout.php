<?php
    /*
     * Custom : 首頁各區塊顯示
     */
    require_once(sysDocumentRoot . '/mooc/models/forum.php');
    require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
    require_once(sysDocumentRoot . '/lang/mooc.php');

    //浮動視窗
    function ShowContact(){
        global $MSG, $sysConn, $sysSession;
        
        $sysConn->Execute('use ' . sysDBschool);
        $headers = apache_request_headers();
        if(preg_match('/Trident\/(\d+)/', $headers['User-Agent'], $regs) && intval($regs[1])>6) $ie11_tag = "-30";

        $qswitch = getqSwitch();
        $cswitch = getSwitch();
        $show_quick = '';

        if ('true' === $qswitch['quick_sw']['onlinehelp']) {
            $show_quick  = '<br><a href="#" id="totop"><img src="../theme/default/learn_mooc/online.png" border="0" style="margin:3px 0 0 0" /></a>';
            //$show_quick .= '<br><a href="#" id="totop"><img src="../theme/default/learn_mooc/offline.png" border="0" style="margin:3px 0 0 0" /></a>';
        }
        if ('true' === $cswitch['content_sw']['courselist']) {
            $show_explore = '<a href="#" id="totop"><img src="../theme/default/learn_mooc/top.png" border="0" style="margin-top: 10px;" /></a>
                            <a href="#" onclick="window.location=\'/mooc/explorer.php\';" style="text-decoration: none;">
                                <div id="rcorners1">
                                    <div>
                                        <img src="../theme/default/learn_mooc/discover_icon.png" border="0" style="margin-top: 6px;" />
                                    </div>
                                    <div>
                                          <span style="font-size:12px;color:#fff;margin-top:3px;">'.$MSG['explorecourse'][$sysSession->lang].'</span>
                                    </div>
                                </div>
                            </a>
                            <a href="javascript:;" id="btnHistory" style="text-decoration: none;">
                                <div id="rcorners2">
                                        <div>
                                             <img src="../theme/default/learn_mooc/history_icon.png" border="0" style="margin-top: 6px;" />
                                        </div>
                                        <div>
                                             <span style="font-size:12px;color:#fff;margin-top:3px;">'.$MSG['historycourse'][$sysSession->lang].'</span>
                                        </div>
                                </div>
                            </a>
                            <a href="javascript:;" id="btnSigning" style="text-decoration: none;">
                                <div id="rcorners3">
                                     <div>
                                          <img src="../theme/default/learn_mooc/course_icon.png" border="0" style="margin-top: 6px;" />
                                     </div>
                                     <div>
                                          <span style="font-size:12px;color:#fff;">'.$MSG['commencementcourse'][$sysSession->lang].'</span>
                                     </div>
                               </div>
                           </a>';
        }

        $ContactHtml = <<<BOF
                <div id="abgne_float_ad" style="display: none;position: absolute;z-index:10;">
                    {$show_explore}
                    {$show_quick}
                </div>

                <!--右方浮動視窗-->
                                <script type="text/javascript">
                    $(window).load(function(){
                        var win = $(window),
                            ad = $('#abgne_float_ad').css('opacity', 0).show(),     // 讓廣告區塊變透明且顯示出來
                            _width = ad.width(),
                            _height = ad.height(),
                            _diffY = $(window).height()/2,
                            _diffX = 0,
                            _moveSpeed = 800;     // 移動的速度

                        // 先把 #abgne_float_ad 移動到定點
                        ad.css({
                            top: $(document).height(),
                            left: win.width() - _width - _diffX,
                            opacity: 1
                        });

                        // 幫網頁加上 scroll 及 resize 事件
                        win.bind('scroll resize', function(){

                            // 控制 #abgne_float_ad 的移動
                            ad.stop().animate({
                                top: $(window).scrollTop() + ($(window).height()/2) - 25,
                                left: $(window).scrollLeft() + $(window).width() - _width - _diffX
                            }, _moveSpeed);
                        }).scroll();     // 觸發一次 scroll()

                        $('a#totop').click(function(){
                            // 讓捲軸移動到 0 的位置
                            $('html, body').scrollTop(0);

                            return false;
                        });

                     });

                </script>
BOF;

        return $ContactHtml;
    }

    //輪播banner
    function ShowAds(){
        global $sysConn, $MSG, $sysSession;

        $data_loop = '';

        $tmpadsdata = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id like 'ads%' ");

        foreach ($tmpadsdata as $k => $v) {
            $adsdata[$v['portal_id']][$v['key']] = $v['value'];
        }

        if ( empty($adsdata) )    return '';

        foreach ($adsdata as $k => $v) {
            //判斷檔案是否存在
            if ( !file_exists(sysDocumentRoot.$v['pic_path']) )	continue;

            $link = $v['url'];
            $pic = $v['pic_path'];

            $data_loop .= '<li><a href="'.$link.'" target="_blank"><img src="'.$pic.'" width="100%"
                height="auto" border="0"/></a></li>';


        }
        
        
        // 取得最新消息討論版名稱
        dbGetNewsBoard($newsresult); //取得新聞板號
        $rsForum = new forum();
        $news = $rsForum->getForumNameByBid($newsresult['board_id']);
     
        // 取得第一個最新消息討論版文章
         $news_forumData = getBannnerForumData($newsresult['board_id']);
         $news_data_loop ='';
        if(isset($news_forumData)){
              foreach($news_forumData as $k => $v){
                $news_data_loop .= '<tr data-cid="'.$v['cid'].'" data-bid="'.$v['boardid'].'" data-nid="_'.$v['node'].'" style="cursor:pointer;"><td nowrap="nowrap"><div><img src="../theme/default/learn_mooc/news_red_point.png" border=0 style="margin-left:8px;margin-right:8px"/>'.$v['postdate'].'</div><div>'.mb_substr($v['subject'], 0, 7,'utf8').'......</div></td></tr>';
              }
         } else {
             $news_data_loop = $MSG['msg_no_course'][$sysSession->lang];
         }
        // 是否顯示搜尋 bar
        $cswitch = getSwitch();
        if ('true' === $cswitch['content_sw']['searchbar']) {
            // 取位置設定
             $tmpSearchSettings = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'searchbar' ");

            foreach ($tmpSearchSettings as $k => $v) {
                $ssdata[$v['key']] = $v['value'];
            }
            if (isset($ssdata)) {
                $sTop = 'top: '.(($ssdata['y'] >= 190)?194:($ssdata['y'] <= 0?4:$ssdata['y']+4)).'px;';
                $sLeft = 'left: '.(($ssdata['x'] >= 472)?476:($ssdata['x'] <= 0?4:$ssdata['x']+4)).'px;';
            }
            $searchBar =    '<div class="search" style="'.$sTop.$sLeft.'">
                                <div class="input-append">
                                    <input name="keyword" id="bar-keyword" type="text" value="" placeholder="'.$MSG['searchcourse'][$sysSession->lang].'">
                                    <button class="btn btn-gray-light" type="button" onclick="adv_search();"><i class="icon-search icon-white"></i></button>
                                </div>
                            </div>';
        }
        //回傳區塊
        $rtn = <<< BOF
        <style>
            #show_mid > .search {
                position: absolute;
                top: 154px;
                left: 54px;
                -webkit-border-radius: 2px;
                -moz-border-radius: 2px;
                -ms-border-radius: 2px;
                -o-border-radius: 2px;
                border-radius: 2px;
                background-color: #DCDCDC;
                display: inline-block;
                box-shadow: inset 0 0 0 1px #555,0 0 0 4px rgba(255,255,255,0.5),0 0 4px 6px rgba(0,0,0,0.25);
            }
            #show_mid > .search > .input-append {
                  margin-bottom: 0;
            }
            #show_mid > .search > .input-append > input {
                -webkit-border-radius: 0;
                -moz-border-radius: 0;
                -ms-border-radius: 0;
                -o-border-radius: 0;
                border-radius: 0;
                line-height: 19px;
                height: 25px;
                box-shadow: 0 0 0 0;
                width: 399px;
                font-size: 10pt;
                color: #000000;
            }
            #show_mid > .search > .input-append  > button {
                padding: 3px 23px;
            }
            #news_show_right {
                left:378px;
            }
            @media (min-width: 1024px) {
                #news_show_right {
                    left:399px;
                }
            }
        </style>
        <div style="text-align:center;overflow: hidden;">
          <div id="mSlidebox_3" class="slidebox" style="display: inline-block;">
                <div id="show_mid">
                    <ul style="height: 226px;">
                        $data_loop
                    </ul>
                    $searchBar
                </div>
                <div><img src="/public/images/banner_shadow.png" border=0 /></div>
          </div>
          <div id="news_show_right" style="position:relative; display:block; top:-249px; z-index:0;margin:0 auto;width:221px;">
              <div id="news_show_msg" style="display:inline-block; width:186px; height:179px;float:left;">
                    <div id='news_show_content' style="background-color:#fff;height:175px;display:none"> 
                        <table cellpadding="8">
                            $news_data_loop
                        </table>
                    </div>
              </div>
              <div class="newButton" style="cursor:pointer;color:#fff; padding-top:9px;font-size:large;font-weight: bold;width:35px; height:167px;display:inline-block;float:right;" id="newsButton">
                  <div style="width: 112px; transform: rotate(90deg); position: relative; top: 2.4em; left: -2.1em;">{$MSG['latestnews'][$sysSession->lang]}</div>
                  <img id='BottomImg' src="../theme/default/learn_mooc/banner_news_0.png" border=0 style="margin-top: 92.3px;" /></div>
                <div class="clearboth"></div>
            </div>
        </div>
        <div style="clear:both;"></div>

        <script type="text/javascript">
        $(document).ready(function(){
            $("#mSlidebox_3").mSlidebox({
                autoPlayTime:3000,
                easeType:"easeInOutCirc",
                pauseOnHover:true
            });
        });
        </script>

BOF;
        return $rtn;
    }


    //內容開關
    function getSwitch(){
        global $sysConn;
        $sysConn->Execute('use ' . sysDBschool);
        $tmpswdata = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'content_sw' ");

        foreach ($tmpswdata as $k => $v) {
            $swdata[$v['portal_id']][$v['key']] = $v['value'];
        }

        return $swdata;
    }

    // 自訂標題名稱
    function getTitle(){
        global $sysConn;

        $tmpswdata = $sysConn->GetArray("SELECT * FROM `WM_portal` where `key` = 'title' ");

        foreach ($tmpswdata as $k => $v) {
            $swdata[$v['portal_id']][$v['key']] = $v['value'];
        }

        return $swdata;
    }

     //快捷開關
    function getqSwitch(){
        global $sysConn;

        $tmpqswdata = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'quick_sw' ");

        foreach ($tmpqswdata as $k => $v) {
            $qswdata[$v['portal_id']][$v['key']] = $v['value'];
        }

        return $qswdata;
    }


    //中間區塊排序
    function getListseq(){
        global $sysConn;

        $tmplistdata = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'content_pri' order by value asc");

        foreach ($tmplistdata as $k => $v) {
            $listdata[$v['key']] = $v['value'];
        }

        return $listdata;
    }

    //品牌大街
    function ShowFlexisel()
    {
        global $sysConn,$appRoot, $sysSession;

        $sysConn->Execute('use ' . sysDBname);

        $tmpbranddata = $sysConn->GetArray("SELECT * FROM WM_school
            where school_host NOT LIKE '[delete]%' order by rand() ");


        $brand_html = '';
        $count = 0;
        foreach ( $tmpbranddata as $k => $v ) {

            $db = sysDBprefix.$v['school_id'];

            $sysConn->Execute('use ' . $db);

            $path = $sysConn->GetOne("SELECT value FROM WM_portal where portal_id ='brand' ");

            $link = 'http://'.$v['school_host'];

            if (''!=$path) {
                $brand_html .= '<li><a href="'.$link.'"><div style="width:190px;height:100px;border:1px solid #e2e2e2;">
                    <img src="'.$path.'?'.time().'" /></div></a></li>';
                $count++;
            }


        }

        if ($count<10) {
            $num  = 10-$count;
            for ($i=0;$i<$num;$i++) {
                $brand_html.='<li><a href="#"><div style="width:190px;height:100px;border:1px solid #e2e2e2;">
                    <img src="../theme/default/learn_mooc/default_brand.png" /></div></a></li>';
            }
        }
        $db = sysDBprefix.$sysSession->school_id;

        $sysConn->Execute('use ' . $db);
        return $brand_html;
    }

    //自訂一
    function ShowCustom1(){
        global $sysConn;

        $tmpcus1data = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'custom1' ");

        foreach ($tmpcus1data as $k => $v) {
            $cus1data[$v['key']] = $v['value'];
        }


        if ('full'==$cus1data['pic_style']) {
                $Custom1Html = <<<BOF
                <style type="text/css">
                    .custom1-nav-tabs {
                       margin: auto;
                    }
                </style>
                <div class="custom1-nav-tabs">
                    <a href="{$cus1data['url']}"><img src="{$cus1data['pic_path']}" style="width: 100%;"></a>
                </div>
BOF;
        } else {
                $Custom1Html = <<<BOF
                <div style="margin:auto;width:950px">
                    <a href="{$cus1data['url']}"><img src="{$cus1data['pic_path']}" style="width:950px;"></a>
                </div>
BOF;
        }


        return $Custom1Html;
    }


    //自訂二
    function ShowCustom2(){
        global $sysConn;

        $tmpcus2data = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'custom2' ");

        foreach ($tmpcus2data as $k => $v) {
            $cus2data[$v['key']] = $v['value'];
        }


        if ('full'==$cus2data['pic_style']) {
                $Custom1Htm2 = <<<BOF
                <style type="text/css">
                    .custom2-nav-tabs {
                       margin: auto;
                    }
                </style>
                <div class="custom2-nav-tabs">
                    <a href="{$cus2data['url']}"><img src="{$cus2data['pic_path']}" style="width: 100%;"></a>
                </div>
BOF;
        } else {
                $Custom1Htm2 = <<<BOF
                <div style="margin:auto;width:950px">
                    <a href="{$cus2data['url']}"><img src="{$cus2data['pic_path']}" style="width:950px;"></a>
                </div>
BOF;
        }

        return $Custom1Htm2;
    }

    //內容廠商
    function getCompany(){
        global $sysConn,$sysSession;
        $tmpcompanydata = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'represent' ");
        foreach ($tmpcompanydata as $k => $v) {
            $companydata[$v['key']] = $v['value'];
        }

        $company['pic_path'] = $companydata['pic_path'];

        $company['school_name'] = $sysConn->GetOne("SELECT banner_title1 FROM " . sysDBprefix . "MASTER.CO_school
            where school_id=$sysSession->school_id ");

        // $company['school_name'] = $sysSession->school_name;
        
        return $company;
    }

    // 課程群組類別
    function getGroup(){
        global $sysConn, $sysSession, $MSG;

        $groupdata = $sysConn->GetArray("SELECT B.* FROM WM_term_group A JOIN WM_term_course B ON A.child= B.course_id
            where A.parent = 10000000 order by A.permute ASC, B.kind ASC" );

        $arr_group = array();

        $data_loop = '';
        $count = 2;
        foreach ($groupdata as $k => $v) {
            $multiCaption = getCaption($v['caption']);
            $caption = $multiCaption[$sysSession->lang];

            $data_loop .='<li><a href="#'.$count.'" id="'.$v['course_id'].'">'.$caption.'</a></li>';
            $count++;
        }




        $rtn = <<< BOF
        <nav>
            <ul>
                <li><a href="#1" id="">{$MSG['group_all'][$sysSession->lang]}</a></li>
                {$data_loop}
            </ul>
        </nav>

        <script type="text/javascript">
            function checkGroupHeight() {
                var groupHeight = $(".lcms-nav-tabs nav").height(),
                    groupLineHeigt = $(".lcms-nav-tabs nav li a").outerHeight();
                /* 判斷是否到第二行 */
                if (groupHeight > groupLineHeigt*2) {
                    $('.lcms-nav-tabs .mooc-nav-more').show();
                } else {
                    $('.lcms-nav-tabs .mooc-nav-more').hide();
                }
            }
            $(window).resize(function(){
                checkGroupHeight();
            });
            $(document).ready(function(){
                checkGroupHeight();
                /* 展開收起 group */
                $('.lcms-nav-tabs .mooc-nav-more').on('click', function() {
                    var obj = $(this);
                    var objGroup = obj.parent().find('.lcms-nav-group');
                    if(objGroup.hasClass('nav-qroup-collapse')) {
                        obj.find('.text').text('less');
                        obj.find('.caret').addClass('caret-reversed');
                    } else {
                        obj.find('.text').text('more');
                        obj.find('.caret').removeClass('caret-reversed');
                    }
                    objGroup.toggleClass('nav-qroup-collapse');
                });
                $('a[href="#1"]').parent().addClass('active');
                $('nav').find('a').on('click', function () {
                    $('nav').find('li').removeClass('active');
                    $(this).parent().addClass('active');
                    if (''==this.id) {
                        getCourseList('getSigningCourses', '');
                    } else {
                        getCourseList('getTreeCourses', this.id);
                    }
                    /* scroll to # */
                    $('html, body').animate({scrollTop:$('#course-tabs').position().top}, 'slow');
                });
            });
        </script>

BOF;


        return $rtn;
    }

    //searchbar底圖
    function getSearchImg(){
        global $sysConn;

        $tmpdata = $sysConn->GetArray("SELECT * FROM WM_portal where portal_id = 'ads001' ");

        foreach ($tmpdata as $k => $v ) {
            $searchdata[$v['key']] = $v['value'];
        }

        return $searchdata['pic_path'];
    }

    // 取最新的討論版3個
    function getSchoolForumList() {
        $rsForum = new forum();
        // jill：僅須過濾NEW、FAQ
        dbGetNewsBoard($result_faq, "faq");
        dbGetNewsBoard($result_news, "news");
        $excBids = array($result_faq['board_id'], $result_news['board_id']);
        $forumList = $rsForum->getSchoolForumList(false, array(), array(), $excBids);
         
        return $forumList;
    }

    // 取指定討論版文章
    function getSchoolForumData($bid) {
        $rsForum = new forum();
        $forumData = $rsForum->getForumData($bid);

        return $forumData;
    }
    
    // 取討論版最新消息文章
    function getDiscussNewsForumData($bid) {
        $rsForum = new forum();
        $forumData = $rsForum->getNewsForumData($bid);

        return $forumData;
    }
    
    /*banner 最新消息
     * banner 最新消息用討論版文章
     *
     * @param string $bid 討論版版號
     * @param integer $curPage 第幾頁
     * @param integer $perPage 每頁幾筆
     * @param string $keyword 關鍵字
     * @param string $sort 排序欄位
     * @param string $order 升冪、降冪
     */
    // getBbsPosts($bid, $nid = array(), $onlyTopic = '0', $curPage = 1, $perPage = 10, $keyword = '',
    //  $sort = '', $order = '')
    function getBannnerForumData($bid) {
            $rsForum = new forum();
            $forumList = $rsForum->getBbsPosts(
            $bid,
            array(),
            '0',
            1,
            3,
            '',
            'pt',
            'desc'
        );

        $forumData = $forumList['data'];
        return $forumData;
    }