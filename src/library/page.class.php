<?php
namespace app\library;

use DB;
use Cache;
use app\library\v10\buildQuestion;
use app\library\v10\buildQuestionEvent;

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
        $pagetree = true;
    }

    function loadxml($census, $page)
    {
        $this->page = $page;

        $doc = $census->pages()->where('page', $page)->remember(5)->first();

        $this->question_array = simplexml_load_string($doc->xml);
    }

    function bulidQuestion()
    {
        buildQuestion::$hide = $this->hide;
        foreach ($this->question_array as $question) {
            if($question->getName()=="question"){
                $this->question_html .= buildQuestion::build($question, $this->question_array, 0, (object)['type' => 'no']);
            }
        }

        $this->name_array = buildQuestion::$name;
    }

    function buildQuestionEvent()
    {
        $javascript = buildQuestionEvent::buildEvent($this->question_array);
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
