<?php


namespace JP\Framwork\Elements;
if (!defined('ABSPATH')) {
    exit;
}


// L'objet Job
class jpJobs
{

//    $job_experience
//    $job_skills
//    $job_employer_id

    private $post;
    public function __construct(\WP_Post $post) {
        $this->post = $post;
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
        $meta_value = get_post_meta($this->post->ID, $name, false);
        return $meta_value;
    }

    public function __set($name, $value)
    {
        if ('post' === $name) return;
        $this->{$name} = $value;
    }

    /**
     * @return \WP_Post
     */
    public function get_post() {
        return $this->post;
    }

    public function get_employer() {
        $employer = get_user_by('ID', $this->employer_id);
        if (!$employer) return false;
        return $employer;

    }

}

// Gestionnaire
final class JobHandler {
    public function __construct()
    {
    }
}


final class JobModel {

}