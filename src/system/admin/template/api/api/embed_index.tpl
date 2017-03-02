<style type="text/css">
    #api-table tr.sortable-placeholder {
        height:40px;
        border:1px solid red;
        border-style: dashed;
    }
</style>
<script src="{$sysurl}/assets/js/common.js"></script>


{if $list}
    <form class="form-horizontal" id="api-sort-form" action="/api-api/ajaxSort" method="post">
        <table id="api-table" class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th style="width:50px;" class="center">序号</th>
                <th>接口名称</th>
                <th>所属分类</th>
                <th>接口方式</th>
                <th>接口地址</th>
                <th style="width:90px;" class="center">操作</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$list index=$index key=$key item=$item}
                <tr>
                    <td class="center">{$index + 1}</td>
                    <td>
                        <input type="hidden" name="ids[]"  value="{$item['id']}">
                        {$_String->highlight($item['name'], $_Get->data['key'])}
                    </td>
                    <td>{$item['cat_name']}</td>
                    <td>{$item['content']['request_type']}</td>
                    <td>{$item['address']}</td>
                    <td class="center">
                        <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons" style="margin-top:10px;">
                            <a href="javascript:editApiInfo({$item['id']});" class="green" title="修改" data-rel="tooltip">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>
                            <a href="javascript:copyApiInfo({$item['id']});" class="blue" title="复制" data-rel="tooltip">
                                <i class="ace-icon fa fa-copy bigger-130"></i>
                            </a>
                            <a href="javascript:ajaxDeleteApi('/api-api/ajaxDelete?id={$item['id']}');" class="red ButtonDelete" title="删除" data-rel="tooltip">
                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </form>
{else}
    {if $cat['name']}
        暂无接口数据，请点击“添加接口”在该分类【{$cat['name']}】下添加接口。
    {else}
        暂无接口数据，请点击“添加接口”添加新接口。
    {/if}
{/if}



<script type="text/javascript">
    // 异步删除操作
    function ajaxDeleteApi(url)
    {
        var message = "<div class='bigger-110'>删除之后数据将无法恢复，确定要删除该数据？</div>";
        bootbox.dialog({
            message: message,
            buttons: {
                "button" : {
                    "label" : "取消",
                    "className" : "btn-sm"
                },
                "danger" : {
                    "label" : "删除",
                    "className" : "btn-sm btn-danger",
                    "callback": function() {
                        $.ajax({
                            url     : url,
                            dataType: 'json',
                            success : function(result){
                                if (result.result) {
                                    $.gritter.add({
                                        title: '成功提示',
                                        text : result.message,
                                        class_name: 'gritter-success gritter-right'
                                    });
                                    reloadCategoryAndApiList();
                                } else {
                                    $.gritter.add({
                                        title: '错误提示',
                                        text : result.message,
                                        class_name: 'gritter-error gritter-right'
                                    });
                                }
                                $('.modal-backdrop').hide();
                            }
                        });
                    }
                }
            }
        });
    }

    jQuery(function($) {
        // 排序
        $('#api-table tbody').sortable({
            placeholder: "sortable-placeholder"
        }).on('sortupdate', function(){
            $('#api-sort-form').ajaxSubmit({
                dataType: 'json',
                success : function(result){
                    if (result.result) {
                        $.gritter.add({
                            title: '成功提示',
                            text : '接口重新排完成',
                            class_name: 'gritter-success gritter-right'
                        });
                        showCategoryApiList({$_Get->data['catid']});
                    }
                }
            });
        });
    });

</script>
