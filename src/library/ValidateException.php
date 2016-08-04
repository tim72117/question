<?php
namespace app\library\files\v0;

use Exception;

class ValidateException extends Exception {

    public $validator;

    public function __construct($validator = Null)
    {
        $this->validator = $validator;
    }

}