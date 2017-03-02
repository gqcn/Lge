<script type="text/javascript" charset="utf-8" src="/static/plugin/ueditor1_3_6_2/ueditor.all.min.js"> </script>

<script type="application/javascript">
    //显示添加媒体文件对话框
    function showMediaSelection()
    {
        showFileManager("添加/选择多媒体文件", function(files){
            addMediaToUEditor(files);
        });
    }
</script>