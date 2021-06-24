<?php


namespace JP\Framwork\Elements;
if (!defined('ABSPATH')) {
    exit;
}


// L'objet Job
class jpJobs
{


    private $post;
    public $ID = 0;

    public function __construct(\WP_Post $post)
    {
        $this->post = $post;
        $this->ID = $this->post->ID;
    }

    /**
     * @param $name - property
     * @return array|mixed
     */
    public function __get($name)
    {
        if (isset($this->post->{$name})):
            return $this->post->{$name};
        endif;
        $meta_value = get_post_meta($this->post->ID, $name, true);
        return $meta_value;
    }

    public function __set($name, $value)
    {
        if ('post' === $name) return;
        $this->{$name} = $value;
    }

    public function get_reset_term($taxonomy_slug)
    {
        $empty_class = new \stdClass();
        $terms = wp_get_post_terms($this->ID, $taxonomy_slug);
        if (empty($terms)) {
            $empty_class->name = "Undefined";
            $empty_class->slug = "undefined";
            $empty_class->term_id = 0;
            return $empty_class;
        }
        return reset($terms);
    }

    /**
     * @return \WP_Post
     */
    public function get_post()
    {
        return $this->post;
    }

    public function get_employer()
    {
        $employer = get_user_by('ID', $this->employer_id);
        if (!$employer) return false;
        return $employer;

    }

}

// Gestionnaire
final class JobHandler
{
    public function __construct()
    {
    }
}


final class JobModel
{

}