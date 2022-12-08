<?php
    $data = $_POST['data'];
    if (!get_magic_quotes_gpc()) {
        $data = addslashes($data);
    }
?>
<script type="text/javascript">
    window.onload = function () {
        window.parent.opener.addLcmsContent('<?=$data?>');
    };
</script>