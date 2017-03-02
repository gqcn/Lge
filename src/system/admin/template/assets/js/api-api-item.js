var codeMirrorJson     = null;
var codeMirrorJsonp    = null;
var codeMirrorXml      = null;
var codeMirrorTemplate = null;
var codeMirrorOther    = null;
CodeMirror.modeURL     = "/static/plugin/codemirror-5.21.0/mode/%N/%N.js";
var codeMirrorOptions  = {
    theme:                   "default",
    indentUnit:              4,
    smartIndent:             true,
    lineWrapping:            false,
    lineNumbers:             false,
    showCursorWhenSelecting: true,
    autofocus:               false,
    autoCloseTags:           true,
    matchBrackets:           true,
    autoRefresh:             true,
    readOnly:                false
};
var tabToSpaceFunction = {
    Tab: function(cm) {
        cm.indentSelection('add');
    }
};

// 删除参数(请求或者返回)
function deleteParam(obj)
{
    var preType = 0; // 1:参数属性, 2:参数说明
    $(obj).parents('tr').attr('deleted', true);
    $(obj).parents('tbody').find('tr').each(function(){
        if ($(this).attr('deleted')) {
            $(this).remove();
        } else {
            if ($(this).find('textarea').length > 0) {
                if (preType != 1) {
                    preType = 2;
                    $(this).remove();
                } else {
                    preType = 2;
                }
            } else {
                preType = 1;
            }
        }
    });
}

$(document).ready(function(){
    UE.delEditor('editor');
    UE.getEditor('editor');

    /**
     * 返回示例代码高亮处理
     */
    codeMirrorJson     = CodeMirror.fromTextArea($('#response-type-json textarea')[0], codeMirrorOptions);
    codeMirrorJsonp    = CodeMirror.fromTextArea($('#response-type-jsonp textarea')[0], codeMirrorOptions);
    codeMirrorXml      = CodeMirror.fromTextArea($('#response-type-xml textarea')[0], codeMirrorOptions);
    codeMirrorTemplate = CodeMirror.fromTextArea($('#response-type-template textarea')[0], codeMirrorOptions);
    codeMirrorOther    = CodeMirror.fromTextArea($('#response-type-other textarea')[0], codeMirrorOptions);
    codeMirrorJson.setOption("extraKeys",       tabToSpaceFunction);
    codeMirrorJsonp.setOption("extraKeys",      tabToSpaceFunction);
    codeMirrorXml.setOption("extraKeys",        tabToSpaceFunction);
    codeMirrorTemplate.setOption("extraKeys",   tabToSpaceFunction);
    codeMirrorOther.setOption("extraKeys",      tabToSpaceFunction);
    codeMirrorJson.setOption("mode",            'javascript');
    codeMirrorJsonp.setOption("mode",           'javascript');
    codeMirrorXml.setOption("mode",             'xml');
    codeMirrorTemplate.setOption("mode",        'htmlmixed');
    codeMirrorOther.setOption("mode",           'htmlmixed');
    CodeMirror.autoLoadMode(codeMirrorJson,     'javascript');
    CodeMirror.autoLoadMode(codeMirrorJsonp,    'javascript');
    CodeMirror.autoLoadMode(codeMirrorXml,      'xml');
    CodeMirror.autoLoadMode(codeMirrorTemplate, 'htmlmixed');
    CodeMirror.autoLoadMode(codeMirrorOther,    'htmlmixed');

    // 添加请求参数按钮
    $('.add-request-param-button').click(function(){
        var html = '<tr class="request-param-tr-values">' + $(this).parents('tbody').find('tr:eq(0)').html() + '</tr>';
        html    += '<tr class="request-param-tr-brief">' + $(this).parents('tbody').find('tr:eq(1)').html() + '</tr>';
        $(this).parents('tbody').find('tr:last').before(html);
    });

    // 添加返回参数按钮
    $('.add-response-param-button').click(function(){
        var html = '<tr class="response-param-tr-values">' + $(this).parents('tbody').find('tr:eq(0)').html() + '</tr>';
        html    += '<tr class="response-param-tr-brief">' + $(this).parents('tbody').find('tr:eq(1)').html() + '</tr>';
        $(this).parents('tbody').find('tr:last').before(html);
    });

    // 通过返回示例自动识别返回参数
    $('.check-response-params').click(function(){
        $.ajax({
            url     : '/api-api/ajaxCheckRespnseParams',
            type    : 'post',
            data    : {
                json: codeMirrorJson.getValue(),
                xml : codeMirrorXml.getValue()
            },
            dataType: 'json',
            success : function(result){
                if (result.result) {
                    $('.response-param-tr-values').remove();
                    $('.response-param-tr-brief').remove();
                    for (var i = 0; i < result.data.length; ++i) {
                        $('.response-params-table .add-response-param-button').click();
                        $('.response-param-tr-values:last').find("input[name='content[response_params][name][]']").val(result.data[i].name);
                        $('.response-param-tr-values:last').find("select[name='content[response_params][type][]']").val(result.data[i].type);
                        $('.response-param-tr-values:last').find("input[name='content[response_params][example][]']").val(result.data[i].example);
                    }
                    $.gritter.add({
                        title: '成功提示',
                        text : '返回参数已自动识别',
                        class_name: 'gritter-success gritter-right'
                    });
                } else {
                    $.gritter.add({
                        title: '错误提示',
                        text : result.message,
                        class_name: 'gritter-error gritter-right'
                    });
                }
            }
        });
    });

    $('#api-save-form').validate({
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
                required: "接口名称不能为空"
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