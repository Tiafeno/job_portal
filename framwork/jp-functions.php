<?php

add_action('wp_enqueue_scripts', function() {
    // style
    wp_register_style('jp-bootstrap', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/css/bootstrap.min.css');
    wp_register_style('jp-bootstrap-select', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/css/bootstrap-select.min.css');
    wp_register_style('jp-icons', get_stylesheet_directory_uri() . '/assets/plugins/icons/css/icons.css');
    wp_register_style('jp-animate', get_stylesheet_directory_uri() . '/assets/plugins/animate/animate.css');
    wp_register_style('jp-bootnav', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/css/bootsnav.css');
    //wp_register_style('jp-nice-select', get_stylesheet_directory_uri() . '/assets/plugins/nice-select/css/nice-select.css');
    wp_register_style('jp-aos', get_stylesheet_directory_uri() . '/assets/plugins/aos-master/aos.css');
    wp_register_style('jp-responsive', get_stylesheet_directory_uri() . '/assets/css/responsive.css');
    wp_register_style('semantic-dropdown', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/dropdown.min.css');
    wp_register_style('semantic-transition', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/transition.min.css');
    wp_enqueue_style('alertify', get_stylesheet_directory_uri() . '/assets/plugins/alertify/css/alertify.css');
    wp_enqueue_style('style-name', get_stylesheet_uri(), [
        'elementor-frontend',
        'jp-bootstrap',
        'jp-bootstrap-select',
        'jp-icons',
        'jp-animate',
        'jp-bootnav',
        //'jp-nice-select',
        'semantic-dropdown',
        'semantic-transition',
        'jp-aos',
    ]);
    wp_enqueue_style('jp-responsive');
    // script
    wp_register_script('jquery-validate', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js', ['jquery']);
    wp_register_script('jp-bootstrap', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/js/bootstrap.min.js', ['jquery'], '1.0.0', true);
    wp_register_script('jp-bootsnav', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/js/bootsnav.js', ['jquery', 'jp-bootstrap'], '1.0.0', true);
    wp_register_script('jp-viewportchecker', get_stylesheet_directory_uri() . '/assets/js/viewportchecker.js', ['jquery'], '1.0.0', true);
    wp_register_script('jp-slick', get_stylesheet_directory_uri() . '/assets/js/slick.js', ['jquery'], '1.0.0', true);
    wp_register_script('jp-wysihtml', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/js/wysihtml5-0.3.0.js', ['jquery', 'jp-bootstrap'], '1.0.0', true);
    wp_register_script('jp-bootstrap-wysihtml5', get_stylesheet_directory_uri() . '/assets/plugins/bootstrap/js/bootstrap-wysihtml5.js', ['jquery', 'jp-bootstrap'], '1.0.0', true);
    wp_register_script('jp-aos', get_stylesheet_directory_uri() . '/assets/plugins/aos-master/aos.js', ['jquery'], '1.0.0', true);
    //wp_register_script('jp-jquery-nice', get_stylesheet_directory_uri() . '/assets/plugins/nice-select/js/jquery.nice-select.min.js', ['jquery'], '1.0.0', true);
    wp_register_script('vuejs', get_stylesheet_directory_uri() . '/assets/js/vuejs/vue.js', [], '2.5.16', true); // dev
    wp_register_script('wpapi', get_stylesheet_directory_uri() . '/assets/js/wpapi/wpapi.js', [], null, true); // dev
    wp_register_script('axios', get_stylesheet_directory_uri() . '/assets/js/axios.min.js', [], null, true); // dev
    wp_register_script('semantic-dropdown', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/dropdown.min.js', ['jquery'], null, true);
    wp_register_script('semantic-transition', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/transition.min.js', ['jquery'], null, true);
    wp_register_script('jp-custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', [
        'jquery',
        'jp-bootstrap',
        'jp-bootsnav',
        'jp-viewportchecker',
        'jp-slick',
        'jp-wysihtml',
        'jp-bootstrap-wysihtml5',
        'jp-aos',
        'semantic-dropdown',
        'semantic-transition',
        'jquery-validate',
        'wpapi',
        'lodash',
        'axios'
    ], '1.0.1', true);
    wp_register_script('comp-login', get_stylesheet_directory_uri() . '/assets/js/component-login.js', ['vuejs', 'wpapi', 'axios', 'lodash'], null, true);
    wp_localize_script('comp-login', 'com_login_params', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce_field' => wp_create_nonce('ajax-login-nonce')
    ]);
    wp_enqueue_script('alertify', get_stylesheet_directory_uri() . '/assets/plugins/alertify/alertify.min.js', ['jquery'], null, true);
    wp_enqueue_script('jp-custom');


});

add_filter('body_class', function ($classes) {
    $classes[] = 'utf_skin_area';
    return $classes;
});

add_action('rest_api_init', function() {

    // Employer
    register_meta('user', 'company_id', [
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true
    ]);
});


class JP_Primary_Walker extends Walker_Nav_Menu {
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
    }
    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;


        /*if the current item has children, append the dropdown class*/
        if ( $args->has_children )
            $class_names .= ' dropdown';

        /*if there aren't any class names, don't show class attribute*/
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';


        $output .= $indent . '<li' . $id . $value . $class_names .'>';

        $atts = array();
        $atts['title']  = ! empty( $item->title )	? $item->title	: '';
        $atts['target'] = ! empty( $item->target )	? $item->target	: '';
        $atts['rel']    = ! empty( $item->xfn )		? $item->xfn	: '';


        /*if the current menu item has children and it's the parent, set the dropdown attributes*/
        if ( $args->has_children && $depth === 0 ) {
            $atts['href']   		= '#';
            $atts['data-toggle']	= 'dropdown';
            $atts['class']			= 'dropdown-toggle';
        } else {
            $atts['href'] = ! empty( $item->url ) ? $item->url : '';
        }

        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $item_output = $args->before;

        $item_output .= '<a'. $attributes .'>';

        $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

        /*	if the current menu item has children and it's the parent item, append the fa-angle-down icon*/
        $item_output .= ( $args->has_children && $depth === 0 ) ? '</a>' : '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

    }
    public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if ( ! $element )
            return;

        $id_field = $this->db_fields['id'];

        if ( is_object( $args[0] ) )
            $args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
}