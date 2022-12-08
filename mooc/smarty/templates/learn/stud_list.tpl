{if !$profile.isPhoneDevice}
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
{/if}
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
{literal}
<style>
    .box1 {
        min-width: 35em;
    }

    .box2 {
        margin-top: 1.9em;
    }

    /*¤â¾÷¤Ø¤o*/
    @media (max-width: 767px) {
        .box1 {
            min-width: initial;
        }

        .box2 {
            margin-top: initial;
        }
    }
</style>
{/literal}
<div class="box1" style="">
    <div class="title">{'title69'|WM_Lang}</div>
    <div class="content">
        <div class="box2" style="">
            <div class="row">
                {assign var=num value=0}
                {foreach from=$datalist key=k item=v}
                <div class="col-xs-6 col-sm-3" style="min-height: 18em;">
                    <div style="text-align: center;">
                        {$v.photo}
                    </div>
                    {$v.homepage}
                    <div class="item" style="line-height: 1.9em; margin-top: 1em;">
                        {if ($v.img_role ne '')}
                            <span title="{'position'|WM_Lang}" style="position: relative; top: -0.2em;">{$v.img_role}</span>
                        {/if}
                        <span style="font-size: 1.2em; font-weight: bold; position: relative; top: 0.1em; margin-right: 0.1em;" title="{'realname'|WM_Lang}">{$v.realname}</span>
                        <span style="color: #afafaf;" title="{'username'|WM_Lang}">{$v.username}</span>
                    </div>
                    <div class="item" style="line-height: 1.9em;" title="{'email'|WM_Lang}"><a href="mailto:{$v.email}">{$v.email}</a></div>
                    {if ($v.cell ne '' || $v.home_tel ne '' || $v.office_tel ne '' || $v.home_address ne '' || $v.office_address ne '' || $v.home_fax ne '')}
                    <div class="pull-right more-info" style="cursor: pointer; color: #000000;">
                        <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                        <span>{'more_info'|WM_Lang}</span>
                    </div>
                    {/if}
                    <div class="info" style="display: none;">
                        {if ($v.cell ne '')}
                        <div class="item" style="line-height: 1.9em;" title="{'cell_phone'|WM_Lang}">{$v.cell}</div>
                        {/if}
                        {if ($v.home_tel ne '')}
                        <div class="item" style="line-height: 1.9em;" title="{'home_tel'|WM_Lang}">{$v.home_tel}</div>
                        {/if}
                        {if ($v.office_tel ne '')}
                        <div class="item" style="line-height: 1.9em;" title="{'office_tel'|WM_Lang}">{$v.office_tel}</div>
                        {/if}
                        {if ($v.home_address ne '')}
                        <div class="item" style="line-height: 1.9em;" title="{'home_address'|WM_Lang}">{$v.home_address}</div>
                        {/if}
                        {if ($v.office_address ne '')}
                        <div class="item" style="line-height: 1.9em;" title="{'office_address'|WM_Lang}">{$v.office_address}</div>
                        {/if}
                        {if ($v.home_fax ne '')}
                        <div class="item" style="line-height: 1.9em;" title="{'home_fax'|WM_Lang}">{$v.home_fax}</div>
                        {/if}      
                        <div class="pull-right less-info" style="cursor: pointer; color: #000000;">
                            <span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span>
                            <span>{'less_info'|WM_Lang}</span>
                        </div>
                    </div>
                </div>
                {assign var=num value=$num+1}
                {if $num % 2 ==0}<div class="clearfix visible-xs-block"></div>{/if}
                {/foreach}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="stud_list.js"></script>