<style type="text/css">
    #api-test-form textarea {
        font-family: Consolas, Monospace;
        padding-left:5px;
    }

    #api-test-form .col-sm-2 {
        width: 150px;
    }

    .params-table input {
        width: 100%;
    }

    .params-table .add-param-td {
        text-align: right;
    }

    .init-param {
        display: none;
    }

    input.param-name, input.param-name:active, input.param-name:focus {
        color: #0881E4 !important;
        font-weight: bold;
    }

    input.param-name::-webkit-input-placeholder {
        font-weight: normal;
    }

　　 input.param-name:-moz-placeholder {
        font-weight: normal;
    }

　　 input.param-name::-moz-placeholder {
        font-weight: normal;
    }

　　 input.param-name:-ms-input-placeholder {
        font-weight: normal;
        　　
    }

</style>


<div class="row">
    <div class="col-xs-12">
        <form class="form-horizontal" id="api-test-form" action="/api-test/ajaxRequest" method="post">
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right required">请求方式:</label>
                <div class="col-xs-12 col-sm-8">
                    <div class="clearfix">
                        <select class="select2" name="request_type" style="width:300px;">
                            <option value="GET" {if $data['request_type'] == 'GET' }selected{/if}>GET</option>
                            <option value="POST" {if $data['request_type'] == 'POST' }selected{/if}>POST</option>
                            <option value="PUT" {if $data['request_type'] == 'PUT' }selected{/if}>PUT</option>
                            <option value="DELETE" {if $data['request_type'] == 'DELETE' }selected{/if}>DELETE</option>
                            {*<option value="SOCKET" {if $data['request_type'] == 'SOCKET' }selected{/if}>SOCKET</option>*}
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right required">测试名称:</label>
                <div class="col-xs-12 col-sm-10">
                    <div class="clearfix">
                        <input value="{$data['name']}" type="text" name="name" placeholder="用以保存当前测试接口的设置" class="col-xs-12 col-sm-12"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right required">接口地址:</label>
                <div class="col-xs-12 col-sm-10">
                    <div class="clearfix">
                        <input value="{$data['address']}" type="text" name="address" placeholder="请填写接口的完整绝对路径地址" class="col-xs-12 col-sm-12"/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right">请求参数:</label>

                <div class="col-xs-12 col-sm-10">
                    <div class="clearfix">
                        <table class="table table-striped table-bordered table-hover params-table request-params-table">
                            <thead>
                            <tr>
                                <th style="width:130px;" class="center">参数名称</th>
                                <th>参数内容</th>
                                <th style="width:50px;" class="center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="init-param">
                                <td>
                                    <input value="" class="param-name" type="text" name="request_params[name][]" placeholder="请输入参数名称"/>
                                </td>
                                <td>
                                    <input value="" type="text" name="request_params[content][]" placeholder="请输入参数内容"/>
                                </td>
                                <td class="center">
                                    <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                                        <a href="javascript:;" onclick="deleteParam(this)" class="red" title="删除"
                                           data-rel="tooltip">
                                            <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="add-param-td">
                                    <button class="btn btn-sm btn-primary add-request-param-button" type="button"><i class="ace-icon fa fa-plus bigger-110"></i>添加参数</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right">返回结果:</label>
                <div class="col-xs-12 col-sm-10">
                    <div class="clearfix">
                        <textarea name="response_content" placeholder="接口的执行结果将会显示在这里" class="col-xs-12 col-sm-12" style="height:300px;">{$data['response_content']}</textarea>
                    </div>
                </div>
            </div>


            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
                <div class="col-xs-12 col-sm-10">
                    <div class="clearfix">
                        <button class="btn btn-info" type="submit">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            执行&nbsp;&nbsp;&nbsp;
                        </button>

                        <button class="btn" type="button" onclick="window.history.go(-1);">
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
    // 删除参数(请求或者返回)
    function deleteParam(obj)
    {
        $(obj).parents('tr').remove();
    }

    $(document).ready(function () {
        // 添加请求参数按钮
        $('.add-request-param-button').click(function(){
            var html = '<tr class="request-param-tr-values">' + $(this).parents('tbody').find('tr:eq(0)').html() + '</tr>';
            $(this).parents('tbody').find('tr:last').before(html);
        });

        {if !$_Get->data['id']}
        // 初始化参数表数据
        $('.request-params-table').each(function(){
            if($(this).find('tbody tr').length < 4) {
                $(this).find('.add-request-param-button').click();
            }
        });
        {/if}

        // 表单验证
        $('#api-test-form').validate({
            errorElement: 'div',
            errorClass  : 'help-block',
            focusInvalid: true,
            rules       : {
                name: {
                    required: true
                },
                address: {
                    required: true
                }
            },
            messages    : {
                name: {
                    required: "测试名称不能为空"
                },
                address: {
                    required: "接口地址不能为空"
                }
            },
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    dataType: 'json',
                    success : function(result){
                        if (result.result) {
                            $("textarea[name='response_content']").val(result.data);
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



