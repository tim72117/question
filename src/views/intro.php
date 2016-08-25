<!DOCTYPE html>
<html xml:lang="zh-TW" lang="zh-TW">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title><?=$doc->title?></title>

<!--[if lt IE 9]><script src="js/html5shiv.js"></script><![endif]-->
<script src="/js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="//ssllogo.twca.com.tw/twcaseal_v3.js" charset="utf-8"></script>

<link rel="stylesheet" href="/css/Semantic-UI/2.1.8/semantic.min.css" />

</head>

<body>
	<?=$child_head?>
	<div class="ui segment" style="width:800px;margin:0 auto">
			
		<div class="ui basic segment" style="width:500px;margin:0 auto">
	        <?=Form::open(array('url' => '/ques/' . $doc->dir . '/qlogin', 'method' => 'post', 'class' => 'ui form attached segment' . ($errors->isEmpty() ? '' : ' error')))?>
				<input type="hidden" name="_token2" value="<?=dddos_token()?>" />
				<?=$child_body?>
			<?=Form::close()?>
			<div class="ui bottom attached warning message">
			    <i class="icon help"></i>
			    <?=link_to('/ques/' . $doc->dir . '/share/chrome', '填答時有遇到問題嗎?', ['target'=>'_blank'])?>
			    <br />
			    <i class="icon help"></i>
			    <?=link_to('/ques/' . $doc->id . '/report', '需要協助嗎?', ['target'=>'_blank'])?>
			</div>
		    <div class="ui basic segment">
		    	<div id="twcaseal" class="SMALL"><img src="/images/twca.gif" /></div>
		    </div>
		</div>
		
		<?=$child_footer?>
	</div>
</body>
</html>