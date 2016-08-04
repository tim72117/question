<?php

function buildQuestionEvent_check($question_array) {

    $ret_text = '';

    $question_checkbox = $question_array->xpath("//type[.='checkbox']/parent::*");
    if (is_array($question_checkbox))
    foreach($question_checkbox as $key => $question){

        $ret_text .= "var cgid = $('#".$question->id.":visible>div.fieldA>div>p>:checkbox');";
        $ret_text .= "if(cgid.length>0)";
        $ret_text .= "if(!cgid.is(':checked')){";
        $ret_text .= "cgid.attr('checkOK','false');";
        $ret_text .= "}else{";
        $ret_text .= "cgid.attr('checkOK','true');";
        $ret_text .= "};";
    }

    return $ret_text;
}