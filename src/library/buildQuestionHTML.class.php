<?php
namespace app\library\v10;

use Form, Input;

class buildQuestionHTML {

    static $name = [];
    static $hide = [];
    static $label_count = 0;
    static $is_disabled = false;
    static $questions = [];
    static $qs = [];

    static function getName()
    {
        return self::$name;
    }

    static function build($question, $layer, $parrent)
    {
        $html = '';

        $is_disabled_skipfrom = false;
        $fieldA_isHide = false;
        $isHide = false;

        $div_init = '';

        ($question->type=='explain' || $parrent=='list') && $layer = 0;

        $classMap_div = array(0=>"ans",1=>"sub1_ans",2=>"sub2_ans",3=>"sub2_ans",4=>"sub2_ans");
        $class = $classMap_div[$layer];

        $questionAttr = $question->attributes();
        if($questionAttr['skipfrom']!=''){
            if( $questionAttr['init']=='disabled' ){
                $fieldA_isHide = true;
                $is_disabled_skipfrom = true;
                $div_init = 'disabled';
            }
        }


        self::$is_disabled = !($layer==0||($layer>=1&&$parrent=='list'))||$is_disabled_skipfrom;
        $layer!=0 && $parrent!='list' && $isHide = true;
        count(self::$hide)>0 && in_array($question->id, self::$hide) && $isHide = true;

        if( $question->getName()=="question" ){
            $html .= '<div class="ui divider"></div>';
            $html .= '<h4 class="ui left floated header">' . (string)$question->idlab . '</h4>';
            $html .= '<div class="main ui form" style="' . ($isHide ? 'display:none' : '') . '">';
        }

        $html .= '<div id="'.$question->id.'" parrent="'.$parrent.'" class="grouped fields layer' . $layer;
        $html .= in_array($question->type, ['radio', 'checkbox']) ? ' ui form' : '';
        $html .= '"';
        $html .= $layer!=0 ? ' style="display:none"' : '';
        $html .= '>';

        if ($question->type=='explain') {
            $html .= '<div>' . (string)$question->title . '</div>';
        } else {
            $html .= '<h4 class="ui header">';
            $html .= (string)$question->title;
            $html .= $fieldA_isHide ? '<p style="font-size:10px;color:red">此題無須填答</p>' : '';
            $html .= '</h4>';
        }

        $html .= '<div class="fieldA '.$class.'" init="'.$div_init.'" style="'. ($fieldA_isHide ? 'display:none' : '') . '">';

        $option = "";
        $table = '';
        $sub_array_all = array();

        $item_count = 1;
        $tableHead = '';
        foreach($question->answer->item as $item){
            $attr = $item->attributes();
            switch($question->type){
            case "explain":
                $html .= '<h3>'.(string)$question->title.'</h3>';
            break;
            case "radio":
                $html .= self::buildRadio($question->id, $question->answer->name, $item, $attr, $layer);
            break;
            case "select":
                $option .= '<option value="' . $attr['value'] . '">' . (string)$item . '</option>';
                $item_count++;
            break;
            case "text":
                $html .= '<div class="field">';
                $html .= '<div style="display:table-cell;line-height:30px">'.(string)$item.'</div>';
                $html .= Form::text((string)$attr['name'], Input::old((string)$attr['name'], ''), array(
                    'placeholder' => $attr["sub_title"],
                    'class' => 'qcheck',
                    'parrent' => $parrent,
                    //'disabled' => (self::$is_disabled ? 'disabled' : ''),
                    'maxlength' => $attr["size"],
                    'textsize' => $attr["size"],
                    'size' => $attr["size"],
                    'filter' => $attr["filter"],
                ));
                $html .= '</div>';

                if( !array_key_exists((string)$attr['name'],self::$name) )
                    self::$name[(string)$attr['name']] = array('type'=>'text','layer'=>$layer);
            break;
            case "text_phone":
                $html .= (string)$item.'<input type="text" class="qcheck" name="'.$attr['name'];
                $html .= '" value="" maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" size="'.$attr["size"].'" filter="'.$attr["filter"].'" />';
            break;
            case "textarea":
                $html .= '<p><textarea placeholder="請勿輸入超過'.$attr["size"].'個中文字" type="textarea" class="qcheck" name="'.$question->answer->name;
                $html .= '" value="" '.(self::$is_disabled?'disabled="disabled"':'').' maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" cols="'.$attr["cols"].'" rows="'.$attr["rows"].'"></textarea><p>'.(string)$item.'</p></p>';

                if( !array_key_exists((string)$question->answer->name,self::$name) )
                    self::$name[(string)$question->answer->name] = array('type'=>'textarea','layer'=>$layer);
            break;
            case "checkbox":
                $html .= self::buildCheckbox($question->id, $attr['name'], $item, $attr, $layer);
            break;
            case "extra":
                $html .= '<p style="line-height:1.8em">';
                $subs_array = NULL;
                if($attr['sub']!='')
                    $subs_array = array_map( create_function('$id', 'return "#".$id;'),explode(",",$attr["sub"]) );
                $subs_string = '';
                if(is_array($subs_array))
                    $subs_string = 'sub="'.implode(",",$subs_array).'"';
                $html .= '<div name="'.$attr['name'].'"></div></p>';
            break;
            case "scale":

                $question_item = $item;

                $scale_style = $question->type->attributes();

                if($scale_style['sstyle']=='2'){

                    $tableHead .= '<th class="one wide">' . (string)$item . '</th>';

                }elseif($scale_style['sstyle']=='3'){

                    $table .= '<tr>';
                    foreach($question->answer->degree as $degree){
                        $attr_degree = $degree->attributes();
                        $table .= '<td class="one wide">';
                        $table .= '<div class="ui radio checkbox">';
                        $table .= '<input type="radio" class="qcheck scale" id="lb'.self::$label_count.'" name="'.$attr['name'].'" value="'.$attr_degree['value'].'" '.(self::$is_disabled?'disabled="disabled"':'').' />';
                        $table .= '<label for="lb'.self::$label_count.'"></label>';
                        $table .= '</div>';
                        $table .= '</td>';
                    }
                    $table .= "</tr>";

                }elseif($scale_style['sstyle']=='4'){//select

                    $scale_title = (string)$question_item;
                    $table .= '<tr>';
                    $table .= '<td>'.$scale_title.'</td>';
                    $table .= '<td><select type="select-one" class="qcheck" name="'.$attr['name'].'"><option value="-1">請選擇</option>';
                    foreach($question->answer->degree as $degree){
                        $attr_degree = $degree->attributes();
                        $table .= '<option value="'.$attr_degree['value'].'">'.(string)$degree.'</option>';
                    }
                    $table .= '</select></td>';
                    $table .= '</tr>';

                }else{
                    $table .= self::buildScaleNormal($attr['name'], $item, $question->answer->degree, $item_count);
                }

                $item_count++;

                if( !array_key_exists((string)$attr['name'],self::$name) )
                    self::$name[(string)$attr['name']] = array('type'=>'scale','layer'=>$layer);

            break;
            case "scale_text":
                $table .= '<tr>';
                $table .= '<td class="scale"><p class="scale" style="margin-left:1em;text-indent:-1em">'.(string)$item.'</p></td>';
                foreach($question->answer->degree as $degree){
                  $attr_degree = $degree->attributes();
                  $table .= '<td class="scale"><input type="text" class="qcheck" size="10" name="'.$attr['name'].'" value="" /></td>';
                }
                $table .= "</tr>";
            break;
            case "list":
                $html .= "<p>".(string)$item."</p>";

                $sub_array = explode(",", $attr["sub"]);
                foreach($sub_array as $attr_i){
                    $sub = self::$questions->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                    if($sub[0])
                      $html .= self::build($sub[0], $layer+1, 'list');
                }
            break;
            }

            if( $attr["sub"] && $question->type!="select" && $question->type!="list" && $question->type!="checkbox" ){
                $sub_array = explode(",", $attr["sub"]);
                foreach($sub_array as $attr_i){
                    $sub = self::$questions->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                    if($sub[0])
                        $html .= self::build($sub[0], $layer+1, (string)$question->type);
                }
            }elseif( $attr["sub"] && $question->type=="select" ){
                $sub_array = explode(",", $attr["sub"]);
                foreach($sub_array as $attr_i){
                    $sub = self::$questions->xpath("/page/question_sub/id[.='".$attr_i."']/parent::*");
                    if($sub[0])
                        array_push($sub_array_all,$sub[0]);
                }
            }

        }


        if( $question->type=='select' ){

            $html .= '<select type="select-one" class="qcheck ui dropdown" name="'.$question->answer->name.'"'.(self::$is_disabled?' disabled="disabled"':'').'>';
            $html .= '<option value="-9">請選擇</option>'.$option.'</select>';

            if( !array_key_exists((string)$question->answer->name, self::$name) )
                self::$name[(string)$question->answer->name] = array('type'=>'select','layer'=>$layer);

            if( count($sub_array_all)>0 ){
                foreach($sub_array_all as $sub){
                    $html .= self::build($sub, $layer+1, 'select');
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
                    $table .= '<td>';
                    $table .= '<div class="ui radio checkbox">';
                    $table .= '<input type="radio" class="qcheck hidden" id="lb'.self::$label_count.'" name="'.$attr_item['name'].'" value="'.$attr_degree['value'].'"'.(self::$is_disabled?' disabled="disabled"':'').' />';
                    $table .= '<label for="lb' . self::$label_count . '"></label>';
                    $table .= '</div>';
                    $table .= '</td>';
                    self::$label_count++;
                }
                $table .= '<td>' . (string)$degree . '</td>';
                $table .= "</tr>";
            }
            $html .= '<table class="ui table scale"><thead><tr>'.$tableHead.'<th></th></tr></thead><tbody>'.$table.'</tbody></table>';

        }elseif($scale_style['sstyle']=='3'){

            $tableHead = '';
            foreach($question->answer->degree as $degree){
                $tableHead .= '<th class="one wide" style="vertical-align:top">' . (string)$degree . '</th>';
            }
            $html .= '<table class="ui table scale"><thead><tr>'.$tableHead.'</tr></thead><tbody>'.$table.'</tbody></table>';

        }elseif($scale_style['sstyle']=='4'){

            $html .= '<table class="scale"><tbody>'.$table.'</tbody></table>';

        }else{

            $tableHead = '';
            foreach($question->answer->degree as $degree){
                $tableHead .= '<th class="one wide" style="vertical-align:top">' . (string)$degree . '</th>';
            }
            $html .= '<div class="field"><table class="ui table scale"><thead><tr><th></th><th></th>'.$tableHead.'</tr></thead><tbody>'.$table.'</tbody></table></div>';

        }


        if( $question->type=="scale_text" ){
            foreach($question->answer->degree as $degree){
                $attr_degree = $degree->attributes();
                $tableHead .= '<th style="font-size:0.8em;width:'.$attr_degree["width"].'"><b>'.(string)$degree.'</b></th>';
            }
            $html .= '<table class="scale" cellspacing="0"><thead><tr><th></th>'.$tableHead.'</tr></thead><tbody>'.$table.'</tbody></table>';
        }




        $html .= '</div>';


        $html .= '</div>';


        if( $question->getName()=="question" )
            $html .= '</div>';


        return $html;
    }

    static function buildQuestion_simp($question, $layer, $parrent)
    {
        $is_disabled = false;
        $html = '';
        switch($question->type){
        case "text":
            $html .= '<div id="'.$question->id.'" style="display:none;text-indent:20px">';
            $html .= '<p style="margin:2px">'.(string)$question->title.'</p>';
            foreach($question->answer->item as $item){
                $attr = $item->attributes();
                $type_attr = $question->type->attributes();
                $sub_title_length = strlen($attr["sub_title"]);

                $input_text = Form::text((string)$attr['name'], Input::old((string)$attr['name'], ''), array(
                    'placeholder' => $attr["sub_title"],
                    'class' => 'fat qcheck',
                    'parrent' => $parrent,
                    'disabled' => self::$is_disabled ? 'disabled' : '',
                    'maxlength' => $attr["size"],
                    'textsize' => $attr["size"],
                    'size' => $attr["size"],
                    'filter' => $attr["filter"],
                    'style' => 'min-width:'.$sub_title_length.'em'
                ));
                //$html .= '<input class="fat" placeholder="'.$attr["sub_title"].'" type="text" parrent="'.$parrent.'" name="'.$attr['name'].'" value="" '.(self::$is_disabled?'disabled="disabled"':'');
                //$html .= ' maxlength="'.$attr["size"].'" textsize="'.$attr["size"].'" size="'.$attr["size"].'" filter="'.$attr["filter"].'" />';
                if( $type_attr['style']!='short' ){
                    $html .= '<p style="margin:2px">'.(string)$item.$input_text.'</p>';
                }else{
                    $html .= '<span style="margin:2px">'.(string)$item.$input_text.'</span>';
                }

                if( !array_key_exists((string)$attr['name'],self::$name) )
                    self::$name[(string)$attr['name']] = array('type'=>'text','layer'=>$layer);


            }
            $html .= '</div>';
        break;
        default:
            $html .= self::build($question, $layer, $parrent);
        break;
        }
        return $html;
    }


    static function buildFields()
    {

    }

    static function buildRadio($id, $name, $item, $attr, $layer)
    {
        $html = '';
        $html .= '<div class="field">';
        $html .= '<div class="ui radio checkbox">';
        $html .= '<input type="radio" class="qcheck hidden" id="lb'.self::$label_count.'" name="'.$name.'" value="'.$attr['value'].'"'.(self::$is_disabled?' disabled="disabled"':'').' />';
        $html .= '<label for="lb'.self::$label_count.'" class="radio">'.(string)$item.'</label>';
        $html .= '</div>';
        $html .= '</div>';

        self::$label_count++;

        if( !array_key_exists((string)$name, self::$name) )
            self::$name[(string)$name] = array('type' => 'radio', 'layer' => $layer, 'rull' => []);
        if( isset($item['pageskip']) && $item['pageskip']!='' ){
            $item['pageskip'] = str_replace('\'','"',$item['pageskip']);
            self::$name[(string)$name]['rull'][(string)$attr['value']] = json_decode((string)$item['pageskip']);
        }

        return $html;
    }

    static function buildCheckbox($id, $name, $item, $attr, $layer)
    {
        $subs_array = $attr['sub']!='' ? array_map(create_function('$id', 'return "#".$id;'), explode(",", $attr['sub'])) : [];
        $subs_string = ' sub="'.implode(",", $subs_array).'"';

        $html = '';
        $html .= '<div class="field">';
        $html .= '<div class="ui checkbox">';
        $html .= '<input type="checkbox" class="qcheck hidden" id="lb'.self::$label_count.'" name="'.$name.'" value="1" '.(self::$is_disabled?'disabled="disabled"':'').' '.$subs_string.' />';
        $html .= '<label for="lb'.self::$label_count.'">'.(string)$item.'</label>';
        $html .= '</div>';

        self::$label_count++;

        $sub_array = explode(',', $attr['sub']);
        foreach($sub_array as $sub_i){
            $sub = self::$questions->xpath("/page/question_sub/id[.='".$sub_i."']/parent::*");
            if (isset($sub[0]))
                $html .= self::buildQuestion_simp($sub[0], $layer+1, 'checkbox');
        }
        $html .= '</div>';

        if( !array_key_exists((string)$attr['name'],self::$name) )
            self::$name[(string)$attr['name']] = array('type'=>'checkbox','layer'=>$layer);

        return $html;
    }

    static function buildScaleNormal($name, $item, $degrees, $item_count)
    {
        $table = '';
        $table .= '<tr>';
        $table .= '<td class="one wide">('.$item_count.')</td>';
        $table .= '<td>'.(string)$item.'</td>';

        foreach($degrees as $degree){
            $attr_degree = $degree->attributes();
            $table .= '<td>';
            $table .= '<div class="ui radio checkbox">';
            $table .= '<input type="radio" class="qcheck" id="lb'.self::$label_count.'" name="'.$name.'" value="'.$attr_degree['value'].'" '.(self::$is_disabled?'disabled="disabled"':'').' />';
            $table .= '<label for="lb'.self::$label_count.'"></label>';
            $table .= '</div>';
            $table .= '</td>';
            self::$label_count++;
        }
        $table .= "</tr>";

        return $table;
    }

}