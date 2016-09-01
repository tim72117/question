
function checkEmpty(obj){
    if( !obj.is(':visible:not(:disabled)') ) return false;
    var value = "";
    switch(obj.attr('type')){
        case "select-one":

            var value = obj.val() !== '-9' ? obj.val() : '';
            var id = obj.parent('div').parent('div').attr('id');
            var main = $('#'+id);
            var atitle = $('#'+id).children('h2,h3,h4').text();

            if( obj.attr('filter')==="select" )
                value = "skip";

            if( value==='' ){
                $('body').scrollTop(main.offset().top-50);
                main.one('change',function(){
                    if( main.parent().is('.main') ){
                        main.parent('.main').removeClass('mark');
                    }else{
                        main.removeClass('mark');
                    }
                });
            }

        break;
        case "radio":

            var value = obj.is(':checked') ? obj.filter(':checked').val() : '';
            if(obj.parent().is('p')){
                var id = obj.parent('p').parent('div').parent('div').attr('id');
            }else{
                var id = obj.parent('td').parent('tr').parent('tbody').parent('table').parent('div').parent('div').attr('id');
            }
            var main = $('#'+id);
            var atitle = $('#'+id).children('h2,h3,h4').text();

            if( obj.parent().is('td') ){
                atitle += obj.parent().prev('td').html();
            }
            if( obj.attr('filter')==="select" )
                value = "skip";

            if( value==='' ){
                obj.filter(':enabled').eq(0).focus();
                $('body').scrollTop(main.offset().top-50);
                main.bind('click',function(){
                    if(obj.is(':checked')){
                        if( main.parent().is('.main') ){
                            main.parent('.main').removeClass('mark');
                        }else{
                            main.removeClass('mark');
                        }
                        $(this).unbind('click');
                    }
                });
            }

        break;
        case "checkbox":

            var value = obj.attr('checkOK')==='false' ? '' : '1';
            var id = obj.parent('.fieldA').parent().attr('id');
            var main = $('#'+id);
            var atitle = main.children('h2,h3,h4').text()!=='' ? $('#'+id).children('h2,h3,h4').text() : '';

            value = main.children('.fieldA').children(':checkbox').is(':checked') ? '1' : '';

            if( value==='' ){
                obj.eq(0).focus();
                $('body').scrollTop(main.offset().top-50);
                main.bind('click',function(){
                    if( $(this).children('.fieldA').children(':checkbox').is(':checked') ){
                        if( main.parent().is('.main') ){
                            main.parent('.main').removeClass('mark');
                        }else{
                            main.removeClass('mark');
                        }
                        $(this).unbind('click');
                    }
                });
            }

        break;
        case "text":

            var value = obj.val();
            if( obj.parent().is('p') || obj.parent().is('span') ){
                var id = obj.parent('p,span').parent('div').attr('id');
            }else{
                var id = obj.parent('div').parent('div').parent('div.fieldA').parent('div').attr('id');
            }
            var atitle = $('#'+id).children('h2,h3,h4').text();
            if( obj.attr('filter')==='skip' )
                value = "skip";
            var main = $('#'+id);

            if( value==='' ){
                obj.focus();
                $('body').scrollTop(main.offset().top-50);
                obj.one('change',function(){
                    if( main.parent().is('.main') ){
                        main.parent('.main').removeClass('mark');
                    }else{
                        main.removeClass('mark');
                    }
                });
            }else if( value.Blength()>obj.attr('textsize') ){
                obj.focus();
                $('body').scrollTop(main.offset().top-50);
                obj.one('change',function(){
                    if( main.parent().is('.main') ){
                        main.parent('.main').removeClass('mark');
                    }else{
                        main.removeClass('mark');
                    }
                });
                alert("不能超過"+(obj.attr('textsize')/2)+"個中文字");
                value = '';
            }

        break;
        case "textarea":
            value = "skip";
        break;
    }


    if( value==='' && typeof(id)!=='undefined' ){
        var main = $('#'+id);
        if( main.parent().is('.main') ){
            main.parent('.main').addClass('mark');
        }else{
            main.addClass('mark');
        }
    }
    return (value==='');

}

String.prototype.Blength = function() {
    var arr = this.match(/[^\x00-\xff]/ig);
    return  arr === null ? this.length : this.length + arr.length;
};


var checkAnswer = function(){
    this.error_array = Array();
    this.addError = function(e){this.error_array.push(e);};
};





