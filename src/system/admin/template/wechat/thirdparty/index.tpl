<style type="text/css">
    .page-content {
        margin:0;
        padding:0;
    }

</style>

<iframe src="{$url}" id="iframe" style="border:0px;width:100%;"></iframe>

<script type="text/javascript">
    jQuery(function($) {
        if (!ace.vars['minimized']) {
            $('#sidebar-collapse').click();
        }

        var documentHeight = $(document).height();
        $('#iframe').css('height', (documentHeight - 50) + 'px');
    });
</script>



