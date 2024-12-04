<?php

namespace App\Domain;

class ExampleJob
{

    public function run (array $params = [])
    {
     logger('ExampleJob is running', $params);
    }

}
