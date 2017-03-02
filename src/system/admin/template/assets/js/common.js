$(document).ready(function(){
    // 如果本文件被重复引入，那么以下部分代码会重复执行，会造成不可预料的错误，因此加try catch
    try {
        // 为必填字段添加提示
        $('.required').each(function(){
            if ($(this).parent().find('i.fa-asterisk').length < 1) {
                $(this).append('&nbsp;<i class="ace-icon fa fa-asterisk red" style="font-size:10px;"></i>');
            }
        });

        // tooltips
        $('[data-rel=tooltip]').tooltip();
        $('[data-rel=popover]').popover({html:true});

        // select插件
        $(".select2").select2({allowClear:true});

        // 日期选择插件
        $('.date-picker').datepicker({language:'cn', autoclose:true}).next().on(ace.click_event, function () {
            $(this).prev().focus();
        });
    } catch (e){

    }
});


// 列表也的删除操作
function deleteItem(url)
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
                    window.location.href = url
                }
            }
        }
    });
}

// 确认退出登录
function confirmLogout()
{
    bootbox.dialog({
        message: "确定注销当前登录状态，退出管理后台？",
        buttons:            
        {
            "button" :
            {
                "label" : "取消",
                "className" : "btn-sm"
            },
            "danger" :
            {
                "label" : "确定",
                "className" : "btn-sm btn-info",
                "callback": function() {
                    window.location.href='/logout/index'
                }
            }
        }
    });	
}

// 显示文件管理器窗口
function showFileManager(title, getFileCallback, onlyMimes)
{
    $('<div/>').dialogelfinder({
        url            : '/static/plugin/elfinder_2_0_rc1/php/connector.php',
        lang           : 'zh_CN',
        title          : title,
        width          : 850,
        height         :550,
        onlyMimes      : onlyMimes,
        destroyOnClose : true,
        resizable      : false,
        debug          : false,
        getFileCallback : function(files, fm) {
        	getFileCallback(files);
        },
        uiOptions : {
            toolbar : [
                        ['home', 'up', 'back', 'forward'],
                        ['mkdir', 'mkfile', 'upload'],
                        ['open', 'download', 'getfile'],
                        ['copy', 'cut', 'paste'],
                        ['rm'],
                        ['duplicate', 'rename', 'edit'],
                        ['info', 'quicklook', 'view', 'sort'],
                        // ['resize'], ['help'],
                        ['reload'],
                        ['search'],
                        // ['archive', 'extract'],
            ]
        },
        commandsOptions : {
            getfile : {
                oncomplete : 'close',
                folders    : false
            }
        }
    }).dialogelfinder('instance');

    /**
     * 通过第三方代码来解决文件弹出框拖动的BUG：当在弹出框中打开文件选择窗口时，拖动会异常。
     */
    function checkFileDialogPosition() {
        setTimeout(function(){
            if ($('.dialogelfinder').length > 0) {
                $('.dialogelfinder').each(function(){
                    var position = $(this).position();
                    if (position.top < 0) {
                        $(this).css('top', '0px');
                    }
                    if (position.left < 0) {
                        $(this).css('left', '0px');
                    }
                });
                checkFileDialogPosition();
            }
        }, 100);
    }
    checkFileDialogPosition();

}

// 添加多媒体文件到编辑器
// 多媒体格式支持: 图片(png、jpg、jpeg、gif、bmp), 音频(mp3、aac、wav、ogg、ogv、m4a), 视频(flv、mp4、mov、f4v、3gp、3g2) 
function addMediaToUEditor(file)
{
    var type = file.substr(file.lastIndexOf('.') + 1).toLowerCase();
    switch (type) {
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'bmp':
        	content = '<img src="'+ file +'" />';
        	break;
        	
        case 'mp3':
        case 'aac':
        case 'wav':
        case 'ogg':
        case 'ogv':
        case 'm4a':
            content ='<embed type="application/x-shockwave-flash" class="edui-faked-music" pluginspage="http://www.macromedia.com/go/getflashplayer" src="'+file+'" width="400" height="95"/>';


        	break;

        case 'flv':
        case 'mp4':
        case 'mov':
        case 'f4v':
        case '3gp':
        case '3g2':
            content ='<embed type="application/x-shockwave-flash" class="edui-faked-video" pluginspage="http://www.macromedia.com/go/getflashplayer" src="'+file+'" width="420" height="280"/>';

            break;
        	
    	default:
    		content = '<a href="'+ file +'" target="_blank">' + file + '</a>';
    		break;
    }
    UE.getEditor('editor').execCommand('insertHtml', content);
}

