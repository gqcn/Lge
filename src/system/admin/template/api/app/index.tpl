<style type="text/css">
.ace-thumbnails > li {
    font-size: 14px;
    text-align: center;
    width:180px;
    border: 1px solid #aaa;
    margin:5px 10px 0 0;
}
.ace-thumbnails > li {
    /*cursor:move;*/
}
.ace-thumbnails > li img {
    display: inline !important;
}
.ace-thumbnails > li.sortable-placeholder {
    width:180px;
    height:200px;
    border-style: dashed;
}
.api-app-logo img {
    height:150px;
    padding:2px;
    border:1px solid #ccc;
}
.api-app-logo img:hover {
    border:1px solid #aaa;
}
</style>


<div class="row">
<div class="col-xs-12">

<div style="margin:0 0 5px 0;">
    <a href="/api-app/item" class="btn btn-sm btn-success" title="添加应用" data-rel="tooltip">
        <i class="ace-icon fa fa-plus bigger-110"></i>添加应用&nbsp;
    </a>
</div>


<div id="table-content">
{if $list}
    <div>鼠标点击拖动可对应用进行重新排序。</div>
    <div>
        <ul class="ace-thumbnails clearfix">
            {foreach from=$list index=$index key=$key item=$item}
                <li item-id="{$item['id']}">
                    <a href="/api-api?appid={$item['id']}" class="" title="接口管理" data-rel="tooltip">
                        <img width="150" height="150" src="{$item['thumb']}" />
                    </a>
                    <div class="text">
                        <div class="inner">{$item['name']}</div>
                    </div>

                    <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons" style="margin-top:10px;">
                        <a href="/api-api?appid={$item['id']}" class="" title="接口管理" data-rel="tooltip">
                            <i class="ace-icon fa fa-share-alt bigger-130"></i>
                        </a>
                        &nbsp;
                        <a href="/api-app/item?id={$item['id']})" class="green" title="修改应用" data-rel="tooltip">
                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                        </a>
                        &nbsp;
                        <a href="javascript:deleteItem('/api-app/delete?id={$item['id']}');" class="red ButtonDelete" title="删除应用" data-rel="tooltip">
                            <i class="ace-icon fa fa-trash-o bigger-130"></i>
                        </a>
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
{else}
    暂无任何应用信息，请点击“添加应用”按钮进行添加。
{/if}
</div>



</div>
</div>

{include _subinc/elfinder}

<script type="text/javascript">
jQuery(function($) {
    $('.ace-thumbnails').sortable({
        placeholder: "sortable-placeholder"
    }).on('sortupdate', function(){
        var ids = new Array();
        $('.ace-thumbnails li').each(function(){
            ids.push($(this).attr('item-id'));
        });
        if (ids.length > 0) {
            $.ajax({
                url     : '/api-app/sort',
                type    : 'post',
                data    : 'ids=' + ids.join(','),
                dataType: 'json',
                success : function(result){
                    if (result.result) {
                        $.gritter.add({
                            title: '成功提示',
                            text : '应用重新排完成',
                            class_name: 'gritter-success gritter-right'
                        });
                    }
                }
            });
        }
    });
});
</script>
