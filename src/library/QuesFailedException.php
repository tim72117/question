<?php
namespace app\library\v10;

class QuesFailedException extends \Exception {

    public $QuesFailedMessage;

    public function __construct($QuesFailedMessage)
    {
        $this->QuesFailedMessage = $QuesFailedMessage;
    }

}