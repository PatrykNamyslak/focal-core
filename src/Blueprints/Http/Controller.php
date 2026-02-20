<?php

namespace PatrykNamyslak\FocalCore\Blueprints\Http;


abstract class Controller{
    public function __construct(Request $request){}


    abstract function attempt(): void;


    // Hooks
    protected function beforeAttempt(){}
    protected function afterAttempt(){}
} 