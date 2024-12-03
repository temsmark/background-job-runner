<?php

namespace App\Domain\Runner;

class CustomBackgroundJobRunner
{
    //Ensure that only pre-approved classes can be run in the background for security reasons

    protected $approvedClasses = [
    ];


    public function run($class, $method, $params=[])
    {
        if (!in_array($class, $this->approvedClasses)) {
            throw new \Exception('Class not approved for background job');
        }

        $class = new $class();
        $class->$method($params);
    }

}
