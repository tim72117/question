$(function() {

    var doc = document,
        win = window;

    function count(options, callback) {

        var limit="10:01";
        var timeout = options.timeout;
        var timeout_alert = options.timeout_alert;
        var msg1 =  options.msg1;
        var msg2 =  options.msg2;
        var reset1 =  options.reset1;
        var timerform,speed = 1000;

        if( document.images ){
            var parselimit = limit.split(":");
            parselimit = parselimit[0]*60+parselimit[1]*1;
        }

        $('body').click(function(){ resettime(); });
        $('body').mousemove(function(){ resettime(); });
        $('body').on('keypress', ':input', function() { resettime(); });

        begintimer = function(){

            if( !document.images )
                return;
            if( parselimit === 1 ){
                timeout();
            }else{
                parselimit -= 1;

                curmin = Math.floor(parselimit/60);
                cursec = parselimit%60;

                if( curmin<3 )
                    timeout_alert();

                if( curmin!==0 ){
                    newmsg1 = msg1.replace(/\[curmin\]/i, curmin);
                    newmsg1 = newmsg1.replace(/\[cursec\]/i, cursec);
                    curtime = newmsg1;
                }else{
                    newmsg2 = msg2.replace(/\[cursec\]/i, cursec);
                    curtime = newmsg2;
                }

                window.status = curtime;
                $('#logout_timer').html(curtime);

                setTimeout("begintimer()",1000);
            }

        };
        resettime = function(){
            today = new Date();
            startsek = today.getSeconds();
            startmin = today.getMinutes();
            starttim = today.getHours();
            starta = (startsek) + 60 * (startmin) + 3600 * (starttim);

            parselimit=limit.split(":");
            parselimit=parselimit[0]*60+parselimit[1]*1;

            if( typeof(reset1)==='function' ){
                reset1();
            }

        };

        begintimer();
    }

    win.timer1 = {
        count : count
    };


    var time_count = new timer1.count({
        msg1:'還剩 [curmin] 分 [cursec] 秒自動登出',
        msg2:'還剩 [cursec] 秒自動登出',
        timeout_alert: function() { $('.page.dimmer').addClass('active'); },
        timeout: function() { window.location.replace("./"); },
        reset1: function() { $('.page.dimmer').removeClass('active'); }
    });

    $('.hint').append('<div class="tooltip n1" style="position:absolute;left:10px;top:0;width:150px;height:40px;color:#f00;display:none;z-index:-1">\n\
        此題目為量表題型，每一題都必須填答。</div>');
    $('table.scale').mouseover(function(e){
        $('.tooltip.n1').stop();
        $('.tooltip.n1').css('top', $(this).position().top);
        $('.tooltip.n1').show().animate({left: -190}, 150, function(){
            //$(this).css('z-index', -1);
        });
    });


});

(function($) {

    $.fn.qhide = function( options ) {
        if (this.length>0){
            this.hide();
            this.find('div[id]').not('[parrent=list]').hide();
            var target = this.find(':input');
            target.attr('disabled','disabled');
            target.filter(':radio:checked').removeAttr('checked');
            target.filter(':text').val('');
            target.filter(':checkbox').each(function(i, checkbox) {
                $(checkbox).prop('checked', false).triggerHandler('click')
            });
            target.filter('select').find('option:eq(0)').attr('selected','selected');
            if (this.is('.fieldA')){
                this.parent('div').find('h2,h3,h4').css('color','#777777');
            }
        }

        return this;
    };

    $.fn.qshow = function( options ) {
        if(this.length>0){

            this.show();

            this.find(':input:visible').removeAttr('disabled');

            if(this.is('.fieldA')){
                this.parent('div').find('h2,h3,h4').css('color','');
            }

        }

        return this;
    };

})(jQuery);