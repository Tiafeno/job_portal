<?php
namespace JobGrid\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;

class JobGrid_Widget extends Widget_Base
{
    public static $slug = 'job-grid';

    public function get_name()
    {
        return self::$slug;
    }

    public function get_title()
    {
        return 'Job Grid';
    }

    /**
     * Retrieve button widget icon.
     *
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'fas fa-newspaper';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Options', self::$slug),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'widget_value',
            [
                'label' => __('value', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('value', self::$slug),
                'placeholder' => __('Value Attribute', self::$slug),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render button widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @access protected
     */
    protected function render()
    {
        global $Liquid_engine;
        echo $Liquid_engine->parseFile('job-grid')->render([]);
    }
}


