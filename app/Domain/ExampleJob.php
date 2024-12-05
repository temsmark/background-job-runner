<?php

namespace App\Domain;

class ExampleJob
{


    public function run (array $params = []): void
    {
     logger('ExampleJob is running', $params);
    }

}
