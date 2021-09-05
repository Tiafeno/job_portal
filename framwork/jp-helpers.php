<?php
if (!defined('ABSPATH')) {
    exit;
}

trait Configs {
    public function get_app_configs() {
        $directory = trailingslashit( get_template_directory_uri() );
        // Make the request
        $request = wp_remote_get( $directory . 'configs/schema.json' );
        // If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
        if( is_wp_error( $request ) ) {
            return false; // Bail early
        }
        // Retrieve the data
        return json_decode( wp_remote_retrieve_body( $request ) );
    }
}

class jpHelpers {
    use Configs;
    public function __construct() {}
    public static function getInstance() {
        return new self();
    }
    public static function getValue( $name, $def = false ) {
        if ( ! isset( $name ) || empty( $name ) || ! is_string( $name ) ) {
            return $def;
        }
        $returnValue = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : ( isset( $_GET[ $name ] ) ? trim( $_GET[ $name ] ) : $def );
        $returnValue = urldecode( preg_replace( '/((\%5C0+)|(\%00+))/i', '', urlencode( $returnValue ) ) );
        return ! is_string( $returnValue ) ? $returnValue : stripslashes( $returnValue );
    }
    // Don't touch it
    public function get_user_json_meta_values(WP_User $user, $meta_value) {
        $user_term = get_the_author_meta($meta_value, $user->ID);
        $user_term_ids = empty($user_term) ? [] : json_decode($user_term, false);
        return  array_values($user_term_ids);
    }

}

function save_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) :
        return false;
    endif;
    /* Edit the following lines according to your set fields */
    $public_cv = jpHelpers::getValue('public_cv') ? jpHelpers::getValue('public_cv') : 0;
    update_user_meta( $user_id, 'phone', jpHelpers::getValue('phone', '') );
    update_user_meta( $user_id, 'address', jpHelpers::getValue('address', '') );
    update_user_meta( $user_id, 'city', jpHelpers::getValue('city', '') );
    update_user_meta( $user_id, 'public_cv', intval($public_cv));
    // TODO: Update profil, language and categories meta value
}
add_action( 'personal_options_update', 'save_profile_fields' );
add_action( 'edit_user_profile_update', 'save_profile_fields' );

add_action( 'show_user_profile', 'crf_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'crf_show_extra_profile_fields' );
function crf_show_extra_profile_fields( $user ) {
    global $Liquid_engine;
    echo $Liquid_engine->parseFile('admin/extra-profil-information')->render([
            'city' =>  esc_attr(get_the_author_meta('city', $user->ID)),
            'address' =>  esc_attr(get_the_author_meta('address', $user->ID)),
            'phone' =>  esc_attr(get_the_author_meta('phone', $user->ID))
    ]);
    if (in_array('candidate', $user->roles)):
        $languages = get_terms(['taxonomy' => 'language', 'hide_empty' => false, 'number' => 50]);
        $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'number' => 50]);
        $public_cv = get_the_author_meta( 'public_cv', $user->ID ); // boolean number value 1 or 0
        $public_cv = intval($public_cv);
        $biographie = get_the_author_meta('profil', $user->ID);
        // categories and languages
        $user_ctg_ids = jpHelpers::getInstance()->get_user_json_meta_values($user, 'categories');
        $user_lang_ids = jpHelpers::getInstance()->get_user_json_meta_values($user, 'languages');
        // Experiences and educations
        $experiences = jpHelpers::getInstance()->get_user_json_meta_values($user, 'experiences');
        $educations = jpHelpers::getInstance()->get_user_json_meta_values($user, 'educations');

        ?>
        <table class="form-table">
            <tr>
                <th><label for="public_cv"><?php _e("Publier CV"); ?></label></th>
                <td>
                    <label for="rich_editing">
                        <input name="public_cv" type="checkbox" id="public_cv" value="1" <?= $public_cv === 1 ? "checked='checked'" : '' ?> >
                        Activer le CV pour le publique
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="categories"><?php _e("Emploi recherché ou métier"); ?></label></th>
                <td>
                    <select name="categories[]" multiple>
                        <?php

                        foreach ($categories as $index => $categorie) {
                            $checked = "selected='true'";
                            $attr = in_array($categorie->term_id, array_values($user_ctg_ids), false) ? $checked : '';
                            ?>
                            <option value="<?= $categorie->term_id ?>" <?= $attr ?>><?= $categorie->name ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="city"><?php _e("Langage(s)"); ?></label></th>
                <td>
                    <select name="languages[]" multiple>
                        <?php
                        foreach ($languages as $language) {
                            $checked = "selected='true'";
                            $attr = in_array($language->term_id, array_values($user_lang_ids), false) ? $checked : '';
                            ?>
                            <option value="<?= $language->term_id ?>" <?= $attr ?>><?= $language->name ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
<?php
    endif;
}