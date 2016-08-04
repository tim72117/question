<!DOCTYPE html>
<html xml:lang="zh-TW" lang="zh-TW">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title><?=$doc->title?></title>

<!--[if lt IE 9]><script src="/js/html5shiv.js"></script><![endif]-->
<script src="/js/jquery/jquery-1.11.2.min.js"></script>
<script src="/js/timer_v5.js"></script>
<script src="/js/qcheck_v4.js"></script>

<link rel="stylesheet" href="/packages/vendor/package/css/page_struct.css" />
<link rel="stylesheet" href="/resource/<?=$doc->dir?>/banner.css" />
<link rel="stylesheet" href="/css/Semantic-UI/2.1.8/components/dimmer.min.css" />
<link rel="stylesheet" href="/css/Semantic-UI/2.1.8/components/icon.min.css" />

<script>

var percent = '<?=$percent?>';
var isCheck = false;

$(document).ready(function(){
	
	$('form[name=form1]').submit(function(){
		if(!isCheck){
			return false;
		}
	});

	<?=$questionEvent?>

	$('form').find(':radio:checked,:checkbox:checked').each(function(){$(this).triggerHandler('click');});
	$('form').find('select option:first-child:not(:selected)').each(function(){$(this).parent('select').triggerHandler('change');});
	$('#checkForm').prop('disabled', false);	

    //送出檢誤
    $('#checkForm').click(function(){

        $('#checkForm').prop('disabled', true);

        var fillnull = [];
        var checkOK = true;

        var testarray = {};
        var qcheck = $(':input.qcheck');

        qcheck.each(function(){	
            var name = $(this).attr('name');

            if( !testarray.hasOwnProperty(name) ){

                var obj = $(':input[name='+name+']');

                if( <?=(($isPhone?'false':'true') . '&&')?> checkEmpty(obj) ){
                    checkOK = false;
                    return false;		
                }

                if( obj.filter(':disabled,:hidden').length==obj.length )
                if( obj.is(':disabled,:hidden') )
                    fillnull.push(name);

                testarray[name] = name;
            }
        });	

        if(!checkOK){
            $('#checkForm').prop('disabled', false);
            return false;
        }else{
            $('#checkForm').prop('disabled', false);
            <?=$questionEvent_check?>
            $('input[name=check_atuo_text]').val(fillnull);
            isCheck = true;
            $('#checkForm').prop('disabled', true);
        }

        $('form[name=form1]').submit();

    });
});
</script>
</head>
<body>
    <div class="ui page dimmer">
        <div class="content">
            <h1 class="ui header" id="logout_timer"></h1>
        </div>
    </div>
<div id="building">
	<div class="hint" style="position:relative">
<!--		<div id="tooltip" style="position:absolute;left:10px;top:0;width:150px;height:80px;color:#000;z-index:-1"></div>-->
	</div>
	<div id="header" class="banner<?=$page?>"></div>
	<div id="contents">
		
		<form action="write" method="post" name="form1">
			<input type="hidden" name="check_atuo_text" value="" />
			<input type="hidden" name="page" value="<?=$page?>" />
			<input type="hidden" name="stime" value="<?=date("Y/n/d H:i:s")?>" />
			<input type="hidden" name="_token" value="<?=csrf_token()?>" />

			<div class="readme"></div>
			<?=$question?>
        </form>
        <div id="submit" style="margin:0 auto; text-align:center">
            <button id="checkForm" disabled="disabled" class="button-green" style="width:100px;height:40px;margin:10px 0 0 0;padding:10px;text-align: center;font-size:15px;color:#fff">下一頁</button>
        </div>
		<div id="init_value" style="display: none"><?=$init_value?></div>
		
	</div>
	<footer><?=$child_footer?></footer>
</div>
</body>
</html>