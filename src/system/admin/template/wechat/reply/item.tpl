<style type="text/css">
    #init-div-news {
        display:none;
    }
    .type-content {
        display:none;
    }
    .news-img {
        float:left;
    }

    .news-img img {
        height:122px;
        padding:2px;
        border:1px solid #B0CDE6;
    }
    .news-img img:hover {
        border:1px solid #54A1E4;
    }

    .news-input {
        margin:0 0 10px 10px;
    }
</style>

<div id="init-div-news">
    <div class="widget-container-col ui-sortable">
        <div class="widget-box ui-sortable-handle news-box">
            <div class="widget-header">
                <div class="widget-toolbar">
                    <a href="javascript:;" onclick="deleteNews(this)">
                        <i class="ace-icon fa fa-minus red"></i>
                        删除消息
                    </a>
                    &nbsp;|&nbsp;
                    <a href="javascript:;" onclick="addNews(this)">
                        <i class="ace-icon fa fa-plus green"></i>
                        新增消息
                    </a>
                </div>
            </div>
            <div class="widget-body">
                <div class="widget-main">
                    <div class="news-img">
                        <a href="javascript:;" onclick="showImageSelection(this)" title="点击选择图片" data-rel="tooltip">
                            <img src="/static/resource/images/default.jpg">
                        </a>
                    </div>
                    <div>
                        <div><input value="" type="text" name="news_titles[]" class="news-input col-xs-12 col-sm-7" placeholder="请输入图文消息标题"/></div>
                        <div><input value="" type="text" name="news_urls[]" class="news-input col-xs-12 col-sm-7" placeholder="请输入图文消息跳转链接"/></div>
                        <div><input value="" type="text" name="news_images[]" class="news-input col-xs-12 col-sm-7" placeholder="请点击图片进行选择，或者手动输入图片地址"/></div>
                    </div>
                    <div>
                        <textarea name="news_briefs[]" placeholder="请输入图文消息描述" class="col-xs-12 col-sm-12"></textarea>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="row">
<div class="col-xs-12">
<form class="form-horizontal reply-form" id="validation-form" action="?app=picture&act=edit" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="{$data['id']}"/>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">回复类型:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <select class="select2" name="type" onchange="onTypeChange()">
                    {foreach from=$types index=$index key=$k item=$v}
                        <option value="{$k}" {if $data['type'] == $k}selected{/if}>{$v}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">回复排序:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <input value="{$data['order']}" type="text" name="order" class="col-xs-12 col-sm-12" style="width:305px;"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">关键字:</label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <input value="{$data['keywords']}" type="text" name="keywords" class="col-xs-12 col-sm-12" style="width:305px;"/>
                <span style="padding:7px 0 0 5px;display:block;">&nbsp;相同关键字将会按照排序优先级进行匹配回复</span>
            </div>
            <div style="margin:7px 0 0 0px;">
                系统预设关键字(点击即可设置)：<a href="javascript:$('input[name=keywords]').val('关注时自动回复')">关注时自动回复</a>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">回复内容:</label>
        <div class="col-xs-12 col-sm-10">

            {* 文本消息 *}
            <div class="type-content text">
                <textarea name="content" placeholder="请输入回复的消息内容(换行：在内容中能够换行，微信客户端就支持换行显示)" class="col-xs-12 col-sm-12" style="height:300px;font-size:16px;color:#111;">{if $data['type'] == 'text'}{$data['content']}{/if}</textarea>
            </div>

            {* 图文消息 *}
            <div class="type-content news">
                <div>图片仅支持JPG、PNG格式，较好的效果为<b>大图360*200</b>，<b>小图200*200</b> (图文消息中第一条消息为大图，其他为小图)。<b>消息窗口可鼠标拖动进行排序</b>。</div>
                {if $data['type'] == 'news'}
                    {foreach from=$data['content'] index=$index key=$key item=$item}
                        <div class="widget-container-col ui-sortable">
                            <div class="widget-box ui-sortable-handle news-box">
                                <div class="widget-header">
                                    <div class="widget-toolbar">
                                        <a href="javascript:;" onclick="deleteNews(this)">
                                            <i class="ace-icon fa fa-minus red"></i>
                                            删除消息
                                        </a>
                                        &nbsp;|&nbsp;
                                        <a href="javascript:;" onclick="addNews(this)">
                                            <i class="ace-icon fa fa-plus green"></i>
                                            新增消息
                                        </a>
                                    </div>
                                </div>
                                <div class="widget-body">
                                    <div class="widget-main">
                                        <div class="news-img">
                                            <a href="javascript:;" onclick="showImageSelection(this)" title="点击选择图片" data-rel="tooltip">
                                                <img src="{$item['image']}">
                                            </a>
                                        </div>
                                        <div>
                                            <div><input value="{$item['title']}" type="text" name="news_titles[]" class="news-input col-xs-12 col-sm-7" placeholder="请输入图文消息标题"/></div>
                                            <div><input value="{$item['url']}" type="text" name="news_urls[]" class="news-input col-xs-12 col-sm-7" placeholder="请输入图文消息跳转链接"/></div>
                                            <div><input value="{$item['image']}" type="text" name="news_images[]" class="news-input col-xs-12 col-sm-7" placeholder="请点击图片进行选择，或者手动输入图片地址"/></div>
                                        </div>
                                        <div>
                                            <textarea name="news_briefs[]" placeholder="请输入图文消息描述" class="col-xs-12 col-sm-12">{$item['brief']}</textarea>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                {/if}
            </div>

        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
        <div class="col-xs-12 col-sm-9">
            <div class="clearfix">
                <button class="btn btn-info" type="submit">
                <i class="ace-icon fa fa-check bigger-110"></i>
                保存&nbsp;&nbsp;&nbsp;
                </button>
                
                <button class="btn" type="button" onclick="window.location.href='?app={$_Get->data['app']}&act=index'">
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
        p.find("input[name='news_images[]']").val(files);
        p.find('img').attr('src', files);
        p.find('img').show();
    }, ['image']);
}

// 删除图片
function deleteNews(obj) {
    var p = $(obj).parent().parent().parent();
    p.remove();
}
// 新增图片DIV
function addNews(obj) {
    var content = $('#init-div-news').html();
    var p = $(obj).parent().parent().parent();
    p.after(content);
    makeNewsDivSortable();
    $('[data-rel=tooltip]').tooltip();
}

// 类型选择事件
function onTypeChange()
{
    // 切换类型时隐藏错误信息
    $('.help-block').hide();

    var type = $("select[name='type']").val();
    $('.type-content').hide();
    $('.' + type).show();
    // 类型特殊逻辑处理
    switch (type) {
        case 'news':
            if ($('.reply-form').find('.news-box').length < 1) {
                var content = $('#init-div-news').html();
                for (var i = 0; i < 1; i++) {
                    $('.news').append(content);
                }
            }
            makeNewsDivSortable();
            break;
    }
    $('[data-rel=tooltip]').tooltip();
}

// 使消息窗口可排序
function makeNewsDivSortable()
{
    $('.widget-container-col').sortable({
        connectWith: '.widget-container-col',
        items:'> .widget-box',
        handle: ace.vars['touch'] ? '.widget-header' : false,
        opacity:0.8,
        placeholder: 'widget-placeholder',
        forcePlaceholderSize:true
    });
}

jQuery(function($) {
    onTypeChange();

	$(".select2").css('width','305px').select2({allowClear:true})
    .on('change', function(){
        $(this).closest('form').validate().element($(this));
    });

    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            keywords: {
                required: true
            }
        },
        messages: {
            keywords: {
                required: "关键字不能为空"
            }
        },
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                dataType: 'json',
                success : function(result){
                    if (result.result) {
                        window.location.reload();
                    } else {
                        $.gritter.add({
                            title: '错误提示',
                            text: result.message,
                            class_name: 'gritter-error gritter-right'
                        });
                    }
                }
            });
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






















