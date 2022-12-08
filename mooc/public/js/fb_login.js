var isError = false;
// 即時檢查帳號
function checkRegUsername($username) {
    var $xml;
    if ($username.val() !== '') {
        $username.parent().children('.icon-ok, .icon-remove').remove();

        $xml = $.parseXML('<manifest><exist_user>' + $username.val() + '</exist_user></manifest>');
        $.ajax(
            appRoot + '/sys/reg/check_user.php',
            {
                'type': 'POST',
                'processData': false,
                'data': $xml,
                'success': function (data) {
                    var resultId = $(data).find('result').text();

                    isError = false;
                    switch (resultId) {
                        case '0':
                            $username
                                .tooltip('destroy')
                                .after('<i class="icon-ok"></i>');
                            break;
                        case '1':
                        case '4':
                            isError = true;
                            $username
                                .attr('title', $username.val() + MSG.reserved)
                                .tooltip('toggle')
                                .after('<i class="icon-remove"></i>')
                                .focus();
                            break;
                        case '2':
                            isError = true;
                            $username
                                .attr('title', '( ' + $username.val() + ' )' + MSG.reduplicate)
                                .tooltip('toggle')
                                .after('<i class="icon-remove"></i>')
                                .focus();
                            break;
                        case '3':
                            isError = true;
                            $username
                                .attr('title', MSG.user_rule)
                                .tooltip('toggle')
                                .after('<i class="icon-remove"></i>')
                                .focus();
                            break;
                    }
                }
            }
        );
    }
}

function checkRegister() {
    var $username = $('#fb_username');
    if ($username.val() === '') {
        $username.parent().children('.icon-ok, .icon-remove').remove();
        $username
            .attr('title', MSG.empty_account)
            .tooltip('toggle')
            .after('<i class="icon-remove"></i>')
            .focus();
        return false;
    }

    if (isError) {
        $username.focus();
        return false;
    }

    return true;
}

$(function () {
    var $username = $('#fb_username');

    $username
        .focus()
        .on('blur', function () {
            checkRegUsername($username);
        });
    checkRegUsername($username);

    $('#btnConfirm').click(function () {
        var $self = $(this);
        $self.attr('disabled', true);
        $('#registerBox').fadeOut(function () {
            $('#confirmBox').show();
            $('#username').focus();
            $self.removeAttr('disabled');
        });
    });
    $('#btnRegister').click(function () {
        var $self = $(this);
        $self.attr('disabled', true);
        $('#confirmBox').fadeOut(function () {
            $('#registerBox').show();
            $username.focus();
            $self.removeAttr('disabled');
        });
    });
    $('#btnCombine').click(function () {
        var
            $self = $(this),
            res = checkLogin(),
            data = $('#loginForm').serialize();

        $self.attr('disabled', true);
        if (res) {
            $.ajax(
                appRoot + '/mooc/fb_confirm.php',
                {
                    'type': 'POST',
                    'data': data,
                    'success': function (response) {
                        var $username = $('#username');

                        if (response.code === 0) {
                            window.location.replace(appRoot + '/mooc/index.php');
                        } else {
                            $('#loginForm').find('input[name="login_key"]').val(response.ticket);
                            $('#password').val('');
                            $username.parent().children('.icon-ok, .icon-remove').remove();
                            $username.tooltip('destroy');
                            $username
                                .attr('title', response.message)
                                .tooltip('show')
                                .after('<i class="icon-remove"></i>')
                                .focus();
                        }
                    },
                    'complete': function () {
                        $self.removeAttr('disabled');
                    }
                }
            );
        }
        return false;
    });
});
