$('#drag-note').parent().bind({
    dragover: function() {
        $('.container').parent().parent().css('border', '5px rgb(119, 119, 119) dashed');
        $('#drag-note').css('background-color', '#D6D6D6').css('opacity', 0.3).css('cursor', 'pointer');
        $('#drag-note').text(DROP_FILES_HERE);
        $('#uploadStep1').css('z-index', 0);
        $('#drag-note').css('height', $('#files-tables').parents('body').height() + 108);
        $('#uploadStep2 input').css('z-index', 0);
        $('#uploadStep3 input').css('z-index', 0);
        $('.itemCancel').css('z-index', 0);
        $('.alert').css('z-index', 0);
        if ($('#uploadStep3').css('display') === 'block') {
            $('.container').parent().parent().css('border', '5px red dashed');
            $('#drag-note').css('color', 'red');
            $('#drag-note').text(REPLOAD_PLEASE);
        }
    },
    dragleave: function() {
        $('.container').parent().parent().css('border', 'none');
        $('#drag-note').css('background-color', '#FFFFFF').css('opacity', 0).css('cursor', 'auto');
        $('#drag-note').text('');
        $('#uploadStep1').css('z-index', 2);
        $('#drag-note').css('height', $('#files-tables').parents('body').height() + 108);
        $('#uploadStep2 input').css('z-index', 2);
        $('#uploadStep3 input').css('z-index', 2);
        $('.itemCancel').css('z-index', 2);
        $('.alert').css('z-index', 2);
        if ($('#uploadStep3').css('display') === 'block') {
            document.location.reload();
        }
    },
    drop: function() {
        $('.container').parent().parent().css('border', 'none');
        $('#drag-note').css('background-color', '#FFFFFF').css('opacity', 0).css('cursor', 'auto');
        $('#drag-note').text('');
        $('#uploadStep1').css('z-index', 2);
        $('#uploadStep2 input').css('z-index', 2);
        $('#uploadStep3 input').css('z-index', 2);
        $('.itemCancel').css('z-index', 2);
        $('.alert').css('z-index', 2);
        if ($('#uploadStep3').css('display') === 'block') {
            document.location.reload();
        }
    }
});