<?php
if (!defined('ABSPATH')) {
    exit;
}

class wtsocial_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct('wtsocial_widget', 'Widget Footer Social Media',
            array('description' => 'Afficher les reseaux sociaux sur le footer',)
        );
    }

    // Creating widget front-end
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        $description = $instance['desc'];
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        echo '<a href="#"><img class="footer-logo" src="" alt=""></a>';
        if (!empty($title)) echo $args['before_title'] . $title . $args['after_title'];
        // This is where you run the code and display the output
        echo sprintf('<p>%s</p>', $description);
        echo "<div class='f-social-box'>";
        echo " <ul>";
        if (!empty($instance['facebook_link'])) {
            echo sprintf('<li>%s</li>', $instance['facebook_link']);
        }
        if (!empty($instance['twitter_link'])) {
            echo sprintf('<li>%s</li>', $instance['twitter_link']);
        }
        if (!empty($instance['instagram_link'])) {
            echo sprintf('<li>%s</li>', $instance['instagram_link']);
        }
        echo " </ul>";
        echo "</div>";
        echo $args['after_widget'];
    }

    public function generate_field($instance, $field, $title = " ", $type = 'input')
    {
        $field = isset($instance[$field]) ? $instance[$field] : '';
        $field_id = $this->get_field_id($field);
        $field_name = $this->get_field_name($field);
        ?>
        <label for="<?= $field_id ?>"><?= $title ?></label>
        <?php if ($type === 'input'): ?>
        <input class="widefat" type="text" id="<?= $field_id ?>" name="<?= $field_name ?>" value="<?= esc_attr($field) ?>">
    <?php endif; ?>
        <?php if ($type === 'textarea'): ?>
        <textarea class="widefat" id="<?= $field_id ?>" name="<?= $field_name ?>"><?= esc_attr($field) ?></textarea>
    <?php endif; ?>
        <?php
    }

    // Creating widget Backend
    public function form($instance)
    {
        // Widget admin form
        $this->generate_field($instance, 'title', 'Titre');
        $this->generate_field($instance, 'desc', 'Description', 'textarea');
        $this->generate_field($instance, 'facebook_link', 'Facebook');
        $this->generate_field($instance, 'twitter_link', 'Twitter');
        $this->generate_field($instance, 'instagram_link', 'Instagram');
    }

// Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['desc'] = (!empty($new_instance['desc'])) ? $new_instance['desc'] : '';
        $instance['facebook_link'] = (!empty($new_instance['facebook_link']))
            ? esc_url($new_instance['facebook_link']) : '';
        $instance['twitter_link'] = (!empty($new_instance['twitter_link']))
            ? esc_url($new_instance['twitter_link']) : '';
        $instance['instagram_link'] = (!empty($new_instance['instagram_link']))
            ? esc_url($new_instance['instagram_link']) : '';
        return $instance;
    }
}

add_action('widgets_init', function () {
    register_widget('wtsocial_widget');
});