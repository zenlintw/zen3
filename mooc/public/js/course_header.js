    function goBoard(val, target, isGroupBoard) {
        var $xml = $.parseXML('<manifest><board_id>' + val + '</board_id></manifest>');
        if ((target === undefined) || (target === '')) {
            target = '_self';
        }
        $.ajax(
            baseUri + '/learn/goto_board.php',
            {
                'type': 'POST',
                'processData': false,
                'data': $xml,
                'success': function (data) {
                    switch (data) {
                        case 'Bad_ID'       : alert(MSG_BAD_BOARD_ID);    break;
                        case 'Bad_Range'    : alert(MSG_BAD_BOARD_RANGE); break;
                        case 'board_notopen': alert(MSG_BOARD_NOTOPEN);   break;
                        case 'board_close'  : alert(MSG_BOARD_CLOSE);     break;
                        case 'board_disable': alert(MSG_BOARD_DISABLE);   break;
                        case 'board_taonly' : alert(MSG_BOARD_TAONLY);    break;
                        default:
                            if (target == '_blank') {
                                if (isGroupBoard == 1) {
                                    boardWin = window.open('/forum/index.php', '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
                                }else if ( val==0 || val ==1) {
                                    boardWin = window.open('/forum/index.php', '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
                                } else {
                                    boardWin = window.open('/forum/m_node_list.php?xbid='+encodeURIComponent(encodeURIComponent(val)), '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
                                }
                            } else {
                                if (isGroupBoard == 1) {
                                     document.location.replace('/forum/index.php');
                                }else if ( val==0 || val ==1) {
                                    
                                    // 課程公告版
                                    $("form[name='node_list']")
                                        .prop('action', appRoot + '/forum/m_node_list.php')
                                        .prop('target', '_self')
                                        .find("input[name='cid']")
                                            .val(courseId).end()
                                        .find("input[name='bid']")
                                            .val(courseBulletin);

                                    $("form[name='node_list']").submit();
                                } else {
                                    document.location.replace('/forum/m_node_list.php?xbid='+encodeURIComponent(encodeURIComponent(val)));
                                }
                            }
                        break;
                    }
                }
            }
        );
    }

    /**
     * 進討論室
     * @param {string} val 討論室編號
     * @return
     **/
    function goChatroom(val) {
        if (window.console) {console.log('course_header.js goChatroom()', val);}
        var $xml;
        if ((typeof chatWin === 'object') && (chatWin != null) && !chatWin.closed) {
            alert(MSG_IN_CHAT_ROOM);
            chatWin.focus();
        } else {
            $xml = $.parseXML('<manifest><chat_id>' + val + '</chat_id></manifest>');
            var message, action;
            $.ajax(
                baseUri + '/learn/goto_chat.php',
                {
                    'type': 'POST',
                    'processData': false,
                    'async': false,
                    'data': $xml,
                    'success': function (data) {
                        message = $(data).find('msg').text();
                        action = $(data).find('uri').text();
                    }
                }
            );
            // AJAX 使用WINDOWOPEN會被擋下，將移到AJAX外面判斷
            if (message !== '') {
                alert(message);
                return;
            }
            if (action === '') {
                action = 'about:blank';
            }
            chatWin = window.open(action, '_blank', 'width=800,height=500,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1');
        }
    }

    /**
     * 切換課程/環境/班級時, 強制登出討論室
     */
    function logoutChatroom() {
        if ((typeof chatWin === 'object') && (chatWin != null) && !chatWin.closed) {
            chatWin.focus();    // 先focus, 以讓跳出的訊息可以在上層
            chatWin.close();
        }
    }