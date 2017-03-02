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

    <div style="margin:0 0 5px 0;">
        <button class="btn btn-sm btn-success" type="button" title="添加微信永久素材" data-rel="tooltip" href="#modal-form" data-toggle="modal" onclick="clearForm()"><i class="ace-icon fa fa-plus bigger-110"></i>添加素材&nbsp;</button>
        <button id="update-menu" onclick="updateMaterial()" class="btn btn-sm btn-primary" type="button" title="从微信服务器将素材同步到本地" data-rel="tooltip"><i class="ace-icon fa fa-cloud-download bigger-110"></i>同步素材&nbsp;</button>
    </div>


<div class="clearfix"></div>

{if $list}
<div class="row-fluid">
    <ul class="ace-thumbnails">
    {if $list}
        {foreach from=$list index=$index key=$key item=$item}
        <li>
            <a target="_blank" href="{$item['thumb']}" class="cboxElement">
                <img src="{$item['thumb']}?150_150" alt="{$item['title']}" width="150" height="150">
                <div class="text">
                    <div class="inner">{$item['title']}</div>
                </div>
            </a>

            <div class="tools tools-bottom">
                <a href="?app=picture&act=showEdit&id={$item['picture_id']}" class="green" title="修改" data-rel="tooltip">
                    <i class="icon-pencil bigger-130"></i>
                </a>

                <a href="javascript:deleteItem({$item['picture_id']});" class="red ButtonDelete" title="删除" data-rel="tooltip">
                    <i class="icon-trash bigger-130"></i>
                </a>
            </div>
        </li>
        {/foreach}
    {/if}
    </ul>
</div>

<div class="clearfix"></div>


{else}
    暂无素材信息，请同步或添加素材！
{/if}

</div>
</div>



<script type="text/javascript">
// 删除数据项
function deleteItem(id)
{
    var message = "<div class='bigger-110'>确认删除该图片？</div>";
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
                    window.location.href='?app=picture&act=delete&id=' + id
                }
            }
        }
    });
}

jQuery(function($) {
   $(".select2").css('width','180px').select2({allowClear:true})
    .on('change', function(){
        $(this).closest('form').validate().element($(this));
    }); 
});
</script>
