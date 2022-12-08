$('#start').data('fileItemCount', 0);
$(function () {
    'use strict';
    var url = '/lib/jQuery-File-Upload/server/php/index.php?currPath=' + currPath;
    // 沒有檔案的警示歸0
    $('#displayPanel').data('nofilealarm', '0');
    $('#fileupload').fileupload({
        url: url,
        dropZone: $('body'),
        dataType: 'json',
        autoUpload: false,
        maxRetries: 100,
        retryTimeout: 500,
        add: function (e, data) {
            if (window.console) {
                console.log('拖曳的檔案名稱', data.files[0].name);
//                console.log('檔案大小KB', parseInt(data.files[0].size/1024, 10));
            }
//            if (window.console) {
//                console.log('單一檔案上限KB', upload_max_filesize);
//            }

            var errMsg = '';

            // 判斷單一檔案上限
//            if (data.files[0].size > upload_max_filesize) {
//                errMsg = msg.file_size_exceeds_limit[nowlang];
//            }
            if (window.console) {
                console.log('加入前，剩餘容量KB', post_max_size);
            }

            // 判斷剩餘容量
            if (post_max_size - parseInt(data.files[0].size/1024, 10) <= 0) {
                errMsg = msg.exceeds_remaining_space_contact_admin[nowlang];
            } else {
                post_max_size = post_max_size - parseInt(data.files[0].size/1024, 10);
            }
            if (window.console) {
                console.log('加入後，剩餘容量KB', post_max_size);
            }

            // 判斷異動儲存沒
//            if (window.console) {console.log('add');}
//            if (window.console) {console.log('沒儲存', notSave);}
//            if (window.console) {console.log('fancybox', $('.fancybox-opened').length);}
            if (notSave === true && $('.fancybox-opened').length === 0) {
//                if (window.console) {console.log(msg.msg_save[nowlang]);}
                $("#start").off('click');
                alert(msg.msg_save[nowlang]);
                return false;
            }
            // 上傳檔案
            $("#start").click(function(){
//                if (window.console) {
//                    console.log('start');
//                    console.log($(this).data('node'));
//                }
                // 是否有可以上傳的
                if ($('.itemCancel').length === 0) {
                    if ($('#displayPanel').data('nofilealarm') === '0') {
                        alert(msg.nofile[nowlang]);
                    }
                    // 沒有檔案的警示設定為1，表示警告過了
                    $('#displayPanel').data('nofilealarm', '1');
                    return false;
                } else {
//                    console.log(data);
                    var jqXHR = data.submit();
                    $("#start").attr('value', msg.dont_turn_off_page[nowlang]);
                    $("#start").attr('disabled', true);

                    // 移除取消上傳按鈕，避免過程中被中斷
                    $('.itemCancel').hide();
                }
            });

            // 顯示進度列
            var tpl = $('<tr class="cssTrEvn" style="text-align:center; height: 3em;">\
                            <td class="itemNum" style="text-align:center;"></td>\n\
                            <td class="itemFileName" style="text-align:left;"><div style="width: 19em; overflow: hidden;"></div></td>\n\
                            <td class="itemFileSize" style="text-align:right;padding-right:5px;width: 8em;"></td>\n\
                            <td class="itemProgress" style="text-align:center;width:101px;position: relative;">\n\
                                <div class="bar progress-bar progress-bar-striped active" style="background-color:#428BCA;width: 0px;block:inline;text-align:center;color:white; height: 3em;">&nbsp;</div>\n\
                                <div class="pro" style="position: relative; position: absolute; top: 9px; right: 22px;" /></div>\n\
                            </td>\n\
                            <td class="itemActions" style="text-align:center; width: 6em;"><button class="itemCancel" style="z-index: 2; position: relative;">' + msg.cancel_upload[nowlang] + '</button></td>\n\
                        </tr>');

            // 流水號
            $('#start').data('fileItemCount', $('#start').data('fileItemCount') + 1);
            tpl.find('.itemNum').text($('#start').data('fileItemCount'));

            // 檔名
            tpl.find('.itemFileName div').text(data.files[0].name).attr('title', data.files[0].name);

            // 檔案大小
            if (data.files[0].size >= 1024) {
                tpl.find('.itemFileSize').html('<span>' + parseInt(data.files[0].size/1024, 10)+'</span> KB');
            }else{
                tpl.find('.itemFileSize').html('<div style="display: none;"><span >' + parseInt(data.files[0].size/1024, 10)+'</span> KB</div>' + data.files[0].size + ' B');
            }

            // 取消上傳
            tpl.find('.itemCancel').click(function(){
                data.files[0] = '';
                tpl.find('.itemNum,.itemFileName,.itemFileSize').css('text-decoration', 'line-through');
                tpl.css('background-color', '#d6d6d6');
                tpl.find('.itemProgress').text(msg.cancel_upload[nowlang]);
                tpl.find('.itemActions').empty();

                // 取消上傳後要加回剩餘容量
                post_max_size = post_max_size + parseInt(tpl.find('.itemFileSize').find('span').text(), 10);
                if (window.console) {
                    console.log('取消後，剩餘容量KB', post_max_size);
                }

                // 新上傳的檔案總大小
                $('#total-size').text(parseInt($('#total-size').text(), 10) - parseInt(tpl.find('.itemFileSize').find('span').text(), 10));
            });

            // 超量處理
            if (errMsg) {
                data.files[0] = '';
                tpl.find('.itemNum,.itemFileName,.itemFileSize').css('text-decoration', 'line-through');
                tpl.css('background-color', '#d6d6d6');
                tpl.find('.itemProgress').text(errMsg);
                tpl.find('.itemActions').empty();
            }

            data.context = tpl.appendTo($("#files-tables"));

            // 新上傳的檔案總大小
            var filesize;
            if (data.files[0] === '') {
                filesize = 0;
            } else {
                filesize = data.files[0].size;
            }
            $('#total-size').text(parseInt($('#total-size').text(), 10) + parseInt(filesize/1024, 10));
        },
        fail: function (e, data) {
            if (window.console) {
                console.log('fail');
                console.log(data.errorThrown);
                console.log(data.textStatus);
                console.log(data.jqXHR);
            }

            var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload'),
                retries = data.context.data('retries') || 0,
                retry = function () {
                    $.getJSON('/lib/jQuery-File-Upload/server/php/index.php', {currPath:currPath, file: data.files[0].name})
                        .done(function (result) {
                            var file = result.file;
                            data.uploadedBytes = file && file.size;
                            data.data = null;
                            data.submit();
                        })
                        .fail(function () {
                            fu._trigger('fail', e, data);
                        });
                };
            if (data.errorThrown !== 'abort' &&
                    data.uploadedBytes < data.files[0].size &&
                    retries < fu.options.maxRetries) {
                retries += 1;
                data.context.data('retries', retries);
                window.setTimeout(retry, retries * fu.options.retryTimeout);
                return;
            }
            data.context.removeData('retries');
        },
        done: function (e, data) {
            // 取消上傳，檔名會被清空
            if (data.files[0].name === undefined) {
               return false;
            }

            // 新增模式
            if ($('#start').data('node') === 'root') {
                if (window.console) {
                    console.log('新增模式');
                }
                parent.c_main.executing(1);
            // 插入模式
            } else {
                if (window.console) {
                    console.log('插入模式');
                }
                // 選取核取方塊
//                if (window.console) {
//                    console.log($('#start').data('node'));
//                }
                $("input[name='" + $('#start').data('node') + "']").attr('checked', true);
                // 新增表單
                parent.c_main.executing(2);
            }
            // 塞原新增節點表單
//            if (window.console) {
//                console.log(data);
//            }
            $("input[name='node_type'][value='1']").attr('checked', true);// 教材網頁

            if (window.console) {console.log(data.files[0].name);}

//            $('#tb_multi_lang_1').find("input[type='text'][name='title[" + nowlang + "]']").val(data.files[0].name.substr(0, data.files[0].name.lastIndexOf('.')));// 單語系
            $('#tb_multi_lang_1').find("input[type='text']").val(data.files[0].name.substr(0, data.files[0].name.lastIndexOf('.')));// 多語系
            $("input[name='url']").val(data.files[0].name);// 路徑

            var tmpfile = data.files[0].name;
            var strtype = tmpfile.substring(tmpfile.length - 4, tmpfile.length);
            strtype = strtype.toLowerCase();
            if(strtype=='.pdf') $("#node_download").attr('checked', true);
//                $('#nodeSetupPanel').hide();
            // 填寫完成送出節點表單
            nodeSetupDone(true);

            // 插入模式，自動成為子節點
            if ($('#start').data('node') !== 'root') {
                var insert_node_name = $("input[name='" + $('#start').data('node') + "']").parent().prev().find('input').attr('name');
//                if (window.console) {
//                    console.log('剛剛插入的節點', insert_node_name);
//                }
                $("input[name='" + insert_node_name + "']").attr('checked', true);
                parent.c_main.executing(12);// 下移
//                if (window.console) {
//                    console.log('下移');
//                }
                parent.c_main.executing(10);// 右移
//                if (window.console) {
//                    console.log('右移');
//                }
            }
//            if (typeof(window.parent.jfileUploaded) != 'undefined') {
//                    window.parent.jfileUploaded = true;
//            }
        },

        stop: function (e, data) {
//            if (window.console) {
//                console.log('stop', '儲存');
//            }

            // 延遲存檔，以利進度動畫做完100％的效果
            setTimeout(function() {parent.c_main.executing(5);}, 500);

//            $('#start').data('node', null);
        },

        drop: function (e, data) {
//            if (window.console) {
//                $.each(data.files, function (index, file) {
//                    console.log('Dropped file: ' + file.name);
//                });
//            }
        },

        chunkdone: function (e, data) {
//            if (window.console) {
//                console.log('chunkdone');
//            }
        },

        // 單一檔案進度
        progress: function(e, data){
            var progress = parseInt(data.loaded / data.total * 100, 10);
            data.context.find('.pro').text(progress + " %").change();
            data.context.find('.bar').css('width', progress + 'px');
            if (progress === 100) {
                data.context.find('.bar').removeClass('active');
                $('.itemCancel').remove();
            }
        },

        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        },

        destroy: function (e, data) {
        }
    });

    $('#fileupload').fileupload({maxChunkSize: 1000000})
    .on('fileuploadchunksend', function (e, data) {})
    .on('fileuploadchunkdone', function (e, data) {})
    .on('fileuploadchunkfail', function (e, data) {})
    .on('fileuploaddestroy', function (e, data) {})
    .on('fileuploadchunkalways', function (e, data) {})
    .on('fileuploadfail', function (e, data) {console.log(data.files[0].name);});
});

var drop_html = '';
var drop_next_object = '';
var dropover_flag = true;
var dropover_ident = '';
var this_node = '';
var move_node = '';
var change_list = false;
var change_list_event = false;


function change_drop(event){
    //remove drop action
    $(event.currentTarget).attr("ondrop","drop(event)");
    $(event.currentTarget).find('li').each(function() {
        $(this).attr("ondrop","drop(event)");
        //console.log();
    });
    drop_html = '';
    drop_next_object = '';
    dropover_flag = true;
}

function drag_clean(event){
    drop_html = '';
    drop_next_object = '';
    dropover_flag = true;
}

function drag() {
    change_list = true;
    change_list_event = true;
    $(".drop-note").html('(移至下方)');
}

function dragstart(event) {
    drop_html = event.currentTarget; //要移動的li
}


function dragover(event){
    event.preventDefault();
    // 避免 drop到節點後又drop到body
    event.stopPropagation();


//    if (window.console) {
//        console.log($(event.currentTarget).prop('tagName'));
//    }
    if(change_list == true && dropover_flag == true){
        notSave = true;
        $('.move-div,.move-title').show();

        dropover_flag = false;
        //$(event.currentTarget).find('span').eq(1).text(MSG_BUILD_CHILD_NODE);
        // 檢查下層是否有ul
        $(drop_html).find('div').hide();
        if(event.currentTarget.nextSibling != null){
            //console.log($(event.currentTarget.nextSibling).get(0).tagName.toLowerCase());
            // if next element is ul
            if($(event.currentTarget.nextSibling).get(0).tagName.toLowerCase() == 'ul'){
                //remove drop action
                $(event.currentTarget.nextSibling).find('li').each(function() {
                    $(this).find('div').hide();
                    $(this).attr("ondrop","change_drop(event)");
                    //$(this).find('span').eq(1).text(MSG_ERROR_NODE);
                    //console.log();
                });
                drop_next_object = event.currentTarget.nextSibling;
            }
        }
        // 要移動的節點編號
        this_node = $(drop_html).find("input").next().text();

        // 拖拉時先移除顏色
        $('.learn-path-stressg').removeClass('learn-path-stressg');

        //add green 子層顏色
        if($(drop_html).find('ul').length != 0){
            $(drop_html).find('ul').each(function() {
                $(this).find('li').each(function() {
                    $(this).addClass('learn-path-stressg');
                });
            });
        }
    }

    // 沒有開fy才異動拖放區
    if ($('.fancybox-opened').length === 0) {
        // 移除非目前拖放區的特效
        $('.learn-path-stress').not(event.currentTarget).removeClass('learn-path-stress');
        $('.drop-note').hide();

        if(change_list == true){
            $(drop_html).addClass('learn-path-stressg');
            if(drop_html.nextSibling != null){
                if($(drop_html.nextSibling).get(0).tagName.toLowerCase() == 'ul'){
                    //console.log(drop_html.nextSibling);
                    $(drop_html.nextSibling).find('li').each(function() {
                        $(this).addClass('learn-path-stressg');
                    });
                }
            }
        }

        // 拖放區的特效
        if ($(event.currentTarget).prop('tagName') === 'BODY') {
            $('#displayPanel').find('li').eq(0).addClass('learn-path-stress');
            $('#displayPanel').find('li').eq(0).find('.drop-note').show();
            $('#node-root-name').text($('.learn-path-stress').text());
        } else {
            $(event.currentTarget).addClass('learn-path-stress');

//            if (window.console) {console.log($(event.currentTarget));}


            $(event.currentTarget).find('.drop-note').show();
            $('#node-root-name').text($('.learn-path-stress').find('span:first').text() + ' ' + $('.learn-path-stress').find('a').text());
        }
    }
}

function dragleave(event){
//    event.preventDefault();
//    $(event.currentTarget).removeClass('learn-path-stress');
}

function drop(event,action_id,times){
    event.preventDefault();

    if (window.console) {
        console.log('drop');
    }
    console.log("times:"+times);
    console.log("change_list:"+change_list);
    console.log("action_id:"+action_id);
//    if (window.console) {
//        console.log($(event.currentTarget).prop('tagName'));
//    }

//    if (window.console) {console.log('沒存檔', notSave);}

//    if (window.console) {
//        console.log($(event.currentTarget).find("input").attr('name'));
//        console.log($(event.currentTarget).find("span").text().replace('.', ''));
//    }
    // 判斷異動儲存沒
    if (window.console) {console.log($('#start').data('node'));}
    if (window.console) {console.log($('.fancybox-opened').length === 0 && notSave === false && ($('#start').data('node') === undefined || $('#start').data('node') === 'saved'));}
    if(change_list == true){
        if(action_id == 3 || action_id == 'top'){
            var drop_event = event.currentTarget;
        }else{
            var drop_event = event.target.parentNode;
        }
        // 同一個層無法拖拉
        if(drop_event == drop_html){
            drop_html = '';
            drop_next_object = '';
            dropover_flag = true;
            return false;
        }

        if(action_id == 1){
            drop_event.parentNode.insertBefore(drop_html,drop_event);
        }else if(action_id == 2){
            // tree root
            if($(drop_event).hasClass('classtree') == true){
                $(drop_event).parent().find('ul').eq(0).append(drop_html);
            }else{
                if(drop_event.nextSibling != null){
                    if($(drop_event.nextSibling).get(0).tagName.toLowerCase() == 'ul'){
                        $(drop_event.nextSibling).append(drop_html);
                    }else{
                        if(drop_html != ''){
                            var co_node = document.createElement("ul");
                            co_node.appendChild(drop_html);
                            //console.log(co_node);
                            //drop_event.after(co_node);
                            insertAfter(co_node,drop_event);
                        }
                    }
                }else{
                    if(drop_html != ''){
                        var co_node = document.createElement("ul");
                        co_node.appendChild(drop_html);
                        //drop_event.after(co_node);
                        insertAfter(co_node,drop_event);
                    }
                }
            }
        }else if(action_id == 3){
            if(drop_event.nextSibling != null){
                if($(drop_event.nextSibling).get(0).tagName.toLowerCase() == 'ul'){
                    insertAfter(drop_html,drop_event.nextSibling);
                }else{
                    insertAfter(drop_html,drop_event);
                }
            }else{
                insertAfter(drop_html,drop_event);
            }
        }else if(action_id == 'top'){
            var drop_event2 = $($(drop_event).parent().find('li').eq(1)).get(0);
            drop_event2.parentNode.insertBefore(drop_html,drop_event2);
            //return false;
            //$(drop_event).parent().find('ul').eq(0);
        }

        // 移動子層歸屬
        if(drop_next_object != ''){
            //drop_html.after(drop_next_object);
            insertAfter(drop_next_object,drop_html);
        }
        // 設定是拖放在哪個節點上
        // 節點外 或者 根節點（$(drop_event).find("input").attr('name') === undefined），都記錄root
        //if (window.console) {console.log('拖放點', $('#start').data('node'));}

        // 父節點文字移除「拖曳至此...」
        $('#node-root-name').text($('#node-root-name').text().replace($('.drop-note').text(), ''));

        //處理organization xml
        move_node = $(drop_event).find("input").next().text();

        // hidden
        $('.move-div,.move-title').hide();
        if(action_id == 1){
             var nodes = organization.getElementsByTagName('item');
             var move_curItem = nodes.item(parseInt(move_node.replace('.','')-1, 10));
             var this_curItem = nodes.item(parseInt(this_node.replace('.','')-1, 10));

             move_curItem.parentNode.insertBefore(this_curItem, move_curItem);
        }else if(action_id == 2){
            if(move_node != '' && this_node != '' && move_node != this_node){
                 var nodes = organization.getElementsByTagName('item');
                 var move_curItem = nodes.item(parseInt(move_node.replace('.','')-1, 10));
                 var this_curItem = nodes.item(parseInt(this_node.replace('.','')-1, 10));

                 move_curItem.appendChild(this_curItem);
            }else if($(drop_event).hasClass('classtree') == true){
                 var nodes = organization.getElementsByTagName('item');

                 var this_curItem = nodes.item(parseInt(this_node.replace('.','')-1, 10));
                 var nodes = organization;
                 nodes.appendChild(this_curItem);
            }
        }else if(action_id == 3){
             var nodes = organization.getElementsByTagName('item');
             var move_curItem = nodes.item(parseInt(move_node.replace('.','')-1, 10));
             var this_curItem = nodes.item(parseInt(this_node.replace('.','')-1, 10));

             insertAfter(this_curItem,move_curItem);
        }else if(action_id == 'top'){
             var nodes = organization.getElementsByTagName('item');
             var move_curItem = nodes.item(0);
             var this_curItem = nodes.item(parseInt(this_node.replace('.','')-1, 10));

                move_curItem.parentNode.insertBefore(this_curItem, move_curItem);
        }

        //重新排列 li 編號
        var co_green = '';
        var co_this = '';
         $('#displayPanel').find('li').each(function(k) {
             if(k!= 0){
                 $(this).find("input").next().text(k+'.');
             }
        });

        //取得目前 li 顏色
        $('#displayPanel').find('li').each(function(k) {
             if(k!= 0){
                 if($(this).hasClass('learn-path-stressg') == true && co_green == ''){
                    // console.log($(this));
                    co_green = k;
                 }
             }

             if($(this).hasClass('learn-path-stress') == true && co_this == ''){
                 co_this = k;
             }
        });
        //console.log('y '+co_this+' green:'+co_green);

        // 重新排列 html
        displayLayout();

        //標記顏色
        if(co_green != ''){
            var this_nodes  = $('#displayPanel').find('li').eq(co_green);

            this_nodes.addClass('learn-path-stressg');

            //add green
            if($(this_nodes).find('ul').length != 0){
                $(this_node).find('ul').each(function() {
                    $(this).find('li').each(function() {
                        $(this).addClass('learn-path-stressg');
                    });
                });
            }
            //console.log($(this_nodes).next().get(0));
            //nextSibling is ul add green
            if($(this_nodes).next().get(0) != null){
                if($(this_nodes).next().get(0).tagName.toLowerCase() == 'ul'){
                    //console.log(drop_html.nextSibling);
                    $(this_nodes).next().find('li').each(function() {
                        $(this).addClass('learn-path-stressg');
                    });
                }
            }

        }
        //標記顏色
        if(co_this != '' || co_this == 0){
            $('#displayPanel').find('li').eq(co_this).addClass('learn-path-stress');
        }

        //console.log(organization);
        drop_html = '';
        drop_next_object = '';
        dropover_flag = true;
        change_list = false;
        $(".drop-note").html('(' + MSG_BUILD_CHILD_NODE + ')');

        setTimeout(function() {change_list_event=false;parent.c_main.executing(5);}, 300);

    } else if (!change_list_event){
        console.log('!change_list_event');
        
        var isMZ = (navigator.userAgent.toLowerCase().indexOf('firefox') > -1);
        if (window.console) {console.log('isMZ', isMZ);}
        
        if ($('.fancybox-opened').length === 0 && notSave === true && isMZ === false) {
            parent.c_main.executing(20);
            notSave = false;
        }
        if ($('.fancybox-opened').length === 0 && notSave === false && ($('#start').data('node') === undefined || $('#start').data('node') === 'saved')) {
            console.log('opening fancybox');

            $("#start").off('click');

            // 設定是拖放在哪個節點上
            // 節點外 或者 根節點（$(event.currentTarget).find("input").attr('name') === undefined），都記錄root
            if ($(event.currentTarget).prop('tagName')  === 'BODY' || $(event.currentTarget).find("input").attr('name') === undefined) {
                $('#start').data('node', 'root');
                $('#node-root-name').text($('#displayPanel').find('li').eq(0).text());
            } else {
                $('#start').data('node', $(event.currentTarget).find("input").attr('name'));
            }
            if (window.console) {console.log('拖放點', $('#start').data('node'));}

            // 父節點文字移除「拖曳至此...」
            $('#node-root-name').text($('#node-root-name').text().replace($('.drop-note').text(), ''));
            $('.ln').click();
        }
    }
}


function insertAfter(newEl, targetEl)
{
    var parentEl = targetEl.parentNode;
    console.log(parentEl);
    console.log(newEl);
    if(parentEl.lastChild == targetEl)
    {
        parentEl.appendChild(newEl);
    }else
    {
        parentEl.insertBefore(newEl,targetEl.nextSibling);
    }
}

// 點選LINE分享圖示
$('.ln').one('click', function() {
    $("#share-ln").fancybox({
        'titlePosition': 'inline',
        'transitionIn': 'none',
        'transitionOut': 'none',
        'autoSize': true,
        'minWidth': 900,
        afterClose : function() {
            if (window.console) {console.log('afterClose');}

            // 如果沒有上傳就關閉視窗，剩餘空間要加回
            if ($('#start').data('node') !== 'saved') {
                post_max_size = post_max_size + parseInt($('#total-size').text(), 10);
            }
            if (window.console) {
                console.log('關閉後，剩餘容量KB', post_max_size);
            }

            $('#files-tables').find('.itemNum').parents('tr').remove();
            $('#files-tables').find('#progress').find('.progress-bar').css('width', '0%');
            $('#start').data('fileItemCount', 0);
            $('#start').removeData('node');
            $('#total-size').text(0);
            // 沒有檔案的警示歸0
            $('#displayPanel').data('nofilealarm', '0');
            // 移除底色
            $('.learn-path-stress').removeClass('learn-path-stress');
            // 隱藏拖放至此的提示
            $('.drop-note').hide();
        }
    });
});