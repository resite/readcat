(function(global,undefined){
if(global.tmpl){
return
}

var tmpl = global.tmpl={}

tmpl.hello = function(){
    alert('hello');
}

tmpl.industry_max = 1;
tmpl.region_max = 4;
tmpl.industry_url = '/index.php?view=rest&entry=industry&parent_id=';
tmpl.region_url = '/index.php?view=rest&entry=region&parent_id=';
tmpl.upfile_url = '/index.php?view=rest&entry=upfile';

function option(data,selected=''){
    var html = '<option></option>';
    $.each(data,function(k,v){
        html += '<option value="'+k+'"';
        if(k == selected) html += ' selected';
        html += ' >'+v+'</option>';
    })
    return html;
}

function radio(data,name='',selected=''){
    var html = '';
    $.each(data,function(k,v){
        html += '<input type="radio" id="'+name+'" name="'+name+'" value="'+k+'"';
        if(k == checked) html += ' checked';
        html += ' />'+v;
    })
    return html;
}

function checkbox(data,name='',checked=''){
    var html = '';
    $.each(data,function(k,v){
        html += '<input type="checkbox" id="'+name+'" name="'+name+'" value="'+k+'"';
        if(k == checked) html += ' checked';
        html += ' />'+v;
    })
    return html;
}

tmpl.industry_option = function(obj,parent_id){
    $.getJSON(tmpl.industry_url+parent_id,function(data){
        if(data.error==0){
            $(obj).html(option(data.data));
        }
    });
}

tmpl.region_option = function(obj,parent_id){
    $.getJSON(tmpl.region_url+parent_id,function(data){
        if(data.error==0){
            $(obj).html(option(data.data));
        }
    });
}

tmpl.region_checkbox = function(obj,parent_id,name){
    $.getJSON(tmpl.region_url+parent_id,function(data){
        if(data.error==0){
            $(obj).html(checkbox(data.data,name));
        }
    });
}

tmpl.refresh_captcha = function(element){
    $(element).attr('src',"/index.php?view=index&entry=captcha&"+new Date().getTime());
}

})(this);

//全局初始化
$(document).ready(function(){
//日期选择datepicker
jQuery(function($){
    if(!$.datepicker) return false;
    
    $.datepicker.regional['zh-CN'] = {
    clearText: '清除', 
    clearStatus: '清除已选日期', 
    closeText: '关闭', 
    closeStatus: '不改变当前选择', 
    prevText: '<上月', 
    prevStatus: '显示上月', 
    prevBigText: '<<', 
    prevBigStatus: '显示上一年', 
    nextText: '下月>', 
    nextStatus: '显示下月', 
    nextBigText: '>>', 
    nextBigStatus: '显示下一年', 
    currentText: '今天', 
    currentStatus: '显示本月', 
    monthNames: ['一月','二月','三月','四月','五月','六月', '七月','八月','九月','十月','十一月','十二月'], 
    monthNamesShort: ['一','二','三','四','五','六', '七','八','九','十','十一','十二'], 
    monthStatus: '选择月份', 
    yearStatus: '选择年份', 
    weekHeader: '周', 
    weekStatus: '年内周次', 
    dayNames: ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'], 
    dayNamesShort: ['周日','周一','周二','周三','周四','周五','周六'], 
    dayNamesMin: ['日','一','二','三','四','五','六'], 
    dayStatus: '设置 DD 为一周起始', 
    dateStatus: '选择 m月 d日, DD', 
    dateFormat: 'yy-mm-dd', 
    firstDay: 1, 
    initStatus: '请选择日期', 
    isRTL: false}; 
    $.datepicker.setDefaults($.datepicker.regional['zh-CN']); 
});

//表单验证jquery.validate
if($.validator){
$.extend($.validator.defaults, {
    errorElement : 'span',
    errorClass : 'help-block',
    focusInvalid : false,
    errorPlacement:function(error,element){
        element.parent('div').append(error);
    },
    success:function(label) {
        label.closest('.form-group').removeClass('has-error');
        label.remove();
    },
    highlight:function(element) {
        $(element).closest('.form-group').addClass('has-error');
    }
});

$.extend($.validator.messages, {
    required: "必选字段",
	remote: "请修正该字段",
	email: "请输入正确格式的电子邮件",
	url: "请输入正确的网址",
	date: "请输入正确的日期",
	dateISO: "请输入正确的日期 (ISO).",
	number: "请输入正确的数字",
	digits: "只能输入整数",
	creditcard: "请输入正确的信用卡号",
	equalTo: "请再次输入相同的值",
	accept: "请输入拥有正确后缀名的字符串",
	maxlength: $.validator.format("长度不多于{0}"),
	minlength: $.validator.format("长度不少于{0}"),
	rangelength: $.validator.format("长度应介于 {0} 和 {1} 之间"),
	range: $.validator.format("请输入一个介于 {0} 和 {1} 之间的值"),
	max: $.validator.format("请输入一个最大为{0} 的值"),
	min: $.validator.format("请输入一个最小为{0} 的值")
});
$.validator.addMethod("mob_phone", function(value, element){
    var length = value.length;
    return this.optional(element) || length == 11 && /^1[358]\d{9}$/.test(value);
}, "请填写正确的手机号码");
$.validator.addMethod("idcard", function(gets, element){
    /*
        该方法由网友提供;
        对身份证进行严格验证;
    */
    if(this.optional(element)) return true;

    var Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ];// 加权因子;
    var ValideCode = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ];// 身份证验证位值，10代表X;

    if (gets.length == 15) {   
        return isValidityBrithBy15IdCard(gets);   
    }else if (gets.length == 18){   
        var a_idCard = gets.split("");// 得到身份证数组   
        if (isValidityBrithBy18IdCard(gets)&&isTrueValidateCodeBy18IdCard(a_idCard)) {   
            return true;   
        }   
        return false;
    }
    return false;
    
    function isTrueValidateCodeBy18IdCard(a_idCard) {   
        var sum = 0; // 声明加权求和变量   
        if (a_idCard[17].toLowerCase() == 'x') {   
            a_idCard[17] = 10;// 将最后位为x的验证码替换为10方便后续操作   
        }   
        for ( var i = 0; i < 17; i++) {   
            sum += Wi[i] * a_idCard[i];// 加权求和   
        }   
        valCodePosition = sum % 11;// 得到验证码所位置   
        if (a_idCard[17] == ValideCode[valCodePosition]) {   
            return true;   
        }
        return false;   
    }
    
    function isValidityBrithBy18IdCard(idCard18){   
        var year = idCard18.substring(6,10);   
        var month = idCard18.substring(10,12);   
        var day = idCard18.substring(12,14);   
        var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));   
        // 这里用getFullYear()获取年份，避免千年虫问题   
        if(temp_date.getFullYear()!=parseFloat(year) || temp_date.getMonth()!=parseFloat(month)-1 || temp_date.getDate()!=parseFloat(day)){   
            return false;   
        }
        return true;   
    }
    
    function isValidityBrithBy15IdCard(idCard15){   
        var year =  idCard15.substring(6,8);   
        var month = idCard15.substring(8,10);   
        var day = idCard15.substring(10,12);
        var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));   
        // 对于老身份证中的你年龄则不需考虑千年虫问题而使用getYear()方法   
        if(temp_date.getYear()!=parseFloat(year) || temp_date.getMonth()!=parseFloat(month)-1 || temp_date.getDate()!=parseFloat(day)){   
            return false;   
        }
        return true;
    }
    
}, "请填写正确的身份证号码");

}
});
