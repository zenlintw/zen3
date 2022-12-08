<link href="{$appRoot}/public/css/third_party/mCustomScrollbar/jquery.mCustomScrollbar.min.css" rel="stylesheet">

{if $courseData.status==0 || ($courseData.status==5 && !$pageEmbed)}
    Access Deny.(no course)
{else}    
    <div id="wrap">
        {include file = "mobile/common/site_header.tpl"}
        {include file = "mobile/common/app_install_tip.tpl"}
        <!-- 課程圖片 -->
        <div class="course-img">
            <img width="399" height="225" src="/lib/app_show_course_picture.php?courseId={$courseData.cpic}{if $assignSch neq $schoolId}&sId={$courseData.spic}{/if}">
        </div>
        <!-- 課程資訊 -->
        <div class="course-info">
            <div class="course-description">
                <div class="course-name" title="{$courseData.caption|WM_Title}">{$courseData.caption|WM_Title}</div>
                <div class="course-time">
                    <div>{'during_registration'|WM_Lang}：</div>
                    <div>{$courseData.enployDateStr}</div><br>
                    <div>{'during_counseling'|WM_Lang}：</div>
                    <div>{$courseData.studyDateStr}</div><br>
                    {if $courseData.credit|intval!=0 }
                        <div>{'td_credit'|WM_Lang}：</div>
                        <div>{$courseData.credit}</div>
                    {/if}
                </div>
            </div>
            <div style="width: 1px; height: 106px; background: transparent url('/public/images/mobile/icon_divider.png')"></div>
            <div class="fee">
                {if $courseData.fee|intval > 0 && $enablePaid}
                    <div style="white-space: nowrap;" class="price-icon">　${$courseData.fee|number_format:0:".":","}</div>
                {else}
                    {if $is_independent_school && !$is_portal_school}
                        <div class="free-icon"></div>
                    {else}
                        <div class="free-icon">{'free'|WM_Lang}</div>
                    {/if}
                {/if}
            </div>
        </div>
        <!-- 社群分享 -->
        <div class="course-share qrcode">
            <div class='title' style='display: none;'><div>{$courseData.caption|WM_Title}</div></div>
            <div class='push' data-id='{$courseData.course_id}' data-description='{$courseData.content}'></div>
            <div class="share">
                {$shareIcon}
            </div>
            <div id="inline-ln-{$courseData.course_id}" class="inline-ln">
                <form class="well">
                    <div>{'linesharenote'|WM_Lang}</div>
                </form>
            </div>
            <!--普仁評估，行動版沒有用wechat-->
            <!--
            <div style="width: auto; height: auto; overflow: auto; position: relative;">
                <div id="inline-wct-{$courseData.course_id}" class="inline-wct">
                    <img src="https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl={$appRoot}/info/{$courseData.course_id}?lang={$nowlang}&choe=UTF-8" style="width: 100%;"/>
                </div>
            </div>
            -->
        </div>
        <!-- 報名、上課按鈕 -->
        <div class="course-action">
            {if $profile.username === 'guest'}
                {if $courseData.enStatus == '1'}<!-- 可報名 -->
                    <button type="button" class="btn btn-primary btn-blue btnAction" onclick="loginreturn();">{'loginsign'|WM_Lang}</button>
                {else}
                    <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'coursenotallowsign'|WM_Lang}');">{'notallowsign'|WM_Lang}</button>
                {/if}
            {else}
                {if $courseData.status==1 || $courseData.status==3}
                    {if $courseData.hasMajored ==1 || $courseData.hasMajored ==2}
                        {if $courseData.hasMajored ==2 && $courseData.status==3}
                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'notallowattend'|WM_Lang}');">{'notallowattend'|WM_Lang}</button>
                            {* <button type="button" class="btn btn-primary btn-large btn-red btnAction" onclick="cancelCourse({$courseData.course_id});">{'withdrawal'|WM_Lang}</button>*}
                        {else}
                            <button type="button" class="btn btn-primary btn-blue btnAction" onclick="gotoCourse('{$courseData.course_id}{if $assignSch neq $schoolId}/{$assignSch}{/if}');">{'attendclass'|WM_Lang}</button>
                            {* <button type="button" class="btn btn-primary btn-large btn-red btnAction" onclick="cancelCourse({$courseData.course_id});">{'withdrawal'|WM_Lang}</button>*}
                        {/if}
                    {else}
                        {if $courseData.enStatus == '1'}<!-- 可報名 -->
                            {if $courseData.use_state!=0}
                                <button type="button" class="btn btn-primary btn-large btn-gray btnAction">{'course_review_flow'|WM_Lang}</button>
                            {else}
                                <button type="button" class="btn btn-primary btn-blue btnAction" onclick="enployCourse({$courseData.course_id}{if $assignSch neq $schoolId},{$assignSch}{/if});">{'signcourse'|WM_Lang}</button>
                            {/if}
                        {else}
                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'coursenotallowsign'|WM_Lang}');">{'notallowsign'|WM_Lang}</button>
                        {/if}
                    {/if}
                {elseif $courseData.status==2 || $courseData.status==4}
                    {if $courseData.hasMajored ==1 || $courseData.hasMajored ==2}
                        <!-- 課程時間狀態 -->
                        {if $courseData.st_period == '0'}<!-- 可上課(期間內) -->
                            {if $courseData.hasMajored ==2 && $courseData.status==4}
                                <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'notallowattend'|WM_Lang}');">{'notallowattend'|WM_Lang}</button>
                                {* <button type="button" class="btn btn-primary btn-large btn-red btnAction" onclick="cancelCourse({$courseData.course_id});">{'withdrawal'|WM_Lang}</button>*}
                            {else}
                                <button type="button" class="btn btn-primary btn-blue btnAction" onclick="gotoCourse('{$courseData.course_id}{if $assignSch neq $schoolId}/{$assignSch}{/if}');">{'attendclass'|WM_Lang}</button>
                                {*<button type="button" class="btn btn-primary btn-large btn-red btnAction" onclick="cancelCourse({$courseData.course_id});">{'withdrawal'|WM_Lang}</button>*}
                            {/if}
                        {elseif $courseData.st_period == '1'}<!-- 不可上課(過期) -->
                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'courseclose'|WM_Lang}。');">{'courseclosed'|WM_Lang}</button>
                        {elseif $courseData.st_period == '2'}<!-- 不可上課(未開始) -->
                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'notstart'|WM_Lang}');">{'notstart'|WM_Lang}</button>
                            {* <button type="button" class="btn btn-primary btn-large btn-red btnAction" onclick="cancelCourse({$courseData.course_id});">{'withdrawal'|WM_Lang}</button>*}
                        {/if}
                    {else}
                        {if $courseData.enStatus == '1'}<!-- 可報名 -->
                            {if $courseData.st_period == '1'}<!-- 課程過期 -->
                                <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'courseclose'|WM_Lang}。');">{'courseclosed'|WM_Lang}</button>
                            {else}
                                {if $courseData.use_state!=0}
                                    <button type="button" class="btn btn-primary btn-large btn-gray btnAction">{'course_review_flow'|WM_Lang}</button>
                                {else}
                                    <button type="button" class="btn btn-primary btn-blue btnAction" onclick="enployCourse({$courseData.course_id}{if $assignSch neq $schoolId},{$assignSch}{/if});">{'signcourse'|WM_Lang}</button>
                                {/if}
                            {/if}
                        {else}
                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'coursenotallowsign'|WM_Lang}');">{'notallowsign'|WM_Lang}</button>
                        {/if}
                    {/if}
                {/if}
            {/if}
        </div>
        <!-- 主要資訊區 -->
        <div class="main">
            <!-- 師資機構 -->
            {if $courseData.teachers|@count >= 1}
                <div class="teacher">
                    <div class="title">
                        <div class='collapse'></div>
                        {'teacher_organizations'|WM_Lang}
                    </div>
                    <div class="content">
                        {assign var=count value=0}
                        {foreach from=$courseData.teachers key=k item=teacherVal}
                            <div style="padding-top:10px;padding-bottom:10px;">
                            <div class="teach-pic"><img src='{$appRoot}/co_showuserpic.php?a={$teacherVal.id}' onerror="javascript:this.src='{$appRoot}/theme/default/learn/co_pic.gif'"></div>
                            <div class="teach-data">{$teacherVal.realname}<br>{$teacherVal.email}</div>
                            <div class="clearboth-block"></div>
                            </div>
                            {if $courseData.teachers|@count == 2  && $k == 0 }
                                <div class="divider"></div>
                            {/if}

                        {/foreach}                  
                    </div>
                </div>
            {/if}  
            <!-- 課程介紹 -->
             <div id="course_intro" class="course-intro">
                 <div class="title">
                    <div class='collapse'></div>
                    {'course_introduction'|WM_Lang}
                </div>
                <div class="content">
                    {if $courseData.subhead neq null}
                        <div class="course-subtitle">{$courseData.subhead}</div>
                    {/if}
                    <div class="course-content"><pre>{$courseData.content}</pre></div>
                    <hr style="margin:10px 15px 10px 15px;">
                    {if 'goal'|in_array:$courseData.is_use && $courseData.goal|@count >= 1}
                        <div class="course-goal">
                            <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'learning_objectives'|WM_Lang}</span></div>
                            <ul>
                            {foreach from=$courseData.goal key=k_goal item=v_goal}
                                <li class="breakword">{$v_goal}</li>
                            {/foreach}
                            </ul>

                        </div>
                    {/if}
                    {if 'audience'|in_array:$courseData.is_use && $courseData.audience|@count >= 1}
                        <div class="course-aud">
                            <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'learning_objects'|WM_Lang}</span></div>
                            <ul>
                            {foreach from=$courseData.audience key=k_audience item=v_audience}
                                <li class="breakword">{$v_audience}</li>
                            {/foreach}
                            </ul>
                        </div>
                    {/if}
                    {if 'ref'|in_array:$courseData.is_use && $courseData.ref_title|@count >= 1}
                        <div class="course-ref">
                            <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'reference_material'|WM_Lang}</span></div>
                            <ul>
                            {foreach from=$courseData.ref_title key=k_texts item=v_texts}
                                <a href="{$courseData.ref_url.$k_texts}" target="_blank" style="color:#000;text-decoration:none"><li class="breakword">{$v_texts}</li></a>
                            {/foreach}
                            </ul>
                        </div>
                    {/if}
                    {if 'formal_score'|in_array:$courseData.is_use || 'formal_time'|in_array:$courseData.is_use || 'formal_process'|in_array:$courseData.is_use}
                        <div class="course-pass" style="margin-bottom:10px">
                            <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'pass_condition'|WM_Lang}</span></div>
                            {if 'formal_score'|in_array:$courseData.is_use}
                                <div style="margin:0 20px 0 6px;" class="breakword">{'grade1'|WM_Lang}：{$courseData.fair_grade} {'fraction'|WM_Lang}</div>
                            {/if}
                            {if 'formal_time'|in_array:$courseData.is_use}
                                <div style="margin:0 20px 0 6px;" class="breakword">{'reading_time'|WM_Lang}：{$courseData.formal.time} {'unit_hour'|WM_Lang}</div>
                            {/if}
                            {if 'formal_process'|in_array:$courseData.is_use}
                                <div class="progress-info">
                                    <div class="name"> {'teaching_schedule'|WM_Lang}：</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped" style="background-color: #048e89;width: {$courseData.formal.percent}%;">
                                            {*<div id="progressBar" class="bar" ></div>*}
                                        </div>
                                    </div>
                                      {$courseData.formal.percent}%
                                </div>
                            {/if}
                        </div>
                    {/if}
                    {if 'gallery_time'|in_array:$courseData.is_use || 'gallery_process'|in_array:$courseData.is_use}
                        <div class="course-gallery-pass">
                            <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'novitiate'|WM_Lang}</span></div>
                            {if 'gallery_time'|in_array:$courseData.is_use}
                                <div style="margin:0 20px 0 6px;" class="breakword">{'reading_time'|WM_Lang}：{$courseData.gallery.time} {'unit_hour'|WM_Lang}</div>
                            {/if}
                            {if 'gallery_process'|in_array:$courseData.is_use}
                                <div class="progress-info">
                                    <div class="name"> {'teaching_schedule'|WM_Lang}：</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped" style="background-color: #048e89;width: {$courseData.gallery.percent}%;">
                                            {*<div class="bar"></div>*}
                                        </div>
                                    </div>
                                      {$courseData.gallery.percent}%
                                </div>
                            {/if}
                        </div>
                    {/if}
                </div>
            </div>
            <!-- 課程安排 -->
            {if $titleAry|@count >= 1}
                <div id="course_plan" class="course-plan">
                     <div class="title">
                        <div class='collapse'></div>
                        {'course_plan'|WM_Lang}
                    </div>
                    <div class="content">
                        <ul>
                            {foreach from=$titleAry item=title name=titleFor}
                                <li>
                                    <div class="course-set-circle"></div>
                                    <div class="course-set breakword">{$title}</div>
                                </li>
                                {if not $smarty.foreach.titleFor.last}
                                    <div class="course-set-line"></div>
                                {/if}
                            {/foreach}
                        </ul>                    
                    </div>
                </div>
            {/if}
            <!-- 相關課程 -->
            {if $courseData.relativeCourses|@count >= 1}
                <div id="course_relation" class="course-relation">
                     <div class="title">
                        <div class='collapse'></div>
                        {'related_courses'|WM_Lang}
                    </div>
                    <div class="content">
                        <div class="rel-course">
                            <div id="main" role="main" class="padding-left-15 padding-right-15">
                                <ul id="tiles">
                                    <div class="lcms-items row">
                                        {foreach from=$courseData.relativeCourses key=k item=v}
                                            <div class="col-xs-6 col-sm-4 item-frame">
                                                {include file = "common/course_item.tpl"}
                                            </div>
                                        {/foreach}
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <!-- dialog -->
            <a href="#goCsModal" id="goCsBtn"></a>
            <div class="group-box" id="goCsModal">
                <div class="group-box-title">{'msg_which_way'|WM_Lang}</div>
                <div class="group-box-content">
                    <button id="appToCs" type="button" class="btn btn-lg btn-warning btn-full" style="margin-bottom: 0.5em;">{'btn_with_app'|WM_Lang}</button> 
                    <button id="pcToCs" type="button" class="btn btn-lg btn-default btn-full">{'btn_with_pc'|WM_Lang}</button>
                </div>
            </div>
        </div>
    </div>
    {include file = "mobile/common/site_footer.tpl"}
    
    <script type="text/javascript">
        var nowlang = '{$nowlang}';
        var confirmsign = "{'confirmsign'|WM_Lang}";
    </script>
    <script type="text/javascript" src="{$appRoot}/mooc/public/js/course_share.js"></script>
    <script type="text/javascript" src="{$appRoot}/public/js/third_party/bootbox/bootbox.min.js"></script>
    <script>
    {literal}
        function setItemCoverHeight  () {
            var $list       = $( '.lcms-items' ),
                $items      = $list.find( '.lcms-item .cover' );
            $items.css('height', Math.floor($items.width()*540/960));

        }
        function gotoCourse(csid)
        {
            $("#goCsBtn").click();
            $("#pcToCs").data('direct', csid);
        }

        function enployCourse(csid, sid)
        {
            if (confirm(confirmsign))
            {
                if (sid != null) {
                    document.location.href = "/enploy/"+csid+"/"+sid;
                } else {
                    document.location.href = "/enploy/"+csid;
                }
            }
        }
        //如是 Guest 身分，須先登入後報名，將課程ID用 GET 傳出，以利登入後返回原課程網頁
        function loginreturn(){
            document.location.href = "/mooc/login.php?reurl="+encodeURIComponent(location.href);
        }
        $(window).on( 'resize', setItemCoverHeight);
        $( document ).ready(function() {
            setItemCoverHeight();
            // 啟用 fancybox
            $("#goCsBtn").fancybox({
                'padding' : 0,
                'margin' : 0,
                // 'modal' : true,
                helpers: { 
                    title: null
                }
            });
            /* 內容展開 */
            $(".main .teacher .title, .main .course-intro .title, .main .course-plan .title, .main .course-relation .title").on('click', function() {
                $(this).parent().find(".content").toggle();
                $(this).toggleClass('expand');
            });
            $("#appToCs").on('click', function () {
                document.location = 'hgwmgeneral://';
            });
            $("#pcToCs").on('click', function () {
                document.location.href = "/"+$(this).data("direct");
            });
        });
    {/literal}
    </script>
{/if}
