<?php
namespace Ques;

use Session;

class Answerer {

    public static function login($root, $stdidnuber)
    {
        Session::set('ques.root', $root);
        Session::set('ques.'.$root.'.login', true);
        Session::set('ques.'.$root.'.newcid', $stdidnuber);
    }

    public static function check($root)
    {
        return Session::get('ques.'.$root.'.login', false);
    }

    public static function newcid()
    {
        $root = Session::get('ques.root');
        return Session::get('ques.'.$root.'.newcid');
    }

}