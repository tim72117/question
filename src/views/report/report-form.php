<?=Form::open(array('url' => Request::url(), 'method' => 'post', 'class' => 'ui form ' . ($errors->isEmpty() ? '' : ' error')))?>

    <input type="hidden" name="_token2" value="<?=dddos_token()?>" />

    <h4 class="ui header">如您在填寫時有任何問題，請在下方留下您的聯絡電話及需要協助的問題，我們會盡快與您聯繫。</h4>

    <div class="field">
        <label>聯絡方法(請輸入您的聯絡方式，如手機號碼或EMail) : </label>
        <?=Form::text('contact', Input::get('contact'), array(
            'placeholder' => '請勿超過50個字',
        ))?>
    </div>

    <div class="field">
        <label>問題內容 : </label>
            <?=Form::textarea('report', Input::get('report'), array(
                'placeholder' => '請勿超過500個中文字',
                'style' => 'resize: none'
            ))?>
    </div>

    <div class="ui error message">
        <p><?=implode('、', array_filter($errors->all()))?></p>
    </div>

    <div class="ui positive button" onclick="document.forms[0].submit()">送出</div>

<?=Form::close()?>