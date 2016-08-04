<?php
namespace app\library\v10;

use Form, Input, DB, Session;

class buildQuestionShow {

    static $name = [];
    static $label_count = 0;
    static $data = NULL;

    static function getName()
    {
        return self::$name;
    }

    static function getData($tablename)
    {
        self::$data = DB::table($tablename)->where('newcid',Session::get('newcid_show'))->select('*')->first();
        if( is_null(self::$data) )
            self::$data = (object)DB::table('sys.columns')->whereRaw("object_id=OBJECT_ID('dbo.".$tablename."')")->select('name',DB::raw("'' AS value"))->lists('value','name');
    }

    static function build($question,$question_array,$layer,$parrent)
    {
        $html = '';

        $is_hint = false;
        $is_disabled_skipfrom = false;

        $hn_style = '';
        $fieldA_display_style = '';
        $div_init = '';

        $classMap_div = array(0=>"ans",1=>"sub1_ans",2=>"sub2_ans",3=>"sub2_ans",4=>"sub2_ans");
        $class = $classMap_div[$layer];


        $questionAttr = $question->attributes();


        $is_disabled = !($layer==0||($layer>=1&&$parrent=='list'))||$is_disabled_skipfrom;
        //$display_style = ($layer==0||$parrent=='list')?'':' display:none';
        $display_style = '';


        if( $question->getName()=="question" && $question->type=='explain' ){
            $html .= '<div class="main_explain">';
        }elseif( $question->getName()=="question" ){
            $html .= '<div class="main" style="'.$display_style.'">';
        }

        if( $question->type != 'explain' && $parrent != 'list' ){
            $html .= '<div id="'.$question->id.'" parrent="'.$parrent.'" class="layer'.$layer.'" style="'.$display_style.'">';
        }elseif( $parrent == 'list' ){
            $html .= '<div id="'.$question->id.'" parrent="'.$parrent.'" class="layer0" style="'.$display_style.'">';
        }else{
            $html .= '<div id="'.$question->id.'" parrent="'.$parrent.'" style="'.$display_style.'">';
            $fieldA_display_style = ' display:none';
        }


        $title = '';
        if( (string)$question->title!='' )
            $title .= (string)$question->title;



        if( $question->type=='explain' ){
            $html .= '<div class="readme" style="background-color:transparent"><b>'.(string)$question->title.'</b></div>';
        }else{

            if( (string)$question->title!='' ){
                if($layer==0){
                    $title_string = '<strong>'.(string)$question->idlab.'</strong>'.$title.($fieldA_display_style==' display:none'?'<p style="font-size:10px;color:red">此題無須填答</p>':'');
                }else{
                    if( (string)$question->idlab=='' ){
                        $title_string = ''.$title;
                    }else{
                        $title_string = '<strong>'.$question->idlab.'</strong>'.$title;
                    }
                }


                $html .= '<h4 class="title" style="'.$hn_style.'">'.$title_string.'</h4>';


            }else{
                $title_string = '';
                if( (string)$question->idlab!='' )
                    $title_string = ($question->idlab.' ').$title;
                $html .= '<h4 class="title" style="'.$hn_style.'">'.$title_string.'</h4>';


            }
        }

        $html .= '<div class="fieldA '.$class.'" init="'.$div_init.'" style="'.$fieldA_display_style.';overflow : auto;position:static">';

        $option = "";
        $table = '';
        $sub_array_all = array();

        switch($question->type){
            case "radio":
            case "select":
                $name = (string)$question->answer->name;
                $html .= '<span style="color:red">'.self::$data->$name.'</span>';
            break;
        }

        $item_count = 1;
        $tableHead = '';
        foreach($question->answer->item as $answer){
            $attr = $answer->attributes();
            switch($question->type){
            //------------------------------------------------explain
            case "explain":
                $html .= '<div class="readme"><h3><b>'.(string)$question->title.'</b></h3></div>';
            break;
                //------------------------------------------------radio
            case "radio":


                $html .= '<p class="radio">';

                if( $is_hint && $attr['ruletip']!='' )
                    $html .= '<span class="small-purple">'.$attr['ruletip']."</span>";
                foreach($answer->text as $text){
                    $html .= '<span style="font-size:.8em;color:#666666">'.$text.'</span>';
                }
                $html .= '</p>';

                $item_count++;

            break;
                //------------------------------------------------select
            case "select":

                if($attr["type"]=="list"){
                    $list = file_get_contents("question/".$attr["value"]);
                    $option .= $list;

                }else{
                    $answerAttr = $question->answer->attributes();

                    if( $answerAttr['others']!='' ){
                        $otherArray = explode(',',$answerAttr['others']);
                        $otherArray_fix = array();
                        foreach($otherArray as $other){
                            if($attr[$other]!='')
                            array_push($otherArray_fix,$other.'="'.$attr[$other].'"');
                        }
                        $other_text = implode(' ',$otherArray_fix);

                        $option .= '<option value="'.$attr["value"].'" '.(count($question->answer->xmlfile)>0?'orgin="true"':'').' '.$other_text.'>'.$item_count.' '.(string)$answer.'</option>';
                    }else{
                        $option .= '<option value="'.$attr["value"].'" '.(count($question->answer->xmlfile)>0?'orgin="true"':'').'>'.(string)$answer.'</option>';
                    }

                }
                $item_count++;



            break;
            //------------------------------------------------text
            case "text":
                $name = $attr["name"];

                $html .= '<div style="display:table-row">';
                $html .= '<div style="display:table-cell;line-height:30px">'.(string)$answer.'</div>';
                $html .= '<div style="display:table-cell;line-height:30px">';
                $html .= '<span style="color:red">'.self::$data->$name.'</span>';
                $html .= '</div>';
                $html .= '</div>';

            break;
            case "text_phone":
                $html .= (string)$answer.'<input type="text" class="qcheck" name="'.$attr["name"];
                $html .= '" value="" maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" size="'.$attr["size"].'" filter="'.$attr["filter"].'" />';
            break;
            //------------------------------------------------textarea
            case "textarea":
                $name = $question->answer->name;
                $html .= '<p>';
                $html .= '<span style="color:red">'.self::$data->$name.'</span>';
                $html .= '</p>';

            break;
            //------------------------------------------------checkbox
            case "checkbox":

                $subs_array = NULL;
                if( $attr['sub']!='' )
                    $subs_array = array_map( create_function('$id', 'return "#".$id;'),explode(",",$attr['sub']) );
                $subs_string = '';
                if( is_array($subs_array) )
                    $subs_string = ' sub="'.implode(",",$subs_array).'"';

                $checkbox_style = $question->type->attributes();
                $name = $attr["name"];

                if( $checkbox_style['cstyle']!='2' ){

                    if( count($question->answer->item)>10 ){
                        $html .= '<div style="line-height:1.8em" class="checkbox horizontal">';
                    }else{
                        $html .= '<div style="line-height:1.8em" class="checkbox horizontal less">';
                    }
                    $html .= '<p class="checkbox" style="display: inline-block;width:100%">';
                    $html .= '<span style="color:red">'.self::$data->$name.'</span>';
                    $html .= '<label for="label_'.$question->id.'_'.$item_count.'" class="checkbox">'.(string)$answer;

                    $html .= '</label>';

                    $html .= '</p>';

                    $sub_array = explode(",", $attr["sub"]);
                    foreach($sub_array as $attr_i){
                        $sub = $question_array->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                        if( isset($sub[0]) )
                            $html .= self::buildQuestion_simp($sub[0],$question_array,$layer+1,(string)$question->type);
                    }
                    $html .= '</div>';

                }elseif( $checkbox_style['cstyle']=='2' ){

                    $html .= '<div style="line-height:1.8em">';
                    $html .= '<p style="margin:0">';
                    $html .= '<span style="color:red">'.self::$data->$name.'</span>';
                    $html .= '<label style="cursor:pointer" for="label_'.$question->id.'_'.$item_count.'" class="checkbox">'.(string)$answer.'</label></p>';

                    $sub_array = explode(",", $attr["sub"]);
                    foreach($sub_array as $attr_i){
                        $sub = $question_array->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                        if( isset($sub[0]) )
                            $html .= self::buildQuestion_simp($sub[0],$question_array,$layer+1,(string)$question->type);
                    }
                    $html .= '</div>';

                }
                $item_count++;

                if( !array_key_exists((string)$attr["name"],self::$name) )
                    self::$name[(string)$attr["name"]] = array('type'=>'checkbox','layer'=>$layer);

            break;
            //------------------------------------------------extra
            case "extra":
                $html .= '<p style="line-height:1.8em">';
                $subs_array = NULL;
                if($attr['sub']!='')
                    $subs_array = array_map( create_function('$id', 'return "#".$id;'),explode(",",$attr["sub"]) );
                $subs_string = '';
                if(is_array($subs_array))
                    $subs_string = 'sub="'.implode(",",$subs_array).'"';
                $html .= '<div name="'.$attr["name"].'"></div></p>';
            break;
            //------------------------------------------------scale
            case "scale":

                $question_item = $question->answer->item[$item_count-1];

                $scale_style = $question->type->attributes();


                $attr = $question_item->attributes();
                $scale_title = (string)$question_item;
                $name = $attr['name'];
                $table .= '<tr>';
                $table .= '<td class="scale" width="5px"><p class="scale">('.$item_count.')</p></td>';
                $table .= '<td class="scale"><p class="scale">'.$scale_title.'</p></td>';
                $table .= '<td class="scale"><span style="color:red">'.self::$data->$name.'</span></td>';
                $degree_key = 0;
                $table .= "</tr>";

                 $item_count++;

            break;
            case "scale_text":
                $table .= '<tr>';
                $table .= '<td class="scale"><p class="scale" style="margin-left:1em;text-indent:-1em">'.(string)$answer.'</p></td>';
                foreach($question->answer->degree as $degree){
                  $attr_degree = $degree->attributes();
                  $table .= '<td class="scale"><input type="text" class="qcheck" size="10" name="'.$attr['name'].'" value="" /></td>';
                }
                $table .= "</tr>";
            break;
            case "list":
                $html .= "<p>".(string)$answer."</p>";

                $sub_array = explode(",", $attr["sub"]);
                foreach($sub_array as $attr_i){
                    $sub = $question_array->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                    if($sub[0])
                      $html .= self::build($sub[0],$question_array,$layer+1,"list");
                }
            break;
            }

            if( $attr["sub"] && $question->type!="select" && $question->type!="list" && $question->type!="checkbox" ){
                $sub_array = explode(",", $attr["sub"]);
                foreach($sub_array as $attr_i){
                    $sub = $question_array->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                    if($sub[0])
                        $html .= self::build($sub[0],$question_array,$layer+1,(string)$question->type);
                }
            }elseif( $attr["sub"] && $question->type=="select" ){
                $sub_array = explode(",", $attr["sub"]);
                foreach($sub_array as $attr_i){
                    $sub = $question_array->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                    if($sub[0])
                        array_push($sub_array_all,$sub[0]);
                }
            }

        }


        if( $question->type=='select' ){


            if($attr["type"]=="range"){
                $html .= '<span class="select_text">'.(string)$question->answer->text.'</span>';
            }
            if( count($sub_array_all)>0 ){
                foreach($sub_array_all as $sub){
                    $html .= self::build($sub,$question_array,$layer+1,"select");
                }
            }
        }


        if( $question->type=="scale" )
        if( $scale_style['sstyle']=='2' ){

            $html .= '<table class="scale" cellspacing="0"><tbody>'.$table.'</tbody></table>';

        }else{

            $tableHead = '';
            $amount_degree = count($question->answer->degree);
            if($amount_degree>5){$scale_width = 25;}elseif($amount_degree>4){$scale_width = 35;}else{$scale_width = 50;}
            foreach($question->answer->degree as $degree){
                $attr_degree = $degree->attributes();
                if( isset($attr_degree['width']) ) $scale_width = $attr_degree['width'];
                $tableHead .= '<th style="text-align:center;font-size:.8em;width:'.$scale_width.'px"><b>'.(string)$degree.'</b></th>';
            }

            $html .= '<table class="scale" cellspacing="0"><tbody>'.$table.'</tbody></table>';

        }


        if( $question->type=="scale_text" ){
            foreach($question->answer->degree as $degree){
                $attr_degree = $degree->attributes();
                $tableHead .= '<th style="font-size:0.8em;width:'.$attr_degree["width"].'"><b>'.(string)$degree.'</b></th>';
            }
            $html .= '<table class="scale" cellspacing="0"><thead><tr><th></th>'.$tableHead.'</tr></thead><tbody>'.$table.'</tbody></table>';
        }




        $html .= '</div>';


        if( $question->type!='explain' && $layer==0 )
            $html .= '<p class="contribute"></p>';

        $html .= '</div>';


        if( $question->getName()=="question" )
            $html .= '</div>';

        return $html;
    }

    static function buildQuestion_simp($question,$question_array,$layer,$parrent)
    {
        $is_disabled = false;
        $html = '';
        switch($question->type){
        case "text":
            $html .= '<div id="'.$question->id.'" style="text-indent:20px">';
            $html .= '<p style="margin:2px">'.(string)$question->title.'</p>';
            foreach($question->answer->item as $answer){
                $attr = $answer->attributes();
                $name = (string)$attr["name"];
                $html .= '<p style="margin:2px">'.(string)$answer;
                $html .= '<span style="color:red">'.self::$data->$name.'</span>';
                $html .= '</p>';
            }
            $html .= '</div>';
        break;
        default:
            $html .= self::build($question,$question_array,$layer,$parrent);
        break;
        }
        return $html;
    }

}