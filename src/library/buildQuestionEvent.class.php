<?php
namespace app\library\v10;

class buildQuestionEvent {

    static function buildEvent($question_array)
    {
        $jqueryEventType = array("radio"=>"click","select"=>"change");

        $jsObj_script = "";

        $questionHasSub_array = $question_array->xpath("//type[.='radio' or .='select']/parent::*/answer/item[@sub]/parent::*/parent::*");


        if(is_array($questionHasSub_array))
        foreach($questionHasSub_array as $question){


            $item_array = $question->xpath("answer/item[@sub]");
            if(is_array($item_array)){

            $jsObjShow_script = "";
            $inSubRootAll_array = array();
            $inSubSubAll_array = array();

            foreach($item_array  as $item){
                $itemAttr = $item->attributes();
                $inSubRoot_array = array();
                $inSubSub_array = array();
                foreach(explode(",",$itemAttr["sub"]) as $key => $itemSub){
                    $questionInSub = $question_array->xpath("//id[.='".$itemSub."']/parent::*");
                    if(is_array($questionInSub))
                    switch($questionInSub[0]->getName()){
                    case "question_sub":
                    if(!in_array("#".$itemSub,$inSubSub_array)){array_push($inSubSub_array,"#".$itemSub);}
                    if(!in_array("#".$itemSub,$inSubSubAll_array)){array_push($inSubSubAll_array,"#".$itemSub);}
                    break;
                    case "question":
                    if(!in_array("#".$itemSub,$inSubRoot_array)){array_push($inSubRoot_array,"#".$itemSub);}
                    if(!in_array("#".$itemSub,$inSubRootAll_array)){array_push($inSubRootAll_array,"#".$itemSub);}
                    break;
                    }
                }

                $jsObjShow_script .= "case '".$itemAttr->value."':";
                if(count($inSubRoot_array)>0){
                    $jsObjShow_script .= "\$(allSub_root).not('".implode(",",$inSubRoot_array)."').find('div.fieldA').qhide();";
                    $jsObjShow_script .= "\$('".implode(",",$inSubRoot_array)."').find('div.fieldA').qshow();";
                }
                if(count($inSubSub_array)>0){
                    $jsObjShow_script .= "\$(allSub_sub).not('".implode(",",$inSubSub_array)."').qhide();";
                    $jsObjShow_script .= "\$('".implode(",",$inSubSub_array)."').qshow();";
                }
                $jsObjShow_script .= "break;";

            }

            $jsObj_script .= "\$(':input[name=".$question->answer->name."]').".$jqueryEventType[(string)$question->type]."(function(){";
            $jsObj_script .= "var allSub_root = '".implode(",",$inSubRootAll_array)."';";
            $jsObj_script .= "var allSub_sub = '".implode(",",$inSubSubAll_array)."';";
            $jsObj_script .= "switch(\$(this).val()){";
            $jsObj_script .= $jsObjShow_script;
            $jsObj_script .= "default:";
            if(implode(",",$inSubSubAll_array)!=""){
                $jsObj_script .= "\$(allSub_sub).qhide();";
            }
            if(implode(",",$inSubRootAll_array)!=""){
                $jsObj_script .= "\$(allSub_root).find('div.fieldA').qhide('slow');";
            }
            $jsObj_script .= "break;";
            $jsObj_script .= "}";
            $jsObj_script .= "});";

            }

        }


        $questionHasSub_array = $question_array->xpath("//answer/item[@skip]/parent::*/parent::*");

        if(is_array($questionHasSub_array))
        foreach($questionHasSub_array as $question){


            $item_array = $question->xpath("answer/item[@skip]");
            $jsObjShow_script = "";

            $temp_array = array();
            $inSubSkipAll_array = array();
            foreach($item_array  as $item){array_push($temp_array,$item->attributes()->skip[0]);}
            foreach(explode(",",implode(",",$temp_array)) as $itemSub){
                //if(!in_array("#".$itemSub,$inSubSkipAll_array)){array_push($inSubSkipAll_array,"#".$itemSub);}
            }


            foreach($item_array  as $item){

                $itemAttr = $item->attributes();
                $inSubSkip_array = array();
                foreach(explode(",",$itemAttr["skip"]) as $key => $itemSub){
                    if($itemSub!=""){
                        if(!in_array("#".$itemSub,$inSubSkip_array)){array_push($inSubSkip_array,"#".$itemSub);}
                        if(!in_array("#".$itemSub,$inSubSkipAll_array)){array_push($inSubSkipAll_array,"#".$itemSub);}
                    }
                }


                $jsObjShow_script .= "case '".$itemAttr->value."':";
                if(count($inSubSkip_array)>0){
                    $jsObjShow_script .= "\$(allSub).not('".implode(",",$inSubSkip_array)."').find('div.fieldA').qshow();";
                    $jsObjShow_script .= "\$('".implode(",",$inSubSkip_array)."').find('div.fieldA').qhide();";
                }
                $jsObjShow_script .= "break;";

                if((string)$question->type=="checkbox"){
                    $jsObj_script .= "\$('input[name=".$itemAttr['name']."]').click(function(){";
                    $jsObj_script .= "if( \$(this).is(':checked') ){";
                    $jsObj_script .= "\$('".implode(",",$inSubSkip_array)."').find('div.fieldA').qhide();";
                    $jsObj_script .= "}else{";
                    $jsObj_script .= "\$('".implode(",",$inSubSkip_array)."').find('div.fieldA').qshow();";
                    $jsObj_script .= "}";
                    $jsObj_script .= "});\n";
                }
            }

            if((string)$question->type!="checkbox"){
                $jsObj_script .= "\$(':input[name=".$question->answer->name."]').".$jqueryEventType[(string)$question->type]."(function(){";
                $jsObj_script .= "var allSub = '".implode(",",$inSubSkipAll_array)."';";

                $jsObj_script .= "switch(\$(this).val()){";

                $jsObj_script .= $jsObjShow_script;
                $jsObj_script .= "default:";
                if(implode(",",$inSubSkipAll_array)!=""){
                    $jsObj_script .= "\$(allSub).find('div.fieldA:not([init=disabled])').qshow();";
                }
                $jsObj_script .= "break;";
                $jsObj_script .= "}";
                $jsObj_script .= "});\n";
            }
        }


        //------------------------------------------select uplv_target----------------------------------------
        $item_uplv_target_array = $question_array->xpath("//type[.='select'][@uplv_target]/parent::*");
        if(is_array($item_uplv_target_array))
        foreach($item_uplv_target_array as $item){
            $itemAttr = $item->type->attributes();
            $uplv_name = (string)$item->answer->name;
            $target_name = $itemAttr['uplv_target'];

            $jsObj_script .= "\$('select[name=$uplv_name]').change(function(){";
            $jsObj_script .= "if(\$(this).val()!=-1){";
            $jsObj_script .= "\$('select[name=$target_name] option[uplv!=n][value!=-1]').attr('disabled','disabled');";
            $jsObj_script .= "\$('select[name=$target_name] option[uplv='+\$(this).val()+']').removeAttr('disabled');";
            $jsObj_script .= "\$('select[name=$target_name]').find('option:eq(0)').attr('selected','selected');";
            $jsObj_script .= "\$('select[name=$target_name]').triggerHandler('change');";
            $jsObj_script .= "};";
            $jsObj_script .= "});";
        }
        //-------------------------------------------------------------------------------------------------
        //select uplv_target

        //------------------------------------------checkbox reset----------------------------------------
        $checkbox_group = array();
        $answer_limit_array = $question_array->xpath("//type[.='checkbox']/parent::*/answer[@limit]/parent::*");

        if(is_array($answer_limit_array))
        foreach($answer_limit_array as $answer_limit){

            $answer_limitAttr = $answer_limit->answer->attributes();
            $answer_limit_amount = $answer_limitAttr['limit'];

            $id = (string)$answer_limit->id;

            if( !isset($checkbox_group[$id]) ) $checkbox_group[$id] = array();
            $checkbox_group[$id]['limit'] = $answer_limit_amount;


            /*
            $jsObj_script .= "\$('#".(string)$answer_limit->id."').children('.fieldA').children('div').children('p').children('input').click(function(){";
            $jsObj_script .= "if(\$('#".(string)$answer_limit->id."').children('.fieldA').children('div').children('p').children('input:checked').length > ".$answer_limit_amount."){";
            $jsObj_script .= "alert('請勿選超過".$answer_limit_amount."個');";
            $jsObj_script .= "\$(this).prop('checked',false).triggerHandler('click');createCheckboxEvent(1,1);";
            $jsObj_script .= "}";
            $jsObj_script .= "});";
            */

        }





        $item_reset_array = $question_array->xpath("//type[.='checkbox']/parent::*/answer/item[@reset]/parent::*/parent::*");


        if(is_array($item_reset_array))
        foreach($item_reset_array as $item_reset_q){

            $id = (string)$item_reset_q->id;

            $reset_name_array = array();
            foreach( $item_reset_q->answer->item as $item_rest ){
                $item_restAttr = $item_rest->attributes();
                if( isset($item_restAttr['reset']) ){
                    $reset_name = 'input[name='.$item_restAttr['name'].']';
                    array_push($reset_name_array,$reset_name);
                }
            }

            if( !isset($checkbox_group[$id]) ) $checkbox_group[$id] = array();

            $checkbox_group[$id]['reset'] = implode(',',$reset_name_array);
            if( !isset($checkbox_group[$id]['limit']) ) $checkbox_group[$id]['limit'] = 0;


        }

        foreach($checkbox_group as $qid => $checkbox){
            $reset = isset($checkbox['reset'])?$checkbox['reset']:null;
            $limit = isset($checkbox['limit'])?$checkbox['limit']:0;
            $jsObj_script .= "createCheckboxEvent('".$qid."','".$reset."',".$limit.");";
        }

        $jsObj_script .= "function createCheckboxEvent(id,reset,limit){";
        $jsObj_script .= "\$('#'+id).children('.fieldA').children('div').children('p').children('input').click(function(){";

        $jsObj_script .= "if(reset!=null)";
        $jsObj_script .= "if(\$(this).is(':checked'))";
        $jsObj_script .= "if(\$(this).is(reset)){";
        $jsObj_script .= "\$(this).parent('p').parent('div').siblings().children('p').children('input:checked').each(function(){\$(this).prop('checked',false).triggerHandler('click');});";
        $jsObj_script .= "}else{";
        $jsObj_script .= "\$(reset).filter(':checked').prop('checked',false).triggerHandler('click');";
        $jsObj_script .= "};";

        $jsObj_script .= "if(limit!=0)";
        $jsObj_script .= "if(\$('#'+id).children('.fieldA').children('div').children('p').children('input:checked').length > limit){";
        $jsObj_script .= "alert('請勿選超過'+limit+'個');";
        $jsObj_script .= "\$(this).prop('checked',false).triggerHandler('click');createCheckboxEvent(1,1);";
        $jsObj_script .= "}";


        $jsObj_script .= "});";
        $jsObj_script .= "};";


        //-------------------------------------------------------------------------------------------------

        //checkbox sub
        $jsObj_script .= "\$(':checkbox[sub]').click(function(){";
        $jsObj_script .= "  var sub = \$(this).attr('sub');";
        $jsObj_script .= "  if( \$(this).is(':checked') ){";
        $jsObj_script .= "      \$(sub).qshow();";
        $jsObj_script .= "  }else{      ";
        $jsObj_script .= "      \$(sub).children('div').find('input').removeAttr('checked').each(function(){\$(this).triggerHandler('click');});";
        $jsObj_script .= "      \$(sub).qhide();";
        $jsObj_script .= "  }";
        $jsObj_script .= "}); ";
        //-------------------------------------------------------------------------------------------------



        return $jsObj_script;
    }

}