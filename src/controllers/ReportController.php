<?php

use QuestionXML\Census;

class ReportController extends BaseController {

    protected $package_name = 'package';

    public function __construct()
    {
        $this->beforeFilter(function($route) {
            $this->census = Census::find($route->getParameter('census_id'));

            if (! isset($this->census))
                return Response::view($this->package_name . '::' . 'prompt-no', array(), 404);

            Config::addNamespace('ques', app_path() . '/views/ques/data/' . $this->census->dir);
            $this->config = Config::get('ques::setting');
        });
    }
    public function report()
    {
        return View::make($this->package_name . '::' . 'report.report-layout')->nest('form', $this->package_name . '::' . 'report.report-form')->nest('footer', $this->config['auth']['loginView']['footer']);
    }

    public function report_save()
    {
        $input = Input::only('contact', 'report');
        $rulls = array(
            'contact' => 'required|max:50',
            'report' => 'required|max:500',
        );
        $input_rull_message = array(
            'contact.required' => '聯絡方法必填',
            'contact.max' => '請勿超過50個字',
            'report.required' => '問題內容必填',
            'report.max' => '請勿超過500個中文字',
        );
        $validator = Validator::make($input, $rulls, $input_rull_message);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            $explorer = $_SERVER['HTTP_USER_AGENT'];
            DB::table('plat_log.dbo.report')->insert([
                'census_id' => $this->census->id,
                'root'      => $this->census->dir,
                'contact'   => $input['contact'],
                'text'      => $input['report'],
                'explorer'  => $explorer,
                'solve'     => false,
                'time'      => Carbon\Carbon::now()->toDateTimeString(),
            ]);
            View::share('config', $this->config);
            View::share('root', $this->census->dir);
            return View::make($this->package_name . '::' . 'report.report-layout')->nest('form', $this->package_name . '::' . 'report.report-end')->nest('footer', $this->config['auth']['loginView']['footer']);
        }
    }
}
