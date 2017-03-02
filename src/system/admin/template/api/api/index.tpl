<style type="text/css">
    html { overflow-x: hidden; overflow-y: auto; }
    #api-category-content {
        padding:0 5px 0 0;
    }
    #api-api-content {
        min-height:300px;
        padding:0 0 0 5px;
        border-left:1px solid #eee;
    }
</style>


<div class="row">
    <div class="col-xs-12" style="padding:0 0 10px 0;">
        <div style="float:left;margin:0 10px 0 0;">
            <button href="#modal-form" data-toggle="modal" onclick="clearForm()" class="btn btn-sm btn-success" type="button" title="添加分类" data-rel="tooltip">
                <i class="ace-icon fa fa-plus bigger-110"></i>添加分类&nbsp;
            </button>
        </div>

        <div style="float:left;margin:0 10px 0 0;">
            <a href="javascript:addApiToCategory({$cat['id']});" class="btn btn-sm btn-primary" title="添加接口" data-rel="tooltip">
                <i class="ace-icon fa fa-plus bigger-110"></i>添加接口&nbsp;
            </a>
        </div>

        <div style="float:left;margin:0 10px 0 0;" title="选择可切换应用" data-rel="tooltip">
            <select class="select3" name="appid" style="width:180px;">
                {foreach from=$apps index=$index key=$key item=$item}
                    <option value="{$item['id']}" {if $item['id'] == $_Get->data['appid']}selected{/if}>{$item['name']}</option>
                {/foreach}
            </select>
        </div>

        <div class="input-group col-xs-3 no-padding-left" title="接口搜索" data-rel="tooltip" >
            <input type="text" name="key" placeholder="请输入接口名称关键字进行搜索" class="form-control search-query" value="{$_String->escape($_Get->data['key'])}">
            <span class="input-group-btn">
                <button class="btn btn-info btn-sm" type="button" onclick="searchApi()">
                    <i class="ace-icon fa fa-search fa-on-right bigger-110"></i>
                </button>
            </span>
        </div>

    </div>

    <div id="api-category-content" class="col-xs-12 col-lg-3"></div>
    <div id="api-api-content" class="col-xs-12 col-lg-9"></div>
</div>

{include _subinc/elfinder}
{include _subinc/ueditor}

<script type="text/javascript">

    // 设置当前操作的名称，该名称将会显示在breadcrumb末尾
    function setCurrentActionName(name)
    {
        $('.breadcrumb .active').html(name);
    }

    // 应用选择切换
    function onAppSelectionChange(appid)
    {
        window.location.href = '/api-api?appid=' + appid
    }

    // 重新加载分类页面
    function reloadCategory()
    {
        setCurrentActionName('接口管理');
        $('#api-category-content').load('/api-category?appid={$_Get->data['appid']}&__content=1');
    }

    // 重新加载分类页面，并且保持分类的选中状态
    function reloadCategoryAndApiList()
    {
        setCurrentActionName('接口管理');
        $('#api-category-content').load('/api-category?appid={$_Get->data['appid']}&__content=1', function(){
            if (currentCatid > 0) {
                onClickCategory(currentCatid);
            }
        });
        if (currentCatid == 0) {
            showCategoryApiList(0);
        }
    }

    // 展示分类的接口列表
    function showCategoryApiList(catid)
    {
        setCurrentActionName('接口管理');
        if (typeof catid != 'undefined') {
            currentCatid = catid;
        } else {
            catid        = currentCatid;
        }
        $('#api-api-content').load('/api-api/apilist?appid={$_Get->data['appid']}&catid=' + catid + '&__content=1');
    }

    // 在分类下添加API接口
    function addApiToCategory(catid)
    {
        setCurrentActionName('添加接口');
        if (typeof catid != 'undefined') {
            currentCatid = catid;
        } else {
            catid        = currentCatid;
        }
        $('#api-api-content').load('/api-api/item?appid={$_Get->data['appid']}&catid=' + catid + '&__content=1');
    }

    // 修改接口信息
    function editApiInfo(id)
    {
        setCurrentActionName('修改接口');
        $('#api-api-content').load('/api-api/item?appid={$_Get->data['appid']}&id=' + id + '&__content=1');
    }

    // 复制接口信息
    function copyApiInfo(id)
    {
        setCurrentActionName('复制接口');
        $('#api-api-content').load('/api-api/item?appid={$_Get->data['appid']}&id=' + id + '&__copy=1&__content=1');
    }

    // 根据关键字搜索API
    function searchApi()
    {
        var key = $("input[name=key]").val();
        $('#api-api-content').load('/api-api/apilist?appid={$_Get->data['appid']}&key=' + encodeURIComponent(key) + '&__content=1');
    }

    var currentAppid    = {$_Get->data['appid']};
    var currentCatid    = 0;

    jQuery(function($) {
        $(".select3").select2({allowClear:true}).on('change', function(){
            onAppSelectionChange($(this).val());
        });

        reloadCategoryAndApiList();
    });

</script>
