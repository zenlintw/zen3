<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/settings.css" rel="stylesheet" />
<script type="text/javascript" src="hotkey.js"></script>
<script type="text/javascript" src="/lib/common.js"></script>
<div class="box1">
    <div class="title">
        {if $title eq 'reply'}
        {'tabs_reply'|WM_Lang}
        {elseif $title eq 'forward'}
        {'tabs_forward'|WM_Lang}
        {else}
        {'tabs_new'|WM_Lang}
        {/if}
    </div>
    <div class="content" style="padding:0px;">
        <div class="data6">
            <div id="dataDiv" class="content" style="">
                <!-- 撐開距離用 -->
                <div class="layout-hr">
                    <div class="data layout-child">
                        <div class="layout-hr">
                            <div class="key layout-child" style="padding: 0.8em 2em;"></div>
                            <div class="value layout-child" style="padding: 0.8em 2em;"></div>
                        </div>
                    </div>
                </div>
                <form id="post1" name="post1" class="form-horizontal" method="post" action="writing.php" enctype="multipart/form-data">
                    {if $refw neq ''}
                    <input type="hidden" name="status" value="{$refw}">
                    {/if}
                    <input type="hidden" name="isCommUse" value="{$isCommUse}">
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_from'|WM_Lang}</div>
                                <div class="value layout-child">{$profile.username}({$profile.realname})</div>
                            </div>
                        </div>
                        <div class="comment layout-child"></div>
                    </div>
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_to'|WM_Lang}</div>
                                <div class="value layout-child">
                                    <input type="text" name="to" value="{if $refw|strpos:'reply'}{$to}{/if}" size="30" placeholder="{'msg_email'|WM_Lang}">
                                </div>
                                <div class="comment layout-child">{'write_to_msg'|WM_Lang}</div>
                            </div>
                        </div>
                    </div>
                    {if $isCommUse neq ''}
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'mage_folder'|WM_Lang}</div>
                                <div class="value layout-child">
                                    {$folderHtml}
                                </div>
                                <div class="comment layout-child"></div>
                            </div>
                        </div>
                    </div>
                    {/if}
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_priority'|WM_Lang}</div>
                                <div class="value layout-child">
                                    {$priorityHtml}
                                </div>
                                <div class="comment layout-child"></div>
                            </div>
                        </div>
                    </div>
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_subject'|WM_Lang}</div>
                                <div class="value layout-child">
                                    <input type="text" name="subject" value="{$subject}" size="64" maxlength="200">
                                </div>
                                <div class="comment layout-child">{'write_subject_msg'|WM_Lang}</div>
                            </div>
                        </div>
                    </div>
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_content'|WM_Lang}</div>
                                <div class="value layout-child">
                                    {$contentEditor}
                                </div>
                                <div class="comment layout-child"></div>
                            </div>
                        </div>
                    </div>
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_tagline'|WM_Lang}</div>
                                <div class="value layout-child">
                                    {$taglineHtml}
                                </div>
                                <div class="comment layout-child"></div>
                            </div>
                        </div>
                    </div>
                    {if $title eq 'modify'}
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_attachement'|WM_Lang}</div>
                                <div class="value layout-child">
                                    {$delAttachHtml}
                                </div>
                                <div class="comment layout-child"></div>
                            </div>
                        </div>
                    </div>
                    {/if}
                    <div class="layout-hr">
                        <div class="data layout-child">
                            <div class="layout-hr">
                                <div class="key layout-child" for="last_name">{'write_attachement'|WM_Lang}</div>
                                <div class="value layout-child">
                                    <div id="upload_box"><input type="file" name="uploads[]" id="uploads" size="60" /></div>
                                </div>
                                <div class="comment layout-child">{$write_attachment_msg}</div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="divider-horizontal"></div>
                <!-- 撐開距離用 -->
                <div class="layout-hr">
                    <div class="data layout-child">
                        <div class="layout-hr" id="upload_base">
                            <div class="key layout-child" style="padding: 0.8em 2em;"></div>
                            <div class="value layout-child" style="padding: 0.8em 2em;">
                                <button id="btnMoreAtt" class="btn" onclick="more_attachs();">{'more_attach'|WM_Lang}</button>
                                <button id="btnCutAtt" class="btn" onclick="cut_attachs();" data-file="/message/write.tpl">{'del_attach'|WM_Lang}</button>        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="operate" style="">
                <button id="frmSubmit" class="btn btn-blue" onclick="chkData()">{'send'|WM_Lang}</button>
                <button id="btnBack" class="btn btn-gray" onclick="goList();">{'goto_list'|WM_Lang}</button>
            </div>
        </div>
    </div>
</div>
<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>
{$inlineXajaxJS}
<script type="text/javascript">
    {$inlineJS}
</script>