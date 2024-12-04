<?php

namespace App\Domain\Exceptions;

class JobRunnerException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }



}
