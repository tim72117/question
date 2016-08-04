<?=View::make('ques.data.'.$doc->dir.'.head')?>
<div class="ui segment" style="width:800px;margin:0 auto">	
	<div class="ui basic segment" style="height:300px;margin:0 auto;text-align:center">
		<h4>目前停止調查</h4>
		<p>調查時間：<?=date("Y-m-d h:i",strtotime($doc->start_at))?>~<?=date("Y-m-d h:i",strtotime($doc->close_at))?></p>
	</div>
	<?=View::make('ques.data.'.$doc->dir.'.footer')?>
</div>