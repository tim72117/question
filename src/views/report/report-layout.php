<!DOCTYPE html>
<html xml:lang="zh-TW" lang="zh-TW">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>問題回報</title>

<!--[if lt IE 9]><script src="js/html5shiv.js"></script><![endif]-->
	
<link rel="stylesheet" href="/css/Semantic-UI/2.1.8/semantic.min.css" />
<style>
.flex-vertical {
    height: 100%;
    display: -webkit-flex;
    display:         flex;
    -webkit-flex-direction: column;
            flex-direction: column;
    -webkit-align-items: center;
            align-items: center;
    -webkit-justify-content: center;
            justify-content: center;
}
</style>
</head>
	
<body class="flex-vertical">

	<div class="ui text container">
		<div class="ui top attached segment">
			<h3 class="ui header">問題回報</h3>
		</div>
		<div class="ui bottom attached segment">
			<?=$form?>			
		</div>
		<?=$footer?>
	</div>

</body>
</html>