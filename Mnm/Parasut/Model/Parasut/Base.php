<?php

namespace Mnm\Parasut\Model\Parasut;

class Base
{
    public $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}