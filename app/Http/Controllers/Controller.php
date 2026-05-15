<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Config;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $defaultLang;

    public function __construct() 
    {
        $this->defaultLang = 1;
        Config::set('defaultLang', $this->defaultLang);

    }
}
