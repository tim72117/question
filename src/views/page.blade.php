<!DOCTYPE html>
<html xml:lang="zh-TW" lang="zh-TW" ng-app="app">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title><?=$doc->title?></title>

<!--[if lt IE 9]><script src="/js/html5shiv.js"></script><![endif]-->
<script src="/js/jquery/jquery-1.11.2.min.js"></script>
<script src="/js/angular/1.5.3/angular.min.js"></script>
<script src="/js/angular/1.5.3/angular-sanitize.min.js"></script>
<script src="/js/angular/1.5.3/angular-animate.min.js"></script>
<script src="/js/angular/1.5.3/angular-aria.min.js"></script>
<script src="/js/angular/1.5.3/angular-messages.min.js"></script>
<script src="/js/angular_material/1.1.0/angular-material.min.js"></script>
<script src="/packages/tim72117/question/js/timer_v5.js"></script>
<script src="/packages/tim72117/question/js/qcheck_v4.js"></script>

<link rel="stylesheet" href="/packages/tim72117/question/css/page_struct.css" />
<link rel="stylesheet" href="/resource/<?=$doc->dir?>/banner.css" />
<link rel="stylesheet" href="/css/Semantic-UI/2.1.8/components/dimmer.min.css" />
<link rel="stylesheet" href="/js/angular_material/1.1.0/angular-material.min.css">

<script>

var app = angular.module('app', ['ngSanitize', 'ngMaterial']);

app.controller('quesController', function($scope, $filter) {

    $scope.questions = {};
    $scope.percent = '<?=$percent?>';
    $scope.checkCheckboxLimit = function(limit, id, index, reset) {

        var items = Object.keys($scope.questions[id]).map(function (key) {return $scope.questions[id][key]});
        $scope.questions[id][index].reset = reset;

        for (var i=0; i < items.length; i++) {
            if (reset && !items[i].reset) {
                items[i].checked = false;
            }
            if (!reset && items[i].reset) {
                items[i].checked = false;
            }
        }
        if (limit != 0 && $filter('filter')(items, {checked: true}).length > limit) {
            $scope.questions[id][index].checked = false;
        }
    };

    $scope.initSubs = function(id) {
        $('#'+id).qshow();
    };

});

app.config(function ($compileProvider, $mdIconProvider, $mdThemingProvider) {
    $compileProvider.debugInfoEnabled(false);
    $mdThemingProvider.theme('default').warnPalette('green');
});

var isCheck = false;

$(document).ready(function(){

    $('form[name=form1]').submit(function(){
        if(!isCheck){
            return false;
        }
    });

    <?=$questionEvent?>

    $('form').find(':radio:checked,:checkbox:checked').each(function(){$(this).triggerHandler('click');});
    $('form').find('select option:first-child:not(:selected)').each(function(){$(this).parent('select').triggerHandler('change');});
    $('#checkForm').prop('disabled', false);

    //送出檢誤
    $('#checkForm').click(function(){

        $('#checkForm').prop('disabled', true);

        var fillnull = [];
        var checkOK = true;

        var testarray = {};
        var qcheck = $(':input.qcheck');

        qcheck.each(function(){
            var name = $(this).attr('name');

            if( !testarray.hasOwnProperty(name) ){

                var obj = $(':input[name='+name+']');

                if( <?=(($isPhone?'false':'true') . '&&')?> checkEmpty(obj) ){
                    checkOK = false;
                    return false;
                }

                if( obj.filter(':disabled,:hidden').length==obj.length )
                if( obj.is(':disabled,:hidden') )
                    fillnull.push(name);

                testarray[name] = name;
            }
        });

        if(!checkOK){
            $('#checkForm').prop('disabled', false);
            return false;
        }else{
            $('#checkForm').prop('disabled', false);
            <?=$questionEvent_check?>
            $('input[name=check_atuo_text]').val(fillnull);
            isCheck = true;
            $('#checkForm').prop('disabled', true);
        }

        $('form[name=form1]').submit();

    });
});
</script>
</head>
<body ng-controller="quesController">
    <md-content layout="row" layout-align="center start">
        <div class="ui page dimmer">
            <div class="content">
                <h1 class="ui header" id="logout_timer"></h1>
            </div>
        </div>
        <md-content id="building">
            <img  id="header" class="banner<?=$page?> md-card-image"></img>
            <div class="hint" style="position:relative">
        <!--        <div id="tooltip" style="position:absolute;left:10px;top:0;width:150px;height:80px;color:#000;z-index:-1"></div>-->
            </div>
            <md-card id="contents">
                <md-progress-linear class="md-warn" md-mode="buffer" value="@{{percent}}" md-buffer-value="@{{percent*1+10}}"></md-progress-linear>

                <form action="write" method="post" name="form1">
                    <input type="hidden" name="check_atuo_text" value="" />
                    <input type="hidden" name="page" value="<?=$page?>" />
                    <input type="hidden" name="stime" value="<?=date("Y/n/d H:i:s")?>" />
                    <input type="hidden" name="_token" value="<?=csrf_token()?>" />

                    <?=$question?>
                </form>

                <md-button id="checkForm" disabled="disabled" class="md-raised md-primary">下一頁</md-button>
                <div id="init_value" style="display: none"><?=$init_value?></div>
            </md-card>
            <md-card><md-card-content><?=$child_footer?></md-card-content></md-card>
        </md-content>
    </md-content>
</body>
</html>