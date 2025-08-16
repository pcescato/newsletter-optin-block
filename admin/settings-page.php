<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add the admin menu page.
 */
function newsopbl_add_admin_menu() {
    add_options_page(
        __( 'Formulaire Auto-injecté', 'newsletter-optin-block' ),
        __( 'Formulaire Auto-injecté', 'newsletter-optin-block' ),
        'manage_options',
        'newsletter-optin-block',
        'newsopbl_options_page_html'
    );
}
add_action( 'admin_menu', 'newsopbl_add_admin_menu' );

/**
 * Register the settings.
 */
function newsopbl_sanitize_settings( $input ) {
    $output = array();
    $output['newsopbl_inject_bottom_short'] = isset($input['newsopbl_inject_bottom_short']) ? 1 : 0;
    $output['newsopbl_activate'] = isset($input['newsopbl_activate']) ? 1 : 0;
    $output['newsopbl_api_key'] = isset($input['newsopbl_api_key']) ? sanitize_text_field($input['newsopbl_api_key']) : '';
    $output['newsopbl_api_secret'] = isset($input['newsopbl_api_secret']) ? sanitize_text_field($input['newsopbl_api_secret']) : '';
    $output['newsopbl_list_id'] = isset($input['newsopbl_list_id']) ? sanitize_text_field($input['newsopbl_list_id']) : '';
    $output['newsopbl_form_id'] = isset($input['newsopbl_form_id']) ? absint($input['newsopbl_form_id']) : '';
    $output['newsopbl_thank_you_message'] = isset($input['newsopbl_thank_you_message']) ? wp_kses_post($input['newsopbl_thank_you_message']) : '';
    $output['newsopbl_injection_threshold'] = isset($input['newsopbl_injection_threshold']) ? min(100, max(0, intval($input['newsopbl_injection_threshold']))) : 60;
    $output['newsopbl_split_mode'] = 'paragraphs';
    return $output;
}

function newsopbl_register_settings() {
    register_setting( 'newsopbl_settings', 'newsopbl_settings', 'newsopbl_sanitize_settings' );
}
add_action( 'admin_init', 'newsopbl_register_settings' );

/**
 * The HTML for the options page.
 */
function newsopbl_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = get_option( 'newsopbl_settings' );
    // Préparation pour affichage du select des listes Mailjet
    $mailjet_lists = array();
    $mailjet_error = '';
    $api_key = isset($options['newsopbl_api_key']) ? trim($options['newsopbl_api_key']) : '';
    $api_secret = isset($options['newsopbl_api_secret']) ? trim($options['newsopbl_api_secret']) : '';
    if ( !empty($api_key) && !empty($api_secret) ) {
        $url = 'https://api.mailjet.com/v3/REST/contactslist?limit=100';
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret),
            ),
            'timeout' => 10,
        );
        $response = wp_remote_get($url, $args);
        if ( is_wp_error($response) ) {
            $mailjet_error = 'Erreur Mailjet : ' . esc_html($response->get_error_message());
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if ($code == 200 && isset($data['Data'])) {
                foreach ($data['Data'] as $list) {
                    $mailjet_lists[$list['ID']] = $list['Name'] . ' (ID: ' . $list['ID'] . ')';
                }
            } else {
                $mailjet_error = 'Erreur Mailjet : ' . esc_html($code) . ' ' . (isset($data['ErrorMessage']) ? esc_html($data['ErrorMessage']) : $body);
            }
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <?php if ( !empty($mailjet_error) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html($mailjet_error); ?></p></div>
        <?php endif; ?>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'newsopbl_settings' );
            do_settings_sections( 'newsopbl_settings' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Injecter en bas si moins de 300 mots', 'newsletter-optin-block' ); ?></th>
                    <td>
                        <input type="checkbox" name="newsopbl_settings[newsopbl_inject_bottom_short]" value="1" <?php checked( isset( $options['newsopbl_inject_bottom_short'] ) ? $options['newsopbl_inject_bottom_short'] : 0, 1 ); ?> />
                        <span class="description"><?php esc_html_e( 'Si coché, le formulaire sera injecté en bas de l’article si celui-ci contient moins de 300 mots.', 'newsletter-optin-block' ); ?></span>
                    </td>
                </tr>
                <?php
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = get_option( 'newsopbl_settings' );
    // Préparation pour affichage du select des listes Mailjet
    $mailjet_lists = array();
    $mailjet_error = '';
    $api_key = isset($options['newsopbl_api_key']) ? trim($options['newsopbl_api_key']) : '';
    $api_secret = isset($options['newsopbl_api_secret']) ? trim($options['newsopbl_api_secret']) : '';
    if ( !empty($api_key) && !empty($api_secret) ) {
        $url = 'https://api.mailjet.com/v3/REST/contactslist?limit=100';
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret),
            ),
            'timeout' => 10,
        );
        $response = wp_remote_get($url, $args);
        if ( is_wp_error($response) ) {
            $mailjet_error = 'Erreur Mailjet : ' . esc_html($response->get_error_message());
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if ($code == 200 && isset($data['Data'])) {
                foreach ($data['Data'] as $list) {
                    $mailjet_lists[$list['ID']] = $list['Name'] . ' (ID: ' . $list['ID'] . ')';
                }
            } else {
                $mailjet_error = 'Erreur Mailjet : ' . esc_html($code) . ' ' . (isset($data['ErrorMessage']) ? esc_html($data['ErrorMessage']) : $body);
            }
        }
    }
    ?>
    <div class="wrap">
        <?php if ( !empty($mailjet_error) ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html($mailjet_error); ?></p></div>
        <?php endif; ?>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'newsopbl_settings' );
            do_settings_sections( 'newsopbl_settings' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Activer le plugin', 'newsletter-optin-block' ); ?></th>
                    <td>
                        <input type="checkbox" name="newsopbl_settings[newsopbl_activate]" value="1" <?php checked( isset( $options['newsopbl_activate'] ) ? $options['newsopbl_activate'] : 0, 1 ); ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('API Key', 'newsletter-optin-block'); ?></th>
                    <td><input type="text" name="newsopbl_settings[newsopbl_api_key]" value="<?php echo esc_attr( isset( $options['newsopbl_api_key'] ) ? $options['newsopbl_api_key'] : '' ); ?>"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('API Secret', 'newsletter-optin-block'); ?></th>
                    <td><input type="text" name="newsopbl_settings[newsopbl_api_secret]" value="<?php echo esc_attr( isset( $options['newsopbl_api_secret'] ) ? $options['newsopbl_api_secret'] : '' ); ?>"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Liste Mailjet', 'newsletter-optin-block'); ?></th>
                    <td>
                        <?php if ( !empty($mailjet_lists) ) : ?>
                            <select name="newsopbl_settings[newsopbl_list_id]">
                                <option value="">-- Sélectionner une liste --</option>
                                <?php foreach ($mailjet_lists as $id => $label) : ?>
                                    <option value="<?php echo esc_attr($id); ?>" <?php selected( isset($options['newsopbl_list_id']) ? $options['newsopbl_list_id'] : '', $id ); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else : ?>
                            <input type="text" name="newsopbl_settings[newsopbl_list_id]" value="<?php echo esc_attr( isset( $options['newsopbl_list_id'] ) ? $options['newsopbl_list_id'] : '' ); ?>" placeholder="ID de la liste Mailjet" />
                            <br><small>Renseignez vos clés API et enregistrez pour choisir une liste.</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Contact Form 7 Form', 'newsletter-optin-block' ); ?></th>
                    <td>
                        <select name="newsopbl_settings[newsopbl_form_id]">
                            <?php
                            $forms = get_posts( array(
                                'post_type' => 'wpcf7_contact_form',
                                'posts_per_page' => -1,
                            ) );

                            foreach ( $forms as $form ) {
                                ?>
                                <option value="<?php echo esc_attr( $form->ID ); ?>" <?php selected( isset( $options['newsopbl_form_id'] ) ? $options['newsopbl_form_id'] : '', $form->ID ); ?>><?php echo esc_html( $form->post_title ); ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Message de remerciement', 'newsletter-optin-block' ); ?></th>
                    <td>
                        <textarea name="newsopbl_settings[newsopbl_thank_you_message]" rows="5" cols="50"><?php echo esc_textarea( isset( $options['newsopbl_thank_you_message'] ) ? $options['newsopbl_thank_you_message'] : '' ); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Seuil d\'insertion (%)', 'newsletter-optin-block' ); ?></th>
                    <td>
                        <input type="number" name="newsopbl_settings[newsopbl_injection_threshold]" value="<?php echo esc_attr( isset( $options['newsopbl_injection_threshold'] ) ? $options['newsopbl_injection_threshold'] : '60' ); ?>" min="0" max="100" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
