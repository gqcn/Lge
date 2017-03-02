<link rel="stylesheet" href="/static/plugin/zTree_v3/css/zTreeStyle/zTreeStyle.css" type="text/css">
<script type="text/javascript" src="/static/plugin/zTree_v3/js/jquery.ztree.core-3.5.min.js"></script>
<script type="text/javascript" src="/static/plugin/zTree_v3/js/jquery.ztree.excheck-3.5.min.js"></script>
<script type="text/javascript">
    <!--
    var setting = {
        view: {
            showIcon: false
        },
        check: {
            enable: true
        },
        data: {
            simpleData: {
                enable: true
            }
        },
        callback: {
            beforeClick: beforeClick
        }
    };

    function beforeClick(treeId, treeNode) {
        var treeObj = $.fn.zTree.getZTreeObj("AuthTree");
        treeObj.checkNode(treeNode, !treeNode.checked, true);
        return false;
    }
    var zNodes = {$treeJson};
    // 获得所有选中的权限Key，组织成点号分隔的字符串返回
    function getCheckedKeys()
    {
        var treeObj = $.fn.zTree.getZTreeObj("AuthTree");
        var nodes   = treeObj.getCheckedNodes(true);
        var array   = new Array();
        for(var i = 0; i < nodes.length; ++i) {
            array.push(nodes[i].key);
        }
        return array.join(',');
    }

    $(document).ready(function(){
        $.fn.zTree.init($("#AuthTree"), setting, zNodes);
    });
    //-->
</script>

<div class="row">
    <div class="col-xs-12">
        <form class="form-horizontal" id="validation-form" action="/geoinfo/editExtra" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="{$data['id']}"/>
            <input type="hidden" name="keys" value=""/>

            <div class="row">
                <div class="col-xs-12 col-sm-12">
                    <div class="form-group">
                        <label class="control-label col-xs-12 col-sm-2 no-padding-right">可选择打开或关闭区域:</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="clearfix">
                                <ul id="AuthTree" class="ztree"></ul>
                            </div>
                        </div>
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

                        <button class="btn" type="button" onclick="history.go(-1)">
                            <i class="ace-icon fa fa-reply bigger-110"></i>
                            返回&nbsp;&nbsp;&nbsp;
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>


<script type="text/javascript">
    jQuery(function($) {


    });
</script>
