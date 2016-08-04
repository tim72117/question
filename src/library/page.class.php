<?php
namespace app\library;

use DB, Cache;

class page {

    public $page = '';
    public $node = '';
    public $question_array = '';
    public $root = './';
    public $is_show_all_question = true;
    public $option = NULL;
    public $question_html = '';
    public $name_array = [];
    public $hide = [];

    function init($option)
    {
        $this->option = $option;
        $this->randomQuesRoot = false;
        $this->randomQuesSub = false;
        $this->isShunt = false;
        $pagetree = true;

        $_SESSION['voice'] = false;
        $_SESSION['randomQuesRoot'] = false;
        $_SESSION['randomQuesScale'] = false;
        $_SESSION['randomQuesScaleControlSessionName'] = 'randomQuesScaleControl';
    }

    function loadxml($census, $page)
    {
        $this->page = $page;

        $doc = $census->pages()->where('page', $page)->remember(5)->first();

        $this->question_array = simplexml_load_string($doc->xml);
    }

    function bulidQuestion($num)
    {
        $buildQuestion = 'app\\library\\'.$this->option['buildQuestion'].'\\buildQuestion';
        $buildQuestion::$hide = $this->hide;

        $question_amount = count($this->question_array->question);

        $isfixedQArray = array();
        $nofixedQArray = array();

        for($qi=0;$qi<$question_amount;$qi++){

            $qAttr = $this->question_array->question[$qi]->attributes();
            if( isset($qAttr['fixed']) ){
                array_push($isfixedQArray,$qi);
            }else{
                array_push($nofixedQArray,$qi);
            }
        }

        if( isset($this->isShunt) && $this->isShunt!='' ){
            $_SESSION['isShuntArray'] = explode(',',$this->isShunt);
        }

        if( $this->randomQuesRoot ){
            shuffle($nofixedQArray);
        }

        $count_nofixedQ_i = 0;

        $start = 0;
        $amount = $num==0 ? $question_amount : $num;

        for($i=$start;$i<$amount;$i++){

            if( in_array($i,$isfixedQArray) || true ){//test
                $randQi = $i;
            }else{

                $randQi = $nofixedQArray[$count_nofixedQ_i];
                $count_nofixedQ_i++;
            }

            $question = $this->question_array->question[$randQi];

            if($question->getName()=="question"){
                $this->question_html .= $buildQuestion::build($question,$this->question_array,0,"no");
            }

        }
        $this->name_array = $buildQuestion::$name;
    }

    function buildQuestionEvent()
    {
        $buildQuestionEvent = 'app\\library\\'.$this->option['buildQuestion'].'\\buildQuestionEvent';
        $javascript = $buildQuestionEvent::buildEvent($this->question_array);
        //-------------------------------------------------------------------載入額外事件JS開始
        $jsfile = 'page_n'.$this->page.'.js';
        if( file_exists($this->root.'/'.$jsfile) ){
             $javascript .= file_get_contents($this->root.'/'.$jsfile, FILE_USE_INCLUDE_PATH);
        }
        //-------------------------------------------------------------------載入額外事件JS

        return $javascript;
    }

    function buildQuestionEvent_check()
    {
        $javascript = '';
        //-------------------------------------------------------------------載入額外事件JS開始
        $jsfile = 'page_n'.$this->page.'_check.js';
        if( file_exists($this->root.'/'.$jsfile) ){
             $javascript .= file_get_contents($this->root.'/'.$jsfile, FILE_USE_INCLUDE_PATH);
        }
        //-------------------------------------------------------------------載入額外事件JS

        return $javascript;
    }

}
