<?php

namespace jobLogin\Widgets;

use Elementor\Widget_Base;

class jobLogin_Widget extends Widget_Base
{
    public static $name = 'job-login';

    public function get_name()
    {
        return self::$name;
    }

    public function get_title()
    {
        return 'Job login';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function render()
    {
        global $Liquid_engine;
        $nonce = wp_create_nonce('jp-login-action');
        echo $Liquid_engine->parseFile('job-login')->render(['nonce' => $nonce, 'msg' => '']);
    }
}

