<?php
namespace app\index\controller;

use think\Config;

class Index
{
    public function index()
    {
        
//         var_dump( Config::get() );
        return 'hi, friends.';
    }
}
