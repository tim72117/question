<?php
namespace app\library\v10;

use Form, Input;

class buildQuestion {

    static $name = [];
    static $hide = [];
    static $label_count = 0;

    static function getName()
    {
        return self::$name;
    }

    static function build($question,$question_array,$layer,$parrent)
    {
        $html = '';

        $is_disabled_skipfrom = false;

        $hn_style = '';
        $fieldA_display_style = '';
        $div_init = '';

        $classMap_div = array(0=>"ans",1=>"sub1_ans",2=>"sub2_ans",3=>"sub2_ans",4=>"sub2_ans");
        $class = $classMap_div[$layer];


        $questionAttr = $question->attributes();
        if($questionAttr['skipfrom']!=''){
            if( $questionAttr['init']=='disabled' ){
                $fieldA_display_style = ' display:none';
                $hn_style = ' color:#777';
                $is_disabled_skipfrom = true;
                $div_init = 'disabled';
            }
        }


        $is_disabled = !($layer==0||($layer>=1&&$parrent=='list'))||$is_disabled_skipfrom;
        $display_style = ($layer==0||$parrent=='list')?'':' display:none';


        if( $question->getName()=="question" && $question->type=='explain' ){
            $html .= '<md-card-content class="main_explain">';
        }elseif( $question->getName()=="question" ){
            $html .= '<md-card-content class="main" style="'.$display_style.'">';
        }

        if( count(self::$hide)>0 && in_array($question->id, self::$hide) ){
            $display_style .= ';display:none';
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
            $html .= '<div class="readme" style="background-color:transparent">'.(string)$question->title.'</div>';
        }else{

            if( (string)$question->title!='' ){
                if($layer==0){
                    $title_string = '<strong>'.((string)$question->idlab).'</strong> '.$title.($fieldA_display_style==' display:none'?'<p style="font-size:10px;color:red">此題無須填答</p>':'');
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

        if( $question->type=='scale' ){
            $question_amount = count($question->answer->item);
            $nofixedQArray = range(0,$question_amount-1);
            $nofixedQArray_text = implode(',',$nofixedQArray);
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
                $html .= '<input type="radio" class="qcheck" id="'.$question->id.'_l'.$item_count.'" name="'.$question->answer->name.'" value="'.$attr["value"].'"'.($is_disabled?' disabled="disabled"':'').' />';
                $html .= '<label for="'.$question->id.'_l'.$item_count.'" class="radio">'.(string)$answer.'</label>';

                foreach($answer->text as $text){
                    $html .= '<span style="font-size:.8em;color:#666666">'.$text.'</span>';
                }
                $html .= '</p>';

                $item_count++;

                if( !array_key_exists((string)$question->answer->name,self::$name) )
                    self::$name[(string)$question->answer->name] = array('type'=>'radio','layer'=>$layer,'rull'=>[]);
                if( isset($answer['pageskip']) && $answer['pageskip']!='' ){
                    $answer['pageskip'] = str_replace('\'','"',$answer['pageskip']);
                    self::$name[(string)$question->answer->name]['rull'][(string)$attr["value"]] = json_decode((string)$answer['pageskip']);
                }

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
                $html .= '<div style="display:table-row">';
                $html .= '<div style="display:table-cell;line-height:30px">'.(string)$answer.'</div>';
                $html .= '<div style="display:table-cell;line-height:30px">';
                $html .= Form::text((string)$attr["name"], Input::old((string)$attr["name"], ''), array(
                    'placeholder' => $attr["sub_title"],
                    'class' => 'fat qcheck',
                    'parrent' => $parrent,
                    //'disabled' => ($is_disabled ? 'disabled' : ''),
                    'maxlength' => $attr["size"],
                    'textsize' => $attr["size"],
                    'size' => $attr["size"],
                    'filter' => $attr["filter"],
                    'style' => 'margin:1px;max-width:500px'
                ));
                $html .= '</div>';
                $html .= '</div>';

                if( !array_key_exists((string)$attr["name"],self::$name) )
                    self::$name[(string)$attr["name"]] = array('type'=>'text','layer'=>$layer);
            break;
            case "text_phone":
                $html .= (string)$answer.'<input type="text" class="qcheck" name="'.$attr["name"];
                $html .= '" value="" maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" size="'.$attr["size"].'" filter="'.$attr["filter"].'" />';
            break;
            //------------------------------------------------textarea
            case "textarea":
                $html .= '<p><textarea placeholder="請勿輸入超過'.$attr["size"].'個中文字" type="textarea" class="qcheck" name="'.$question->answer->name;
                $html .= '" value="" '.($is_disabled?'disabled="disabled"':'').' maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" cols="'.$attr["cols"].'" rows="'.$attr["rows"].'"></textarea><p>'.(string)$answer.'</p></p>';

                if( !array_key_exists((string)$question->answer->name,self::$name) )
                    self::$name[(string)$question->answer->name] = array('type'=>'textarea','layer'=>$layer);
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

                if( $checkbox_style['cstyle']!='2' ){

                    if( count($question->answer->item)>10 ){
                        $html .= '<div class="checkbox horizontal">';
                    }else{
                        $html .= '<div class="checkbox horizontal less">';
                    }
                    $html .= '<p class="checkbox">';
                    $html .= '<input type="checkbox" class="qcheck" id="lb'.self::$label_count.'" name="'.$attr["name"].'" value="1" '.($is_disabled?'disabled="disabled"':'').' size="'.$question->size.'" '.$subs_string.' />';
                    $html .= '<label for="lb'.self::$label_count.'" class="checkbox">'.(string)$answer.'</label>';
                    self::$label_count++;
                    $html .= '</p>';

                    $sub_array = explode(",", $attr["sub"]);
                    foreach($sub_array as $attr_i){
                        $sub = $question_array->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                        if( isset($sub[0]) )
                            $html .= self::buildQuestion_simp($sub[0],$question_array,$layer+1,(string)$question->type);
                    }
                    $html .= '</div>';

                }elseif( $checkbox_style['cstyle']=='2' ){

                    $html .= '<div>';
                    $html .= '<p class="checkbox">';
                    $html .= '<input type="checkbox" class="qcheck" id="lb'.self::$label_count.'" name="'.$attr["name"].'" value="1" '.($is_disabled?'disabled="disabled"':'').' size="'.$question->size.'" '.$subs_string.' />';
                    $html .= '<label style="cursor:pointer" for="lb'.self::$label_count.'" class="checkbox">'.(string)$answer.'</label>';
                    self::$label_count++;
                    $html .= '</p>';

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

                $randQi = $nofixedQArray[$item_count-1];
                $question_item = $question->answer->item[$randQi];

                $scale_style = $question->type->attributes();

                if($scale_style['sstyle']=='2'){

                    $tableHead .= '<th style="text-align:center;width:40px;font-size:12px"><b>'.(string)$answer.'</b></th>';

                }elseif($scale_style['sstyle']=='3'){

                    $scale_title = (string)$answer;
                    $table .= '<tr>';
                    foreach($question->answer->degree as $degree){
                        $attr_degree = $degree->attributes();
                        $table .= '<td class="scale">';
                        $table .= '<input type="radio" class="qcheck scale" name="'.$attr['name'].'" value="'.$attr_degree['value'].'"'.($is_disabled?' disabled="disabled"':'').' />';
                        $table .= '</td>';
                    }
                    $table .= "</tr>";

                }elseif($scale_style['sstyle']=='4'){//select

                    $scale_title = (string)$question_item;
                    $table .= '<tr>';
                    $table .= '<td class="scale scale-title" width="5px"><p class="scale">('.$item_count.')</p></td>';
                    $table .= '<td class="scale"><p class="scale" style="">'.$scale_title.'</p></td>';

                    $table .= '<td class="scale" style="width:200px"><select type="select-one" class="qcheck" name="'.$attr["name"].'"><option value="-1">請選擇</option>';
                    $degree_key = 0;
                    foreach($question->answer->degree as $degree){
                        $attr_degree = $degree->attributes();
                        $table .= '<option value="'.$attr_degree["value"].'">'.(string)$degree.'</option>';
                        $degree_key++;
                    }
                    $table .= '</select></td>';
                    $table .= '</tr>';

                }else{

                    $scale_title = (string)$question_item;
                    $table .= '<tr>';
                    $table .= '<td class="scale scale-title" width="5px"><p class="scale">('.$item_count.')</p></td>';
                    $table .= '<td class="scale"><p class="scale">'.$scale_title.'</p></td>';
                    $degree_key = 0;
                    $attr = $question_item->attributes();

                    foreach($question->answer->degree as $degree){
                        $attr_degree = $degree->attributes();
                        $table .= '<td class="scale">';
                        $table .= '<input type="radio" class="qcheck scale" id="'.'lb'.self::$label_count.'" name="'.$attr['name'].'" value="'.$attr_degree['value'].'" '.($is_disabled?'disabled="disabled"':'').' />';
                        $table .= '<label for="lb'.self::$label_count.'" class="scale"></label>';
                        $table .= '</td>';
                        $degree_key++;
                        self::$label_count++;
                    }
                    $table .= "</tr>";

                }
                 $item_count++;

                if( !array_key_exists((string)$attr["name"],self::$name) )
                    self::$name[(string)$attr["name"]] = array('type'=>'scale','layer'=>$layer);

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

            $html .= '<select type="select-one" class="qcheck" name="'.$question->answer->name.'"'.($is_disabled?' disabled="disabled"':'').'><option value="-9">請選擇</option>'.$option.'</select>';

            if( !array_key_exists((string)$question->answer->name,self::$name) )
                self::$name[(string)$question->answer->name] = array('type'=>'select','layer'=>$layer);

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

            foreach($question->answer->degree as $degree){
                $attr_degree = $degree->attributes();
                $table .= "<tr>";
                foreach($question->answer->item as $item){
                    $attr_item = $item->attributes();
                    $table .= '<td class="scale">';
                    $table .= '<input type="radio" class="qcheck scale" id="'.'lb'.self::$label_count.'" name="'.$attr_item["name"].'" value="'.$attr_degree["value"].'" '.($is_disabled?'disabled="disabled"':'').' />';
                    $table .= '<label for="lb'.self::$label_count.'" class="scale"></label>';
                    $table .= '</td>';
                    self::$label_count++;
                }
                $table .= '<td class="scale"><p class="scale" style="">'.(string)$degree.'</p></td>';
                $table .= "</tr>";
            }
            $html .= '<table class="scale" cellspacing="0"><thead><tr>'.$tableHead.'<th></th></tr></thead><tbody>'.$table.'</tbody></table>';

        }elseif($scale_style['sstyle']=='3'){

            $tableHead = '';
            $amount_degree = count($question->answer->degree);
            if($amount_degree>4){$scale_width = 35;}elseif($amount_degree>6){$scale_width = 30;}else{$scale_width = 60;}
            foreach($question->answer->degree as $degree){
                $attr_degree = $degree->attributes();
                $tableHead .= '<th style="text-align:center;font-size:0.8em;width:'.$scale_width.'px"><b>'.(string)$degree.'</b></th>';
            }
            $html .= '<table class="scale" cellspacing="0"><tbody>'.$table.'</tbody><thead><tr>'.$tableHead.'</tr></thead></table>';

        }elseif($scale_style['sstyle']=='4'){

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

            $html .= '<table class="scale" cellspacing="0"><thead><tr><th></th><th></th>'.$tableHead.'</tr></thead><tbody>'.$table.'</tbody></table>';

            if( isset($question->answer['randomOrder']) )
                $html .= '<input type="hidden" name="'.$question->answer['randomOrder'].'" value="'.$nofixedQArray_text.'" />';

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
            $html .= '</md-card-content>';

        return $html;
    }

    static function buildQuestion_simp($question,$question_array,$layer,$parrent)
    {
        $is_disabled = false;
        $html = '';
        switch($question->type){
        case "text":
            $html .= '<div id="'.$question->id.'" style="display:none;text-indent:20px">';
            $html .= '<p style="margin:2px">'.(string)$question->title.'</p>';
            foreach($question->answer->item as $answer){
                $attr = $answer->attributes();
                $type_attr = $question->type->attributes();
                $sub_title_length = strlen($attr["sub_title"]);

                $input_text = Form::text((string)$attr["name"], Input::old((string)$attr["name"], ''), array(
                    'placeholder' => $attr["sub_title"],
                    'class' => 'fat qcheck',
                    'parrent' => $parrent,
                    'disabled' => $is_disabled ? 'disabled' : '',
                    'maxlength' => $attr["size"],
                    'textsize' => $attr["size"],
                    'size' => $attr["size"],
                    'filter' => $attr["filter"],
                    'style' => 'min-width:'.$sub_title_length.'em'
                ));
                //$html .= '<input class="fat" placeholder="'.$attr["sub_title"].'" type="text" parrent="'.$parrent.'" name="'.$attr["name"].'" value="" '.($is_disabled?'disabled="disabled"':'');
                //$html .= ' maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" size="'.$attr["size"].'" filter="'.$attr["filter"].'" />';
                if( $type_attr['style']!='short' ){
                    $html .= '<p style="margin:2px">'.(string)$answer.$input_text.'</p>';
                }else{
                    $html .= '<span style="margin:2px">'.(string)$answer.$input_text.'</span>';
                }

                if( !array_key_exists((string)$attr["name"],self::$name) )
                    self::$name[(string)$attr["name"]] = array('type'=>'text','layer'=>$layer);


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