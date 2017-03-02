<style type="text/css">
    #api-save-form textarea  {
        font-family: Consolas, Monospace;
    }
    #api-save-form .col-sm-2 {
        width:120px;
    }
    textarea.api-brief {
        height:100px;
        padding-left:5px;
    }
    .params-table input {
        width:100%;
    }

    .params-table textarea {
        width:100%;
        height:50px;
    }

    .params-table .add-param-td {
        text-align: right;
    }
    .response-example-tabs textarea {
        width:100%;
        height:300px;
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
　　 input.param-name::-moz-placeholder{
        font-weight: normal;
    }
　　 input.param-name:-ms-input-placeholder{
        font-weight: normal;
        　　 }

    .response-type-tips {
        padding:10px 0 0 10px;
        color:#888;
    }
    .response-type-div {
        border:1px solid #ddd;
    }
    .response-type-div textarea {
        border:0;
    }
    .CodeMirror pre.CodeMirror-placeholder {
        color: #999;
    }
</style>
<link rel="stylesheet" href="/static/plugin/codemirror-5.21.0/lib/codemirror.css">
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/lib/codemirror.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/mode/loadmode.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/search/match-highlighter.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/edit/matchbrackets.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/display/placeholder.js"></script>
<script type="text/javascript" src="/static/plugin/codemirror-5.21.0/addon/display/autorefresh.js"></script>

<script src="{$sysurl}/assets/js/common.js"></script>
<script src="{$sysurl}/assets/js/api-api-item.js"></script>

<form class="form-horizontal" id="api-save-form" action="/api-api/item" method="post">
    <input type="hidden" name="id"     value="{$data['id']}"/>
    <input type="hidden" name="appid"  value="{$data['appid']}"/>
    <input type="hidden" name="cat_id" value="{$cat['id']}"/>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">接口分类:</label>
        <div class="col-xs-12 col-sm-6">
            <div class="clearfix">
                <select class="select2" name="cat_id" style="width:300px;">
                    {if $catList}
                        {foreach from=$catList index=$index key=$key item=$item}
                            <option value="{$item['id']}" {if $data['cat_id'] == $item['id'] }selected{/if}>{$item['name']}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">请求方式:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <select class="select2" name="content[request_type]" style="width:300px;">
                    <option value="GET" {if $data['content']['request_type'] == 'GET' }selected{/if}>GET</option>
                    <option value="POST" {if $data['content']['request_type'] == 'POST' }selected{/if}>POST</option>
                    <option value="PUT" {if $data['content']['request_type'] == 'PUT' }selected{/if}>PUT</option>
                    <option value="DELETE" {if $data['content']['request_type'] == 'DELETE' }selected{/if}>DELETE</option>
                    <option value="SOCKET" {if $data['content']['request_type'] == 'SOCKET' }selected{/if}>SOCKET</option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">接口名称:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <input value="{$data['name']}" type="text" name="name" placeholder="请输入接口名称" class="col-xs-12 col-sm-12" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right required">接口地址:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <input value="{$data['address']}" type="text" name="address" placeholder="请填写接口的相对路径或者完整的绝对路径，相对路径以'/'开始，例如：/sms/send" class="col-xs-12 col-sm-12" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">简要介绍:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <textarea name="brief" placeholder="请输入接口的简要介绍信息(本表单所有多行文本输入框均采用通用代码字体，方便代码编辑)" class="col-xs-12 col-sm-12 api-brief">{$data['brief']}</textarea>
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
                        <th style="width:120px;" class="center">参数类型</th>
                        <th style="width:130px;" class="center">参数状态</th>
                        <th>参数示例</th>
                        <th style="width:50px;"  class="center">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="init-param">
                        <td>
                            <input value="" class="param-name" type="text" name="content[request_params][name][]" placeholder="请输入参数名称"/>
                        </td>
                        <td>
                            <select name="content[request_params][type][]" style="width:130px;">
                                <option value="string">String (字符串)</option>
                                <option value="integer">Integer (数字)</option>
                                <option value="binary">Binary (二进制)</option>
                                <option value="array">Array (数组)</option>
                            </select>
                        </td>
                        <td>
                            <select name="content[request_params][status][]" style="width:130px;">
                                <option value="required">Required (必填)</option>
                                <option value="optional">Optional (选填)</option>
                                <option value="constant">Constant (常量)</option>
                            </select>
                        </td>
                        <td>
                            <input value="" type="text" name="content[request_params][example][]" placeholder="请输入参数示例值"/>
                        </td>
                        <td class="center" rowspan="2">
                            <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                                <a href="javascript:;" onclick="deleteParam(this)"  class="red" title="删除" data-rel="tooltip">
                                    <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr class="init-param">
                        <td colspan="4">
                            <textarea name="content[request_params][brief][]" placeholder="请输入参数说明"></textarea>
                        </td>
                    </tr>

                    {if $data['content']['request_params']}
                        {foreach from=$data['content']['request_params'] index=$index key=$key item=$item}
                            <tr>
                                <td>
                                    <input value="{$item['name']}" class="param-name" type="text" name="content[request_params][name][]" placeholder="请输入参数名称"/>
                                </td>
                                <td>
                                    <select name="content[request_params][type][]" style="width:130px;">
                                        <option value="string" {if $item['type'] == 'string'}selected{/if}>String (字符串)</option>
                                        <option value="integer" {if $item['type'] == 'integer'}selected{/if}>Integer (数字)</option>
                                        <option value="binary" {if $item['type'] == 'binary'}selected{/if}>Binary (二进制)</option>
                                        <option value="array" {if $item['type'] == 'array'}selected{/if}>Array (数组)</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="content[request_params][status][]" style="width:130px;">
                                        <option value="required" {if $item['status'] == 'required'}selected{/if}>Required (必填)</option>
                                        <option value="optional" {if $item['status'] == 'optional'}selected{/if}>Optional (选填)</option>
                                        <option value="constant" {if $item['status'] == 'constant'}selected{/if}>Constant (常量)</option>
                                    </select>
                                </td>
                                <td>
                                    <input value="{$item['example']}" type="text" name="content[request_params][example][]" placeholder="请输入参数示例值"/>
                                </td>
                                <td class="center" rowspan="2">
                                    <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                                        <a href="javascript:;" onclick="deleteParam(this)"  class="red" title="删除" data-rel="tooltip">
                                            <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <textarea name="content[request_params][brief][]" placeholder="请输入参数说明">{$item['brief']}</textarea>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    <tr>
                        <td colspan="5" class="add-param-td">
                            <button class="btn btn-sm btn-primary add-request-param-button" type="button"><i class="ace-icon fa fa-plus bigger-110"></i>添加参数</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">返回结构:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <div class="tabbable response-example-tabs">
                    <ul class="nav nav-tabs" id="response-type">
                        <li class="active">
                            <a data-toggle="tab" href="#response-type-json" id="response-type-tab-json">JSON</a>
                        </li>
                        <li class="">
                            <a data-toggle="tab" href="#response-type-xml"  id="response-type-tab-xml">XML</a>
                        </li>
                        <li class="">
                            <a data-toggle="tab" href="#response-type-jsonp" id="response-type-tab-jsonp">JSONP</a>
                        </li>
                        <li class="">
                            <a data-toggle="tab" href="#response-type-template" id="response-type-tab-template">模板变量</a>
                        </li>
                        <li class="">
                            <a data-toggle="tab" href="#response-type-other" id="response-type-tab-other">其他/自定义</a>
                        </li>
                        <li class="response-type-tips">
                            请在接口支持的返回数据结构中填写返回值示例。
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="response-type-json" class="response-type-div tab-pane active">
                            <textarea name="content[response_example][JSON]" placeholder="请输入JSON格式的返回参数示例">{$data['content']['response_example']['JSON']}</textarea>
                        </div>

                        <div id="response-type-xml" class="response-type-div tab-pane">
                            <textarea name="content[response_example][XML]" placeholder="请输入XML格式的返回参数示例">{$data['content']['response_example']['XML']}</textarea>
                        </div>

                        <div id="response-type-jsonp" class="response-type-div tab-pane">
                            <textarea name="content[response_example][JSONP]" placeholder="请输入JSONP格式的返回参数示例">{$data['content']['response_example']['JSONP']}</textarea>
                        </div>

                        <div id="response-type-template" class="response-type-div tab-pane">
                            <textarea name="content[response_example][Template]" placeholder="请输入用于服务端MVC的模板变量示例">{$data['content']['response_example']['Template']}</textarea>
                        </div>

                        <div id="response-type-other" class="response-type-div tab-pane">
                            <textarea name="content[response_example][Other]" placeholder="请输入返回示例">{$data['content']['response_example']['Other']}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">返回参数:</label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <div style="padding-bottom: 8px;">
                    <button class="btn btn-sm btn-success check-response-params" type="button">
                        <i class="ace-icon fa fa-long-arrow-down bigger-110"></i>从返回示例自动识别返回参数
                    </button>
                </div>
                <table class="table table-striped table-bordered table-hover params-table response-params-table">
                    <thead>
                    <tr>
                        <th style="width:130px;" class="center">参数名称</th>
                        <th style="width:120px;" class="center">参数类型</th>
                        <th>参数示例</th>
                        <th style="width:50px;"  class="center">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="init-param">
                        <td>
                            <input value="" class="param-name" type="text" name="content[response_params][name][]" placeholder="请输入参数名称"/>
                        </td>
                        <td>
                            <select name="content[response_params][type][]" style="width:130px;">
                                <option value="string">String (字符串)</option>
                                <option value="integer">Integer (数字)</option>
                                <option value="binary">Binary (二进制)</option>
                                <option value="array">Array (数组)</option>
                                <option value="object">Object (对象)</option>
                            </select>
                        </td>
                        <td>
                            <input value="" type="text" name="content[response_params][example][]" placeholder="请输入参数示例值"/>
                        </td>
                        <td class="center" rowspan="2">
                            <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                                <a href="javascript:;" onclick="deleteParam(this)" class="red" title="删除" data-rel="tooltip">
                                    <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr class="init-param">
                        <td colspan="3">
                            <textarea name="content[response_params][brief][]" placeholder="请输入参数说明"></textarea>
                        </td>
                    </tr>
                    {if $data['content']['response_params']}
                        {foreach from=$data['content']['response_params'] index=$index key=$key item=$item}
                            <tr class="response-param-tr-values">
                                <td>
                                    <input value="{$item['name']}" class="param-name" type="text" name="content[response_params][name][]" placeholder="请输入参数名称"/>
                                </td>
                                <td>
                                    <select name="content[response_params][type][]" style="width:130px;">
                                        <option value="string" {if $item['type'] == 'string'}selected{/if}>String (字符串)</option>
                                        <option value="integer" {if $item['type'] == 'integer'}selected{/if}>Integer (数字)</option>
                                        <option value="binary" {if $item['type'] == 'binary'}selected{/if}>Binary (二进制)</option>
                                        <option value="array" {if $item['type'] == 'array'}selected{/if}>Array (数组)</option>
                                        <option value="object" {if $item['type'] == 'object'}selected{/if}>Object (对象)</option>
                                    </select>
                                </td>
                                <td>
                                    <input value="{$item['example']}" type="text" name="content[response_params][example][]" placeholder="请输入参数示例值"/>
                                </td>
                                <td class="center" rowspan="2">
                                    <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                                        <a href="javascript:;" onclick="deleteParam(this)" class="red" title="删除" data-rel="tooltip">
                                            <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr class="response-param-tr-brief">
                                <td colspan="3">
                                    <textarea name="content[response_params][brief][]" placeholder="请输入参数说明">{$item['brief']}</textarea>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    <tr>
                        <td colspan="4" class="add-param-td">
                            <button class="btn btn-sm btn-primary add-response-param-button" type="button"><i class="ace-icon fa fa-plus bigger-110"></i>添加参数</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right">详细介绍:</label>
        <div class="col-xs-12 col-sm-10">
            <div style="margin:0 0 5px 0;">
                <button class="btn btn-sm btn-info" onclick="showMediaSelection()" type="button">
                    <i class="ace-icon fa fa-inbox"></i> 添加媒体 </button>
                格式支持：图片(png、jpg、jpeg、gif), 音频(mp3、wav、ogg等), 视频(flv、mp4、mov、f4v等)
            </div>
            <textarea name="detail" id="editor">{$data['detail']}</textarea>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2 no-padding-right"></label>
        <div class="col-xs-12 col-sm-10">
            <div class="clearfix">
                <button class="btn btn-info" type="submit">
                    <i class="ace-icon fa fa-check bigger-110"></i>
                    保存&nbsp;&nbsp;&nbsp;
                </button>

                <button class="btn" type="button" onclick="reloadCategoryAndApiList();">
                    <i class="ace-icon fa fa-reply bigger-110"></i>
                    返回&nbsp;&nbsp;&nbsp;
                </button>
            </div>
        </div>
    </div>
</form>




<script type="text/javascript">
    $(document).ready(function(){
        {if !$_Get->data['id']}
        // 初始化参数表数据
        $('.request-params-table').each(function(){
            if($(this).find('tbody tr').length < 4) {
                $(this).find('.add-request-param-button').click();
            }
        });
        $('.response-params-table').each(function(){
            if($(this).find('tbody tr').length < 4) {
                $(this).find('.add-response-param-button').click();
            }
        });
        {/if}
    });
</script>



