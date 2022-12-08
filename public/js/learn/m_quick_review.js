var editBtn;
var notes = [];

function displayTimeline(data, reply) {
    $('#timeline-data').empty();
    if (data == null) {
        return;
    }

    notes = [];
    $.each(data, function(i, v) {
        var childrens='';
        $.each(v, function(key, value) {
            var replycnt, discussEvent, discussClass;
            // 根據有無張貼，顯示不同頁面
            if (null !== reply && 'undefined' !== typeof reply[value.note_id]) {
                replycnt = '<span style="color: red;">(' + (reply[value.note_id].num -1) + ')</span>';
                discussClass= 'subject-btn';
                discussEvent = 'showPost('+value.course_id+', \''+reply[value.note_id].bid+'\', \''+reply[value.note_id].nid+'\');';
            } else {
                replycnt= '';
                discussClass= 'post-btn';
                discussEvent = 'postNote('+value.course_id+', \''+approot+noteDir+value.note_id+'/'+value.image_name+'\',\''+value.title+'['+ value.shot_time +']\', '+value.note_id+' );';
            }
            notes.push(value.note_id);
            childrens += '<span class="tick tick-before"></span>' +
                    '<dt id="note' + value.note_id + '" class="timeline-event"><a>'+ value.course_name + '-' + value.title +'</a><span class="note-time">'+value.create_time_noy+'</span></dt>' +
                    '<span class="tick tick-after"></span>' +
                    '<dd class="timeline-event-content" id="note' + value.note_id + 'EX">' +
                        '<div class="media">' +
                            '<a href="#videoFrame" class="media-url" onclick="showVideo(\''+value.sco_id+'\', \''+value.note_id+'\');">' +
                                '<img src="'+approot+noteDir+value.note_id+'/'+value.image_name +'" height="200" width="300"/>' +
                            '</a>' +
                            '<div style="text-align: right;">' +
                                '<a href="#discussFrame" class="'+discussClass+' btn btn-orange" style="color: #FFFFFF;" onclick="'+discussEvent+'" data-cid="'+value.course_id+'">'+
                                    MSGDISCUSS+replycnt+
                                '</a>' +
                                '<button class="btn btn-orange" onclick="delNote('+value.note_id+', \'' + value.title + '\');">'+MSGDELETE+'</button>' +
                                '<a href="#edit-div" class="edit-btn btn btn-orange" style="color: #FFFFFF;" onclick="setNote(\''+value.note_id+'\', \''+value.title+'\', this);" data-memo="'+value.memo+'">'+MSGEDIT+'</a>' +
                            '</div>' +
                        '</div>' +
                        '<blockquote class="breakword">' + value.memo_view + '</blockquote>' +
                        '<br class="clear">' +
                    '</dd>';
        });
        $('#timeline-data')
        .append($('<div class="timeline-wrapper"></div>')
            .append('<h2 class="timeline-time"><span>'+ i +'</span></h2>')
            .append($('<dl class="timeline-series"></dl>')
                .append(childrens)
            )
        );
    });
}

function getNote(key) {
    var kw = '', cid = '';
    if (key != null) {
        kw = '&key='+key;
    }
    cid = '&cid=' + $("#course-filter").data('value');
    $.ajax({
        'url': appRoot + '/mooc/controllers/user_ajax.php',
        'type': 'POST',
        'data': 'action=getUserNote'+kw+cid,
        'dataType': 'json',
        'success': function(res) {
            // console.log(res);
            displayTimeline(res.data, res.reply);
        },
        'error': function() {

        }
    });
}

function delNote(id, title) {
    if (confirm(MSGDELETE + '「' + title + "」?")) {
        $.ajax({
            'url': appRoot + '/mooc/controllers/user_ajax.php',
            'type': 'POST',
            'data': 'action=delUserNote&note_id=' + id,
            'dataType': 'json',
            'success': function(res) {
                if (res.code == 1) {
                    $.each(res.data, function(i, v) {
                        $('#note' + v).remove();
                        $('#note' + v + 'EX').remove();    
                    });
                }
            },
            'error': function() {

            }
        });
    }
}
function setNote(id, title, obj) {
    $("#editFrm .note-title").text(title);
    $("#editFrm input[name=note_id]").val(id);
    $("#editFrm textarea[name=content]").val($(obj).data('memo'));
    editBtn = obj;
}

function showVideo(rid, nid) {
    rid = rid.replace("I_", "");
    $("#videoFrame").attr('src', appRoot + '/learn/path/lcms.php?motion=review&rid=' + rid +'&nid=' + nid);
}

function searchKW(obj) {
    var key = $(obj).find('input[name=keyword]').val();
    getNote(key);
    return false;
}
function postNote(cid, imgurl, title, ntid) {
    $("#postFrm input[name=cid]").val(cid);
    $("#postFrm input[name=imgsrc]").val(imgurl);
    $("#postFrm input[name=title]").val(title);
    $("#postFrm input[name=note_id]").val(ntid);
}
function showPost(cid, bid, nid) {
    $("#subjectFrm input[name=cid]").val(cid);
    $("#subjectFrm input[name=bid]").val(bid);
    $("#subjectFrm input[name=nid]").val(nid);
}
function chgCourse(obj, cid) {
    $("#course-filter").data('value', cid);
    $("#course-filter").empty();
    $("#course-filter").append($('<div class="filter-name"></div>').append($(obj).text()))
                        .append('&nbsp;')
                        .append('<span class="caret">');
    $("#searchFrm").submit();
}

function expandTimeline(obj) {
    var exdBtn = $('#expand-btn');
    exdBtn.trigger('click');
    $(obj).html(exdBtn.html());
}

function reloadReplyNum() {
    $.ajax({
        'url': appRoot + '/mooc/controllers/user_ajax.php',
        'type': 'POST',
        'data': 'action=getNoteReplyNum&note_id=' + notes.join(','),
        'dataType': 'json',
        'success': function(res) {
            $.each(res.data, function(i, v) {
                var obj = $("#note"+i+"EX .media > div");
                var discussBtn = obj.find('.subject-btn');
                if (discussBtn.size() < 1) {
                    discussBtn = obj.find('.post-btn');
                    discussBtn.removeClass('post-btn').addClass('subject-btn');
                    discussBtn.attr('onclick', 'showPost('+discussBtn.data("cid")+', "'+v.bid+'", "'+v.nid+'");');
                }
                discussBtn.html(MSGDISCUSS+'<span style="color: red;">('+(v.num-1)+')</span>');
            });
        }
    });
}

$(document).ready(function() {
    getNote();
    
    $.timeliner({
        timelineContainer: '#timeline',
        expandAllText: '<div class="icon-expand-s"></div>&nbsp;' + MSGEXPAND,
        collapseAllText: '<div class="icon-collapse-s"></div>&nbsp;' + MSGCOLLAPSE
    });
    /* 設定fancybox */ 
    $('.media-url').fancybox({ 
        'titlePosition': 'inline',
        'transitionIn': 'none',
        'transitionOut': 'none',
        'closeBtn': false,
        helpers : {
            overlay : {
                locked : false
            }
        },
        afterClose : function() {
            $("#videoFrame").attr('src', '');
        }
    });
    $('.edit-btn').fancybox({ 
        'titlePosition': 'inline',
        'transitionIn': 'none',
        'transitionOut': 'none',
        'closeBtn': false,
        helpers : {
            overlay : {
                locked : false
            }
        }
    });
    $('.post-btn').fancybox({ 
        'titlePosition': 'inline',
        'transitionIn': 'none',
        'transitionOut': 'none',
        'closeBtn': false,
        helpers : {
            overlay : {
                locked : false
            }
        },
        afterShow : function() {
            $("#postFrm").submit();
        },
        afterClose : function() {
            reloadReplyNum();
        }
    });
    $('.subject-btn').fancybox({ 
        'titlePosition': 'inline',
        'transitionIn': 'none',
        'transitionOut': 'none',
        'closeBtn': false,
        helpers : {
            overlay : {
                locked : false
            }
        },
        afterShow : function() {
            $("#subjectFrm").submit();
        },
        afterClose : function() {
            reloadReplyNum();
        }
    });
    
    $.fn.multiline = function(text){
        this.text(text);
        this.html(this.html().replace(/\n/g,'<br/>'));
        return this;
    }

    // 編輯 Note
    $('#memo-btn').on('click', function() {
        $.ajax({
            'url': $("#editFrm").attr('action'),
            'type': 'POST',
            'data': $("#editFrm").serialize() + '&action=setUserNote',
            'dataType': 'json',
            'success': function(res) {
                if(res == 1) {
                    var nid = $("#editFrm input[name=note_id]").val();
                    var content = $("#editFrm textarea[name=content]").val();
                    $('#note'+nid+'EX').find('blockquote').multiline(content);
                    $(editBtn).data('memo', content);
                    $.fancybox.close();
                }
            },
            'error': function() {

            }
        });
    });
});