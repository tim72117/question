<?php

class QuesController extends BaseController {

    protected $page = 1;
    protected $node = 1;
    protected $percent = 0;
    protected $skip = '';
    protected $package_name = 'package';

    public function __construct()
    {
        $this->dataroot = app_path().'/views/ques/data/';
        $this->beforeFilter(function($route) {

            Event::fire('ques.open', array());

            $this->root = $route->getParameter('root');

            $this->doc = QuestionXML\Census::where('dir', $this->root)->first();

            if ($this->doc->closed)
            {
                $has_key = Input::get('key') == 'a48256edc05c473deeac08e3b09f0cd8';
                if ($has_key) {
                    Session::put('ques.testing', true);
                }
                if (!$has_key && !Session::get('ques.testing', false)) {
                    View::share('doc', $this->doc);
                    return View::make($this->package_name . '::' . 'layout-doc')->nest('child', $this->package_name . '::' . 'prompt-close-force');
                }
            }
            elseif ($this->checktime())
            {
                View::share('doc', $this->doc);
                return View::make($this->package_name . '::' . 'layout-doc')->nest('child', $this->package_name . '::' . 'prompt-close');
            }

            Config::addNamespace('ques', $this->dataroot.$this->root);
            $this->config = Config::get('ques::setting');

            $this->table = $this->doc->database . '.dbo.' . $this->doc->table;
        });
    }

    public function page()
    {
        $this->init();

        $this->newpage = new app\library\page;
        $this->newpage->root = $this->dataroot.$this->root;
        $this->newpage->init($this->config);
        $this->setpage();

        if ($this->page > $this->pages_amount) {
            return $this->end();
        }else{
            $this->newpage->loadxml($this->doc, $this->page);
            return $this->build();
        }
    }

    public function init()
    {
        $this->newcid = Ques\Answerer::newcid();
        $this->page = $this->loadpage()['page'];
        $this->pages_amount = $this->doc->pages->count();
        $this->percent = floor((($this->page-1)/$this->pages_amount)*100);
    }

    public function loadpage()
    {
        $pstat_table= DB::table($this->table . '_pstat')->where('newcid', $this->newcid);
        if ($pstat_table->exists()) {

            $pstat = $pstat_table->select('page')->first();
            return array('page'=>$pstat->page, 'node'=>0);

        }else{

            DB::table($this->table . '_pstat')->insert( array('newcid'=>$this->newcid, 'page'=>1, 'updated_at'=>date("Y/n/d H:i:s"), 'created_at'=>date("Y/n/d H:i:s")) );
            return array('page'=>1, 'node'=>0);
        }
    }

    public function setpage()
    {
        if ($this->page > $this->pages_amount) {
            return true;
        }

        $pagetable = DB::table($this->table . '_page' . $this->page)->where('newcid', $this->newcid);

        if ($pagetable->exists()) {
            if (!$pagetable->whereNull('stime'.$this->page)->exists()) {
                $this->page++;
                DB::table($this->table . '_pstat')->where('newcid', $this->newcid)->update( array('page'=>$this->page, 'updated_at'=>date("Y/n/d H:i:s")) );
                return $this->setpage();
            }
        }else{
            DB::table($this->table . '_page' . $this->page)->insert( array('newcid' => $this->newcid, 'ctime'.$this->page => date("Y/n/d H:i:s")) );
        }
        return true;
    }

    public function build()
    {
        if (isset($this->config['hide']) && is_callable($this->config['hide'])) {
            $hide = $this->config['hide']($this->page);
            if ($hide != false) {
                $this->newpage->hide = $hide;
            }
        }

        $this->newpage->bulidQuestion();
        Session::put('name_array', $this->newpage->name_array);
        $init_value = '';
        if (isset($this->config['blade']) && is_callable($this->config['blade'])) {
            $init = array();
            $blade = $this->config['blade']($this->page, $init);
            if ($blade != false) {
                $BladeCompiler_with = new app\library\BladeCompiler_with;
                $this->newpage->question_html = $BladeCompiler_with->compileWiths($this->newpage->question_html, $blade);
            }
            foreach ($init as $key => $value) {
                $init_value .= Form::text($key, $value);
            }
        }

        View::share(array('page' => $this->page, 'doc' => $this->doc, 'percent' => $this->percent, 'isPhone' => substr($this->newcid, 0, 6) == 'phone_'));
        $contents = View::make($this->package_name . '::' . 'page', array(
            'question'            => $this->newpage->question_html,
            'questionEvent'       => $this->newpage->buildQuestionEvent(),
            'questionEvent_check' => $this->newpage->buildQuestionEvent_check(),
            'init_value'          => $init_value
        ))
        ->nest('child_footer', $this->config['auth']['loginView']['footer']);

        $response = Response::make($contents, 200);
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Last-Modified', gmdate( 'D, d M Y H:i:s' ).' GMT');

        return $response;
    }

    public function write()
    {
        $this->init();

        if ($this->page != Input::get('page'))
            throw new app\library\v10\QuesFailedException('newcid - '. $this->newcid .';page - this:'.$this->page.',input:'.Input::get('page') . ';agent - ' . $_SERVER['HTTP_USER_AGENT']);

        $check_atuo_text = Input::get('check_atuo_text');
        $allname_array = Session::get('name_array');

        $this->logInput();

        $skip_array = explode(',', $check_atuo_text);

        $insert = [];
        $rulls = [];
        $skip = [];
        foreach ($allname_array as $name => $name_obj) {
            $name_input = $name;
            $type_input = $name_obj['type'];
            switch($type_input){
                case 'checkbox':
                    $insert[$name_input] = in_array($name_input,$skip_array) ? 'n' : Input::get($name_input,0);
                    $rulls[$name_input] = 'in:0,1,n';
                break;
                case 'radio':
                    $insert[$name_input] = in_array($name_input,$skip_array) ? 'n' : Input::get($name_input,-9);
                    $rulls[$name_input] = 'regex:/^[0-9a-zA-Z_-]+$/';
                    if (count($name_obj['rull']) > 0) {
                        if (array_key_exists($insert[$name_input], $name_obj['rull'])) {
                            $skip_rull = $name_obj['rull'][$insert[$name_input]];
                            $skip_page = $skip_rull->page;
                            $skip_ques = $skip_rull->skip;
                            !isset($skip[$skip_page]) && $skip[$skip_page] = [];
                            $skip[$skip_page] = array_merge($skip[$skip_page], $skip_ques);
                        }
                    }
                break;
                case 'scale':
                    $insert[$name_input] = in_array($name_input,$skip_array) ? 'n' : Input::get($name_input,-9);
                    $rulls[$name_input] = 'regex:/^[0-9a-zA-Z_-]+$/';
                break;
                case 'select':
                    $insert[$name_input] = in_array($name_input,$skip_array) ? 'n' : Input::get($name_input,-9);
                    $rulls[$name_input] = 'regex:/^[0-9a-zA-Z_-]+$/';
                break;
                case 'text':
                    $insert[$name_input] = in_array($name_input,$skip_array) ? 'n' : Input::get($name_input,'');
                    $rulls[$name_input] = 'max:20';
                break;
                case 'textarea':
                    $insert[$name_input] = in_array($name_input,$skip_array) ? 'n' : Input::get($name_input,'');
                    $rulls[$name_input] = 'max:20';
                break;
            }
        }
        $validator = Validator::make($insert, $rulls);
        $this->skip = $skip;
        //echo $validator->fails() ? 1 : 2;
        //$failed = $validator->failed();

        isset($this->config['update']) && is_callable($this->config['update']) && $this->config['update']($this->page, $this, $insert);

        $this->jump($this->page+1);

        $this->writeDB($insert);

        isset($this->config['afterUpdate']) && is_callable($this->config['afterUpdate']) && $this->config['afterUpdate']($this->page, $this, $insert);

        return Redirect::to('/ques/' . $this->root . '/page');
    }

    public function writeDB($ans_array)
    {
        $ans_array['stime'.$this->page] = Input::get('stime','');
        $ans_array['etime'.$this->page] = date("Y/n/d H:i:s");
        DB::table($this->table . '_page' . $this->page)->where('newcid', $this->newcid)->update($ans_array);
    }

    public function jump($page_now)
    {
        $tStamp = date("Y/n/d H:i:s");
        $skip_all = $this->skip;
        ksort($skip_all);

        if (count($skip_all) > 0) {
            foreach ($skip_all as $page => $skip_page) {
                if (in_array('all', $skip_page)) {
                    $columns = DB::table('sys.columns')->whereRaw("object_id=OBJECT_ID('dbo." . $this->table . '_page' . $page . "')")->select('name', DB::raw("'n' AS value"))->lists('value', 'name');
                    $columns['ctime'.$page] = $tStamp;
                    $columns['stime'.$page] = $tStamp;
                    $columns['etime'.$page] = $tStamp;
                    $columns['newcid'] = $this->newcid;
                    DB::table($this->table . '_page' . $page)->insert($columns);
                }
                array_key_exists($page_now, $skip_all) && ($page_now+1 <= $this->pages_amount) && $page_now++;
            }
        }

        DB::table($this->table . '_pstat')->where('newcid', $this->newcid)->update( array('page'=>$page_now, 'updated_at'=>$tStamp) );
    }

    public function end()
    {
        if (isset($this->config['redirect']) && is_callable($this->config['redirect'])) {
            $redirect = $this->config['redirect']($this->page, $this);
            if ($redirect)
                return $redirect;
        }
        return Redirect::to('ques/' . $this->root.'/end');
    }

    public function loginPage()
    {
        if (isset($this->config['parent'])) {
            return Redirect::to($this->config['parent']);
        }

        $loginView = $this->config['auth']['loginView'];

        $intro_view = array_key_exists('intro', $loginView) ? $loginView['intro'] : 'intro';

        View::share('doc', $this->doc);
        $contents = View::make($this->package_name . '::' . $intro_view)
            ->nest('child_head', $loginView['head'])
            ->nest('child_body', $loginView['body'])
            ->nest('child_footer', $loginView['footer']);

        $response = Response::make($contents, 200);
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Last-Modified', gmdate( 'D, d M Y H:i:s' ).' GMT');

        return $response;
    }

    public function login()
    {
        $input = Input::only(array_keys($this->config['auth']['input_rull']));
        $rulls = $this->config['auth']['input_rull'];
        $rulls_message = isset($this->config['auth']['input_rull_message']) ? $this->config['auth']['input_rull_message'] : array();
        $validator = Validator::make($input, $rulls, $rulls_message);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }else{
            isset($this->config['auth']['checker']) && is_callable($this->config['auth']['checker']) && $this->config['auth']['checker']($validator,$this);

            if (count($validator->getMessageBag()->all()) === 0) {
                Event::fire('user.login', array($this->root));
                Session::regenerate();
                return Redirect::to('ques/' . $this->root.'/page');
            }else{
                return Redirect::back()->withErrors($validator)->withInput();
            }

        }
    }

    public function mlogin()
    {
        $config = $this->config;

        if (isset($config['login_customize']) && $config['login_customize']==1) {

            $input_name = array_keys($config['auth']['input_rull']);

            $input = Input::only($input_name);
            $rulls = $config['auth']['input_rull'];
            $rulls_message = isset($config['auth']['input_rull_message']) ? $config['auth']['input_rull_message'] : array();
            $validator = Validator::make($input, $rulls, $rulls_message);
            if ($validator->fails()) {
                return 'error1';
            }else{
                if (isset($config['auth']['checker']) && is_callable($config['auth']['checker']))
                    $config['auth']['checker']($validator,$this);

                if (count($validator->getMessageBag()->all()) === 0) {
                    return Redirect::to($this->root.'/page');
                }else{
                    return 'error2';
                }

            }

        }
    }

    public function skip_page($page_array)
    {
        $this->newcid = Ques\Answerer::newcid();
        $tStamp = date("Y/n/d H:i:s");
        if (is_array($page_array)) {
            foreach ($page_array as $page) {
                $columns = DB::table('sys.columns')->whereRaw("object_id=OBJECT_ID('dbo." . $this->table . "_page" . $page . "')")->select('name',DB::raw("'n' AS value"))->lists('value', 'name');
                $columns['ctime'.$page] = $tStamp;
                $columns['stime'.$page] = $tStamp;
                $columns['etime'.$page] = $tStamp;
                $columns['newcid'] = $this->newcid;
                $page_query = DB::table($this->table . '_page' . $page)->where('newcid', '=', $this->newcid);
                if (!$page_query->exists()) {
                    DB::table($this->table . '_page' . $page)->insert($columns);
                }
            }
        }
    }

    public function checktime()
    {
        $close = false;
        if (isset($this->doc->start_at) && strtotime($this->doc->start_at) > time()) {
            $close = true;
        }
        if (isset($this->doc->close_at) && strtotime($this->doc->close_at) < time()) {
            $close = true;
        }
        return $close;
    }

    public function logInput()
    {
        if (isset($this->config['logInput']) && $this->config['logInput'] && is_dir($this->config['logInputDir'])) {
            $input = Input::all();
            File::append($this->config['logInputDir'].'/'.$this->newcid.'.json', json_encode($input)."\n");
        }
    }

    public function maintenance()
    {
        View::share('doc', $this->doc);
        return View::make($this->package_name . '::' . 'layout-doc')->nest('child', $this->package_name . '::' . 'maintenance');
    }

}
