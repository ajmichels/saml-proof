<?php

namespace xsaml;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

class Logger extends Monolog
{

    public function __construct($name, $path)
    {
        // create a log channel
        parent::__construct($name);
        $this->pushHandler(new StreamHandler($path, Monolog::DEBUG));
    }

}
