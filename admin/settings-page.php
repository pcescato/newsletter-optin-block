<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add the admin menu page.
 */
function fai_add_admin_menu() {
    add_options_page(
        __( 'Formulaire Auto-injecté', 'formulaire-auto-injecte' ),
        __( 'Formulaire Auto-injecté', 'formulaire-auto-injecte' ),
        'manage_options',
        'formulaire-auto-injecte',
        'fai_options_page_html'
    );
}
add_action( 'admin_menu', 'fai_add_admin_menu' );

/**
 * Register the settings.
 */
function fai_register_settings() {
    register_setting( 'fai_settings', 'fai_settings' );
}
add_action( 'admin_init', 'fai_register_settings' );

/**
 * The HTML for the options page.
 */
function fai_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = get_option( 'fai_settings' );
    // Préparation pour affichage du select des listes Mailjet
    $mailjet_lists = array();
    $api_key = isset($options['fai_api_key']) ? trim($options['fai_api_key']) : '';
    $api_secret = isset($options['fai_api_secret']) ? trim($options['fai_api_secret']) : '';
    if ( !empty($api_key) && !empty($api_secret) ) {
        try {
            if ( ! class_exists('Mailjet\\Client') ) {
                require_once dirname(__FILE__) . '/../vendor/autoload.php';
            }
            $mj = new \Mailjet\Client($api_key, $api_secret, true, ['version' => 'v3']);
            $response = $mj->get(\Mailjet\Resources::$Contactslist, ['limit' => 100]);
            if ($response->success()) {
                $lists = $response->getData();
                foreach ($lists as $list) {
                    $mailjet_lists[$list['ID']] = $list['Name'] . ' (ID: ' . $list['ID'] . ')';
                }
            }
        } catch (\Exception $e) {
            // On ignore l'erreur, le champ sera un input texte fallback
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'fai_settings' );
            do_settings_sections( 'fai_settings' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Activer le plugin', 'formulaire-auto-injecte' ); ?></th>
                    <td>
                        <input type="checkbox" name="fai_settings[fai_activate]" value="1" <?php checked( isset( $options['fai_activate'] ) ? $options['fai_activate'] : 0, 1 ); ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="fai_settings[fai_api_key]" value="<?php echo esc_attr( isset( $options['fai_api_key'] ) ? $options['fai_api_key'] : '' ); ?>"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Secret</th>
                    <td><input type="text" name="fai_settings[fai_api_secret]" value="<?php echo esc_attr( isset( $options['fai_api_secret'] ) ? $options['fai_api_secret'] : '' ); ?>"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Liste Mailjet</th>
                    <td>
                        <?php if ( !empty($mailjet_lists) ) : ?>
                            <select name="fai_settings[fai_list_id]">
                                <option value="">-- Sélectionner une liste --</option>
                                <?php foreach ($mailjet_lists as $id => $label) : ?>
                                    <option value="<?php echo esc_attr($id); ?>" <?php selected( isset($options['fai_list_id']) ? $options['fai_list_id'] : '', $id ); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else : ?>
                            <input type="text" name="fai_settings[fai_list_id]" value="<?php echo esc_attr( isset( $options['fai_list_id'] ) ? $options['fai_list_id'] : '' ); ?>" placeholder="ID de la liste Mailjet" />
                            <br><small>Renseignez vos clés API et enregistrez pour choisir une liste.</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Contact Form 7 Form', 'formulaire-auto-injecte' ); ?></th>
                    <td>
                        <select name="fai_settings[fai_form_id]">
                            <?php
                            $forms = get_posts( array(
                                'post_type' => 'wpcf7_contact_form',
                                'posts_per_page' => -1,
                            ) );

                            foreach ( $forms as $form ) {
                                ?>
                                <option value="<?php echo esc_attr( $form->ID ); ?>" <?php selected( isset( $options['fai_form_id'] ) ? $options['fai_form_id'] : '', $form->ID ); ?>><?php echo esc_html( $form->post_title ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Message de remerciement', 'formulaire-auto-injecte' ); ?></th>
                    <td>
                        <textarea name="fai_settings[fai_thank_you_message]" rows="5" cols="50"><?php echo esc_textarea( isset( $options['fai_thank_you_message'] ) ? $options['fai_thank_you_message'] : '' ); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Seuil d\'insertion (%)', 'formulaire-auto-injecte' ); ?></th>
                    <td>
                        <input type="number" name="fai_settings[fai_injection_threshold]" value="<?php echo esc_attr( isset( $options['fai_injection_threshold'] ) ? $options['fai_injection_threshold'] : '60' ); ?>" min="0" max="100" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Mode de découpage', 'formulaire-auto-injecte' ); ?></th>
                    <td>
                        <select name="fai_settings[fai_split_mode]">
                            <option value="paragraphs" <?php selected( isset( $options['fai_split_mode'] ) ? $options['fai_split_mode'] : 'paragraphs', 'paragraphs' ); ?>><?php _e( 'Paragraphes', 'formulaire-auto-injecte' ); ?></option>
                            <option value="words" <?php selected( isset( $options['fai_split_mode'] ) ? $options['fai_split_mode'] : '', 'words' ); ?>><?php _e( 'Mots', 'formulaire-auto-injecte' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
