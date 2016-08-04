<!DOCTYPE html>
<html xml:lang="zh-TW" lang="zh-TW">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>問卷調查平台</title>

<!--[if lt IE 9]><script src="js/html5shiv.js"></script><![endif]-->
<script src="/js/jquery-1.11.2.min.js"></script>

<link rel="stylesheet" href="/css/Semantic-UI/2.1.8/semantic.min.css" />

<style>
html * {
	font-family: 微軟正黑體 !important;
}
</style>

</head>

<body>

<div class="ui inverted vertical grey center aligned segment" style="min-height:300px">
	<h1 class="ui header" style="font-size:4em;margin-top:3em">建立調查問卷</h1>
	
	<h1 class="ui header" style="margin-bottom:3em">提供問卷設計服務，客製化問卷</h1>
</div>	

	<div class="ui container">

		<div class="ui horizontal divider">調查中問卷</div>

		<div class="ui two cards">  		

		<?php
		$docs = QuestionXML\Census::where('start_at', '<', date("Y-m-d H:i:s"))->where('close_at', '>', date("Y-m-d H:i:s"))->get();

		foreach($docs as $doc) {
		?>
  			<div class="card">
  				<div class="image"><img src="/resource/<?=$doc->dir?>/images/0.jpg" /></div>
  				<div class="content">
  					
  					<div class="header"><?=$doc->title?></div>
  					<div class="extra content">				      
						<a class="ui green button" href="<?=$doc->dir?>">開始填答</a>
					</div> 
  				</div>
  			</div>
		<?php
		}
		?>

		</div>

	</div>	

	<div class="ui text container">

		<div class="ui divider"></div>

		<div class="ui center aligned basic segment">

			<div class="ui horizontal bulleted link list">				
				<span class="item">Copyright © 2013 國立台灣師範大學 教育研究與評鑑中心</span>
				<a class="item" target="_blank" href="https://use-database.cher.ntnu.edu.tw/">聯絡我們</a>
				<a class="item" target="_blank">隱私政策</a>
				<a class="item" target="_blank">網路安全</a>
				<span class="item"><?=gethostname()?></span>
			</div>

		</div>

	</div>			

</body>
</html>