// 討論區結束後公開或關閉
afterFinished = function(e) {
    var status = $(this).val();
//    console.log(status);
    switch (status) {
    case 'open':
        $("input[name=after_finish][value='public']").prop('checked', false);
        $("input[name=after_finish][value='public']").hide();
        $("input[name=after_finish][value='public']+label").hide();
        $("input[name=after_finish][value='closed']").prop('type', 'checkbox');
        $("input[name=after_finish][value='closed']").show();
        $("input[name=after_finish][value='closed']+label").show();
        break;

    case 'taonly':
        $("input[name=after_finish][value='public']").show();
        $("input[name=after_finish][value='public']+label").show();
        $("input[name=after_finish][value='closed']").prop('type', 'radio');
        $("input[name=after_finish][value='closed']").show();
        $("input[name=after_finish][value='closed']+label").show();
        break;

    case 'disable':
        $("input[name=after_finish][value='public']").prop('checked', false);
        $("input[name=after_finish][value='closed']").prop('checked', false);
        $("input[name=after_finish][value='public']").hide();
        $("input[name=after_finish][value='public']+label").hide();
        $("input[name=after_finish][value='closed']").prop('type', 'radio');
        $("input[name=after_finish][value='closed']").hide();
        $("input[name=after_finish][value='closed']+label").hide();
        break;
    }
};

// 討論區結束後公開或關閉
$('#forum_status input').click(afterFinished);

// 發表人員連動
var poster = function(e) {
    var posterId = $(this).val();
    switch (posterId) {
    case 'login_persons':
        if ($(this).prop('checked') === true) {
            $('#poster input').prop('checked', true);
        }
        break;

    default:
        if ($(this).prop('checked') === false && $("input[name^=poster][value='login_persons']").prop('checked') === true) {
            $(this).prop('checked', true);
        }
        break;
    }
};

// 發表人員連動
$('#poster input').click(poster);