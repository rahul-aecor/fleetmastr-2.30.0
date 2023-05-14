var currentDate = '';
var currentName = '';
function changeDateFormat(data,datepicker) {

    datepicker = $(datepicker[0]);
    if(data == "") {
        return false;
    }

    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;

    if (dateRegex.test(data)) {
        var dateRegexWithDash = /([0-9]{2})-([0-9]{2})-([0-9]{4})/;
        var dateRegexWithDot = /([0-9]{2}).([0-9]{2}).([0-9]{4})/;

        if (dateRegexWithDash.test(data)) {
            var dateParts = data.split('-');
            data = dateParts[1]+'/'+dateParts[0]+'/'+dateParts[2];
        }else if(dateRegexWithDot.test(data)){
            var dateParts = data.split('.');
            data = dateParts[1]+'/'+dateParts[0]+'/'+dateParts[2];
        } else {
            var dateParts = data.split('/');
            data = dateParts[1]+'/'+dateParts[0]+'/'+dateParts[2];
        }

    }

    var dateRegexWithSpace = /^[0-9]{2} [0-9]{2} [0-9]{4}/;

    if (dateRegexWithSpace.test(data)) {
        var dateParts = data.split(' ');
        data = dateParts[1]+'/'+dateParts[0]+'/'+dateParts[2];
    }

    var dateRegexWithSpaceNew = /^[0-9]{2} [0-9]{2} [0-9]{2}/;

    if (dateRegexWithSpaceNew.test(data)) {
        var dateParts = data.split(' ');
        data = dateParts[1]+'/'+dateParts[0]+'/'+dateParts[2];
    }

    var d = new Date(data),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if(d == 'Invalid Date') {
        var parent = datepicker.parent().parent();
        parent.find('.date-error').remove();
        parent.parent().addClass('has-error');
        parent.append('<p class="help-block help-block-error date-error">Enter a valid date</p>');
        datepicker.val("");
        return false;
    }

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    var val = $.datepicker.formatDate('dd M yy', new Date([year,month,day].join('/')));
    datepicker.val(val);
    currentName = '';
}

$('body').on('focus focusin',".date > input[type=text]",function(){

    if(!$(this).hasClass('no-script')) {
        $(this).removeAttr('readonly');
        $(this).removeClass('datepicker-pointer-events-none');
        $(this).css('cursor', 'text');
        currentDate = $(this).val();
        var parent = $(this).parent().parent();
        parent.find('.date-error').remove();
        parent.parent().removeClass('has-error');
    }

});

$('.date > input[type=text]').bind('keydown',function(e){

    if(!$(this).hasClass('no-script')) {
        if (e.which == 13) //13 is Enter/Return key.
        {
            e.stopImmediatePropagation();
        }
    }
}).datepicker();

$('body').on("keyup paste",".date > input[type=text]", function(event){
    if(!$(this).hasClass('no-script')) {
        event.preventDefault();
        currentDate = $(this).val();
    }
});

$('body').on('focusout',".date > input[type=text]",function(event){
    if(!$(this).hasClass('no-script')) {
        //event.preventDefault();
        var data = currentDate;
        var datepicker = $(this);
        if(currentName == $(this).attr('name')) {
            changeDateFormat(data, $(this));
        }
    }
});


$('.date > input[type=text]').bind('paste', function(e) {
    if(!$(this).hasClass('no-script')) {
        e.preventDefault();
        var data = e.originalEvent.clipboardData.getData('Text');
        currentDate = e.originalEvent.clipboardData.getData('Text');
        currentName = $(this).attr('name');
        var datepicker = $(this);
        setTimeout(function () {
            changeDateFormat(data, $(this));
        }, 100);

        //IE9 Equivalent ==> window.clipboardData.getData("Text");
    }
});