<style type="text/css">
    .api-app-logo {
        width:122px;
        height:122px;
    }
    .api-app-logo img {
        height:122px;
        padding:10px;
        border:1px solid #B0CDE6;
    }
    .api-app-logo img:hover {
        border:1px solid #54A1E4;
    }
    #validation-form textarea  {
        height:200px;
        padding-left:5px;
    }
</style>

<div class="row">
<div class="col-xs-12">


<form class="form-horizontal reply-form" id="validation-form" action="/api-app/item" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="{$data['id']}"/>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <a href="javascript:;" class="api-app-logo" onclick="showImageSelection(this)" title="点击选择应用的LOGO" data-rel="tooltip">
                    <img src="{$data['thumb']}">
                    <input type="hidden" name="thumb" value="{$data['thumb']}"/>
                </a>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">应用名称:</label>
        <div class="col-xs-12 col-sm-4">
            <div class="clearfix">
                <input value="{$data['name']}" type="text" name="name" placeholder="请输入应用名称" class="col-xs-12 col-sm-12"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">生产地址:</label>
        <div class="col-xs-12 col-sm-6">
            <div class="clearfix">
                <input value="{$data['address_prod']}" name="address_prod" class="col-xs-12 col-sm-10" type="text" placeholder="生产环境的接口地址，例如：http://prod.xxx.com/api"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">测试地址:</label>
        <div class="col-xs-12 col-sm-6">
            <div class="clearfix">
                <input value="{$data['address_test']}" name="address_test" class="col-xs-12 col-sm-10" type="text" placeholder="测试环境的接口地址，例如：http://test.xxx.com/api"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">应用简介:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <textarea name="brief" placeholder="请输入应用的简要介绍信息" class="col-xs-12 col-sm-12">{$data['brief']}</textarea>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">详细介绍:</label>
        <div class="col-xs-12 col-sm-10">
            <div style="margin:0 0 5px 0;">
                <button class="btn btn-sm btn-info" onclick="showMediaSelection()" type="button">
                <i class="ace-icon fa fa-inbox"></i> 添加媒体 </button>
                格式支持: 图片(png、jpg、jpeg、gif), 音频(mp3、aac、wav、ogg、ogv、m4a), 视频(flv、mp4、mov、f4v、3gp、3g2)
            </div>
            <textarea name="content" id="editor">{$data['content']}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <button class="btn btn-info" type="submit">
                <i class="ace-icon fa fa-check bigger-110"></i>
                保存&nbsp;&nbsp;&nbsp;
                </button>
                
                <button class="btn" type="button" onclick="window.history.go(-1)">
                <i class="ace-icon fa fa-reply bigger-110"></i>
                返回&nbsp;&nbsp;&nbsp;
                </button>
            </div>
        </div>
    </div>

</form>
</div>
</div>

{include _subinc/elfinder}
{include _subinc/ueditor}
    
    
<script type="text/javascript">
// 显示缩略图上传/选择文件对话框
function showImageSelection(obj)
{
    showFileManager("请选择图片", function(files){
        var p = $(obj).parent().parent();
        console.log(p);
        p.find("input[name='thumb']").val(files);
        p.find('img').attr('src', files);
        p.find('img').show();
    }, ['image']);
}


jQuery(function($) {
    UE.getEditor('editor');

	$(".select2").css('width','305px').select2({allowClear:true})
    .on('change', function(){
        $(this).closest('form').validate().element($(this));
    });

    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            name: {
                required: true
            }
        },
        messages: {
            name: {
                required: "应用名称不能为空"
            }
        },
        highlight: function (e) {
            $(e).closest('.form-group').removeClass('has-info').addClass('has-error');
        },
        success: function (e) {
            $(e).closest('.form-group').removeClass('has-error').addClass('has-info');
            $(e).remove();
        },
        errorPlacement: function (error, element) {
            if(element.is(':checkbox') || element.is(':radio')) {
                var controls = element.closest('div[class*="col-"]');
                if(controls.find(':checkbox,:radio').length > 1) {
                    controls.append(error);
                } else {
                    error.insertAfter(element.nextAll('.lbl:eq(0)').eq(0));
                }
            } else if(element.is('.select2')) {
                error.insertAfter(element.siblings('[class*="select2-container"]:eq(0)'));
            } else if(element.is('.chosen-select')) {
                error.insertAfter(element.siblings('[class*="chosen-container"]:eq(0)'));
            } else {
                error.insertAfter(element.parent());
            }
        }
    });

});
</script>






















