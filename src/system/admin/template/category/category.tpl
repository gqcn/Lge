<style type="text/css">
.table thead > tr > th, 
.table tbody > tr > th, 
.table tfoot > tr > th, 
.table thead > tr > td, 
.table tbody > tr > td, 
.table tfoot > tr > td  {vertical-align: middle;}
</style>


<div class="row">
<div class="col-xs-12">

<form class="form-horizontal" action="?" method="get">
    <div class="row" style="margin-bottom:10px;">
        <div class="input-group col-xs-1" title="分类类型" data-rel="tooltip" >
            <select class="select2" name="type">
                <option value="0">所有分类类型</option>
                {foreach from=$types index=$index key=$key item=$name}
                    <option value="{$key}" {if $_Get->data['type'] == $key}selected{/if}>{$name}</option>
                {/foreach}
            </select>
        </div>


        <span class="input-group-btn col-xs-2 no-padding-left">
            <button class="btn btn-info btn-sm" type="submit">
                <i class="icon-search icon-on-right bigger-110"></i>
            </button>
        </span>

        <button style="margin-right:12px;float:right;" class="btn btn-sm btn-success" href="#modal-form" data-toggle="modal" onclick="clearForm()">
            <i class="icon-plus bigger-110"></i>添加&nbsp;
        </button>
    </div>


</form>


<div class="clearfix"></div>



    {if $catArray}
<form class="form-horizontal" id="CategorySortForm" action="/category/sort" method="post">
<table id="sample-table-1" class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th style="width:70px;">排序</th>
            <th style="width:80px;">分类ID</th>
            <th style="width:300px;">分类名称</th>
            <th>分类类型</th>
            <th>分类Key</th>
            <th>分类描述</th>
            <th style="width:80px;" class="center">操作</th>
        </tr>
    </thead>

    <tbody>

        {foreach from=$catArray index=$index key=$key item=$item}
        <tr cat_info='{$_String->jsonEncode($item)}' id="{$item['cat_id']}">
            <td>
            <input type="text" name="orders[{$item['cat_id']}]" style="width:50px;" value="{$item['order']}"/>
            </td>
            <td>{$item['cat_id']}</td>
            <td>{$item['cat_name']}</td>
            <td>{$item['type_name']}</td>
            <td>{$item['cat_key']}</td>
            <td>{$item['brief']}</td>
            <td class="center" style="width:80px;" >
                <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                    <a href="#modal-form" data-toggle="modal" onclick="editForm({$item['cat_id']})" class="green" title="修改" data-rel="tooltip">
                        <i class="icon-pencil bigger-130"></i>
                    </a>

                    <a href="javascript:deleteItem({$item['cat_id']});" class="red ButtonDelete" title="删除" data-rel="tooltip">
                        <i class="icon-trash bigger-130"></i>
                    </a>
                </div>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<div style="margin:0;">
<button class="btn btn-sm btn-info" type="submit"><i class="icon-signal bigger-110"></i>排序</button>
</div>

<input name="type" type="hidden" value="{$type}" />
</form>

{else}

暂无分类信息，请先添加分类。

{/if}

<div id="modal-form" class="modal" tabindex="-1">
<form class="form-horizontal" id="validation-form" action="/category/edit" method="post">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="blue bigger">添加分类</h4>
            </div>

            <div class="modal-body overflow-visible">
                <div class="row">
                    <div class="col-xs-12 col-sm-12">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right">分类类型:</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="clearfix">
                                    <select name="type" style="width:200px;">
                                        {if $types}
                                            {foreach from=$types index=$index key=$key item=$item}
                                                <option value="{$key}">{$item}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right">父级分类:</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="clearfix">
                                <select name="pcat_id" style="width:200px;">
                                    <option value="0">作为顶级分类</option>
                                        {if $catArray}
                                            {foreach from=$catArray index=$index key=$key item=$item}
                                            <option value="{$item['cat_id']}" pcat_id="{$item['pcat_id']}">{$item['cat_name']}</option>
                                            {/foreach}
                                        {/if}
                                </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right">分类排序:</label>
                            <div class="col-xs-12 col-sm-10">
                            <div class="clearfix">
                                <input style="width:200px;" name="order" class="input-large" type="text" placeholder="序号越小,排序越靠前" value="99" />
                            </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right required">分类名称:</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="clearfix">
                                    <input name="cat_name"  class="col-xs-12 col-sm-10" type="text" placeholder="" value="" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right">分类Key:</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="clearfix">
                                <input name="cat_key"  class="col-xs-12 col-sm-10" type="text" placeholder="主要使用在模板中" value="" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right">分类图片:</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="clearfix">
                                <input name="thumb"  class="col-xs-12 col-sm-10" type="text" placeholder="点击选择或上传文章的缩略图" onclick="showThumbSelection()" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-2 no-padding-right">分类描述:</label>
                            <div class="col-xs-12 col-sm-10">
                                <div class="clearfix">
                                <textarea name="brief" class="col-xs-12 col-sm-10" placeholder="关于该分类的简单描述" style="position: inherit;height:100px;padding:5px 4px;" ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input name="name_type" type="hidden" value="1" />
                <input name="cat_id" type="hidden" value="" />
                <button class="btn btn-sm" data-dismiss="modal" type="button"><i class="icon-remove"></i>取消</button>
                <button class="btn btn-sm btn-primary" type="submit"><i class="icon-ok"></i>保存</button>
            </div>
        </div>
    </div>
</form>
</div>
                                
                                
</div>
</div>

{include _subinc/elfinder}

<script type="text/javascript">
//显示缩略图上传/选择文件对话框
function showThumbSelection()
{
    showFileManager("缩略图选择", function(files){
        $("input[name=thumb]").val(files);
    }, ['image']);
}

// 创建分类时清空表单数据
function clearForm()
{
    var form = $("#validation-form");
    form.find("h4").html("添加分类");
    form.find("select[name='type']").val(0);
    form.find("select[name='pcat_id']").val(0);
    form.find("select[name='pcat_id']").find("option").removeAttr("disabled");
    form.find("input[name='cat_id']").val(0);
    form.find("input[name='order']").val(99);
    form.find("input[name='cat_name']").val('');
    form.find("input[name='cat_key']").val('');
    form.find("input[name='thumb']").val('');
    form.find("textarea[name='brief']").val('');
}

// 添加数据到表单中
function editForm(id)
{
	var cat  = $.parseJSON($("tr[id=" + id + "]").attr("cat_info"));
    var form = $("#validation-form");
    form.find("h4").html("修改分类");
    form.find("select[name='type']").val(cat.type);
    form.find("select[name='pcat_id']").val(cat.pcat_id);
    form.find("select[name='pcat_id']").find("option").removeAttr("disabled");
    form.find("input[name='order']").val(cat.order);
    form.find("input[name='cat_name']").val(cat.old_name);
    form.find("textarea[name='brief']").val(cat.brief);
    form.find("input[name='cat_id']").val(cat.cat_id);
    form.find("input[name='cat_key']").val(cat.cat_key);
    form.find("input[name='thumb']").val(cat.thumb);
    // 自身以及子级不能作为自己的父级
    var cat_id = cat.cat_id;
    var select = form.find("select[name='pcat_id']");
    while (true) {
    	select.find("option[value='" + cat_id + "']").attr('disabled', true);
    	select.find("option[pcat_id='" + cat_id + "']").attr('disabled', true);
    	if (select.find("option[pcat_id='" + cat_id + "']").length > 0) {
    		cat_id = select.find("option[pcat_id='" + cat_id + "']").val();
    	} else {
    		break;
    	}
    }
}

// 删除数据项
function deleteItem(id)
{
	var cat_info = $.parseJSON($("tr[id=" + id + "]").attr("cat_info"));
    var message  = "<div class='bigger-110'>确认删除该分类: <font color=red>" + cat_info.cat_name + "</font>？</div>";
    // message += "<div>注意: 如果分类下有子级分类, 该分类将不能被删除!</div>";
    bootbox.dialog({
        message: message,
        buttons:            
        {
            "button" :
            {
                "label" : "取消",
                "className" : "btn-sm"
            },
            "danger" :
            {
                "label" : "删除",
                "className" : "btn-sm btn-danger",
                "callback": function() {
                    window.location.href='/category/delete?cat_id=' + cat_info.cat_id
                }
            }
        }
    });	
}

jQuery(function($) {
    $(".select2").css('width','120px').select2({allowClear:true}).on('change', function(){
        $(this).closest('form').validate().element($(this));
    });

    $('#validation-form').validate({
        errorElement: 'div',
        errorClass: 'help-block',
        focusInvalid: true,
        rules: {
            cat_name: {
                required: true
            }
        },
        messages: {
            cat_name: {
                required: "分类名称不能为空"
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
