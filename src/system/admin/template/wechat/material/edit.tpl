<div class="row">
<div class="col-xs-12">
<form class="form-horizontal" id="validation-form" action="?app=picture&act=edit" method="post" enctype="multipart/form-data">
<input type="hidden" name="picture_id" value="{$data['picture_id']}"/>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">图片分类:</label>
        <div class="col-xs-12 col-sm-9">
        {if $catArray}
        <select class="select2" name="cat_id">
            {foreach from=$catArray index=$index key=$key item=$item}
            <option value="{$item['cat_id']}" {if $data['cat_id'] == $item['cat_id']}selected{/if}>{$item['cat_name']}</option>
            {/foreach}
        </select>
        {else}
        <div style="margin:5px 0 0 0px;">当前没有图片分类，您可以点这 <a href="?app=picture&act=category" target="_blank">创建分类</a></div>
        {/if}
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">图片作者:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <input value="{$data['author']}" type="text" name="author" class="col-xs-12 col-sm-12" style="width:305px;"/>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">图片排序:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <input value="{$data['order']}" type="text" name="order" class="col-xs-12 col-sm-12" style="width:305px;"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">图片标题:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <input value="{$data['title']}" type="text" name="title" class="col-xs-12 col-sm-12" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required" for="url">图片地址: {if $data['thumb']}(<a href="{$data['thumb']}" target="_blank">查看</a>){/if}</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
            <input value="{$data['thumb']}" name="thumb" type="text" class="col-xs-12 col-sm-12" placeholder="点击选择或上传图片的缩略图" onclick="showThumbSelection()"/>
            </div>
        </div>
    </div>


    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">图片简单描述:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <textarea name="brief" style="height:150px;padding:5px 4px;" class="col-xs-12 col-sm-12" >{$data['brief']}</textarea>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">图片详细说明:</label>
        <div class="col-xs-12 col-sm-9">
            <div style="margin:0 0 5px 0;">
            <button class="btn btn-sm btn-info" onclick="showMediaSelection()" type="button"> <i class="icon-magnet"></i> 添加媒体 </button>
            格式支持: 图片(png、jpg、jpeg、gif), 音频(mp3、aac、wav、ogg、ogv、m4a), 视频(flv、mp4、mov、f4v、3gp、3g2)
            </div>
            <script name="content" id="editor">{$data['content']}</script>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">图片标签:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <input  value="{$data['tags']}" placeholder="输入标签后按回车键添加" type="text" name="tags" id="form-field-tags" class="col-xs-12 col-sm-12" style="width:100%;"/>
            </div>
        </div>
    </div>
    
    
    
    
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <button class="btn btn-info" type="submit">
                <i class="icon-ok bigger-110"></i>
                保存&nbsp;&nbsp;&nbsp;
                </button>
                
                <button class="btn" type="button" onclick="window.location.href='?app={$_Get->data['app']}&act=index'">
                <i class="icon-reply bigger-110"></i>
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
function showThumbSelection()
{
	showFileManager("缩略图选择", function(files){
		$("input[name=thumb]").val(files);
	}, ['image']);
}

//显示添加媒体文件对话框
function showMediaSelection()
{
    showFileManager("添加/选择多媒体文件", function(files){
    	addMediaToUEditor(files);
    });
}

jQuery(function($) {
    $('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });
    $('#timepicker').timepicker({
        minuteStep: 1,
        showSeconds: true,
        showMeridian: false
    }).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });

	$(".select2").css('width','305px').select2({allowClear:true})
    .on('change', function(){
        $(this).closest('form').validate().element($(this));
    }); 
    
    //we could just set the data-provide="tag" of the element inside HTML, but IE8 fails!
    var tag_input = $('#form-field-tags');
    if (!( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) ) {
        tag_input.tag({
            placeholder:tag_input.attr('placeholder')
          }
        );
    } else {
        // display a textarea for old IE, because it doesn't support this plugin or another one I tried!
        tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
        //$('#form-field-tags').autosize({append: "\n"});
    }
	
    UE.getEditor('editor');
    
    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            author: {
                required: true
            },
            title: {
                required: true
            },
            thumb: {
                required: true
            }
        },
        messages: {
        	author: {
                required: "图片作者不能为空"
            },
            title: {
                required: "图片标题不能为空"
            },
            thumb: {
                required: "请选择图片"
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






















