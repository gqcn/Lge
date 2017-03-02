<style type="text/css">
.table thead > tr > th, 
.table tbody > tr > th, 
.table tfoot > tr > th, 
.table thead > tr > td, 
.table tbody > tr > td, 
.table tfoot > tr > td  {vertical-align: middle;}
a {
    hide-focus: expression(this.hideFocus=true);
    outline: none;
}
</style>


<div class="row">
<div class="col-xs-12">

<div style="margin:0 0 5px 0;">
<button onclick="window.location.href='/wechat-reply/item'" class="btn btn-sm btn-success" type="button" title="添加微信自定义回复" data-rel="tooltip">
    <i class="ace-icon fa fa-plus bigger-110"></i>添加回复&nbsp;
</button>
</div>


<div id="table-content">
    {if $list}
    <form class="form-horizontal" id="MenuSortForm" action="/wechat-reply/sort" method="post">
        <table id="sample-table-1" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th class="center" style="width:70px;">排序</th>
                    <th class="center" style="width:100px;">类型</th>
                    <th style="width:200px;">关键字</th>
                    <th>回复内容</th>
                    <th style="width:80px;" class="center">操作</th>
                </tr>
            </thead>

            <tbody>
            {if $list}
                {foreach from=$list index=$index key=$key item=$item}
                <tr>
                    <td class="center">
                    <input type="text" name="orders[{$item['id']}]" style="width:50px;" value="{$item['order']}"/>
                    </td>
                    <td class="center">{$item['typeName']}</td>
                    <td>{$item['keywords']}</td>
                    <td>{$item['content']}</td>
                    <td class="center">
                        <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                            <a href="/wechat-reply/item?id={$item['id']}" class="green" title="修改" data-rel="tooltip">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>

                            <a href="javascript:deleteItem({$item['id']}, '/wechat-reply/delete');" class="red ButtonDelete" title="删除" data-rel="tooltip">
                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                {/foreach}
            {/if}
            </tbody>
        </table>

        {if $list}
            <div style="margin:0;">
                <button class="btn btn-sm btn-info" type="submit"><i class="ace-icon fa fa-signal bigger-110"></i>排序</button>
            </div>
        {/if}
    </form>
{else}
    暂无自定义回复信息，请添加回复信息！
{/if}
</div>

</div>
</div>



<script type="text/javascript">
    // 删除数据项
    function deleteItem(id, url)
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
                        window.location.href = url+'?id=' + id
                    }
                }
            }
        });
    }

jQuery(function($) {

});
</script>
