<?php
if (!defined('ABSPATH')) {
    exit;
}

function save_extra_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    /* Edit the following lines according to your set fields */
    $public_cv = isset($_POST['public_cv']) ? $_POST['public_cv'] : 0;
    update_user_meta( $user_id, 'phone', $_POST['phone'] );
    update_user_meta( $user_id, 'public_cv', intval($public_cv));
}
add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );

add_action( 'show_user_profile', 'crf_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'crf_show_extra_profile_fields' );
function crf_show_extra_profile_fields( $user ) {

    ?>
    <h3>Extra profile information</h3>
    <table class="form-table">
        <tr>
            <th><label for="city"><?php _e("Ville"); ?></label></th>
            <td>
                <input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
            <th><label for="address"><?php _e("Address"); ?></label></th>
            <td>
                <input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
        <tr>
            <th><label for="phone"><?php _e("Numéro de téléphone"); ?></label></th>
            <td>
                <input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
            </td>
        </tr>
    </table>
<?php
    if (in_array('candidate', $user->roles)):
        $public_cv = get_the_author_meta( 'public_cv', $user->ID ); // boolean number value 1 or 0
        $public_cv = intval($public_cv);
        var_dump($public_cv);
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
        </table>
<?php
    endif;
}