<?php

class QuesADController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | Default Home Controller
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |   Route::get('/', 'HomeController@showWelcome');
    |
    */

    protected $package_name = 'package';

    public function __construct(){
        $this->beforeFilter(function($route){
            $this->root = $route->getParameter('root');
            Config::addNamespace('ques', app_path().'/views/ques/data/'.$this->root);
            $this->config = Config::get('ques::setting');
        });
    }

    public function paperLogin() {
        $dddos_error = Input::old('dddos_error');
        $csrf_error = Input::old('csrf_error');

        Session::flush();
        Session::start();

        //if( Input::has('test') )
        //  $this->test();

        $config = Config::get('ques::setting_paper');
        $loginView = $config['auth']['loginView'];

        View::share('dddos_error',$dddos_error);
        View::share('csrf_error',$csrf_error);
        View::share('config',$config);

        $contents = View::make($loginView['intro'], array('auth'=>'empty', 'root'=>$this->root))
            ->nest('child_head', $loginView['head'])
            ->nest('child_body', $loginView['body'])
            ->nest('child_footer', $loginView['footer']);

        $response = Response::make($contents, 200);
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->header('Pragma', 'no-cache');
        $response->header('Last-Modified', gmdate( 'D, d M Y H:i:s' ).' GMT');
        return $response;
    }

    public function login() {

        $this->config = Config::get('ques::setting_paper');
        $config = $this->config;
        Config::set('database.default', $this->config['connections']);
        Config::set('database.connections.sqlsrv_ques.database', $this->config['database']);

        $input_name = array_keys($config['auth']['input_rull']);

        $input = Input::only($input_name);
        $rulls = $config['auth']['input_rull'];
        $rulls_message = isset($config['auth']['input_rull_message']) ? $config['auth']['input_rull_message'] : array();
        $validator = Validator::make($input, $rulls, $rulls_message);

        if( $validator->fails() ){
            return Redirect::back()->withErrors($validator);
        }else{
            if( isset($config['auth']['checker']) && is_callable($config['auth']['checker']) )
                $config['auth']['checker']($validator,$this);

            if( count($validator->getMessageBag()->all()) === 0 ){
                return Redirect::to($this->root.'/page');
            }else{
                return Redirect::back()->withErrors($validator);
            }

        }

    }

    public function sharePage($root, $page) {
        $doc = QuestionXML\Census::where('dir', $this->root)->first();
        View::share('doc', $doc);
        return View::make($this->package_name . '::' . 'layout-doc')
            ->nest('head', $this->config['auth']['loginView']['head'])
            ->nest('child', $this->package_name . '::' . 'share.' . $page)
            ->nest('footer', $this->config['auth']['loginView']['footer']);
    }

    public function subpage($root, $subpage){
        $doc = QuestionXML\Census::where('dir', $this->root)->first();
        View::share('doc', $doc);
        return View::make($this->package_name . '::' . 'layout-doc')
            ->nest('child', 'ques.data.' . $root . '.subpage.' . $subpage);
    }

    public function publicData($root, $data) {
        $doc = QuestionXML\Census::where('dir', $this->root)->first();
        return $this->config['publicData']($data);
    }

}