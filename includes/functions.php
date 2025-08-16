<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Inject the form into the content.
 */
function newsopbl_inject_form( $content ) {
    $options = get_option( 'newsopbl_settings' );
    $form_id = isset( $options['newsopbl_form_id'] ) ? $options['newsopbl_form_id'] : '';
    $threshold = isset( $options['newsopbl_injection_threshold'] ) ? intval( $options['newsopbl_injection_threshold'] ) : 60;
    $split_mode = isset( $options['newsopbl_split_mode'] ) ? $options['newsopbl_split_mode'] : 'paragraphs';

    if ( ! is_single() || empty( $form_id ) ) {
        return $content;
    }

    $shortcode = '[contact-form-7 id="' . esc_attr( $form_id ) . '"]';
    $inject_bottom_short = isset($options['newsopbl_inject_bottom_short']) ? $options['newsopbl_inject_bottom_short'] : 0;
    $word_count = str_word_count( wp_strip_all_tags( $content ) );

    if ( $inject_bottom_short && $word_count < 300 ) {
        // Injecte en bas de l'article
        $content .= '\n' . $shortcode;
    } else {
        if ( $split_mode === 'paragraphs' ) {
            $paragraphs = explode( '</p>', $content );
            $count = count( $paragraphs );
            $injection_point = floor( $count * ( $threshold / 100 ) );

            if ( $count > 1 ) {
                array_splice( $paragraphs, $injection_point, 0, $shortcode );
                $content = implode( '</p>', $paragraphs );
            }
        } else {
            $words = explode( ' ', $content );
            $count = count( $words );
            $injection_point = floor( $count * ( $threshold / 100 ) );

            if ( $count > 1 ) {
                array_splice( $words, $injection_point, 0, $shortcode );
                $content = implode( ' ', $words );
            }
        }
    }
    return $content;
}

/**
 * Handle the form submission.
 */
function newsopbl_handle_submission( $contact_form ) {
    global $wpdb, $newsopbl_mailjet_error_message;

    $options = get_option( 'newsopbl_settings' );
    $target_form_id = isset( $options['newsopbl_form_id'] ) ? absint( $options['newsopbl_form_id'] ) : 0;
    if ( ! $target_form_id ) {
        return;
    }

    // Vérifie si le formulaire soumis est bien celui configuré
    if ( method_exists( $contact_form, 'id' ) && $contact_form->id() != $target_form_id ) {
        return;
    }

    $submission = WPCF7_Submission::get_instance();
    if ( $submission ) {
        $posted_data = $submission->get_posted_data();
        $email = isset( $posted_data['mailjetemail'] ) ? sanitize_email( $posted_data['mailjetemail'] ) : '';
        $name = isset( $posted_data['mailjetname'] ) ? sanitize_text_field( $posted_data['mailjetname'] ) : '';

        if ( ! empty( $email ) ) {
            $api_key = isset( $options['newsopbl_api_key'] ) ? $options['newsopbl_api_key'] : '';
            $api_secret = isset( $options['newsopbl_api_secret'] ) ? $options['newsopbl_api_secret'] : '';
            $list_id = isset( $options['newsopbl_list_id'] ) ? $options['newsopbl_list_id'] : '';

            if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
                // Création du contact (si pas déjà existant)
                $contact_url = 'https://api.mailjet.com/v3/REST/contact';
                $contact_body = array(
                    'IsExcludedFromCampaigns' => true,
                    'Name' => $name,
                    'Email' => $email
                );
                $args = array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret),
                        'Content-Type' => 'application/json',
                    ),
                    'body' => wp_json_encode($contact_body),
                    'timeout' => 10,
                    'method' => 'POST',
                );
                $response = wp_remote_post($contact_url, $args);
                if ( is_wp_error($response) ) {
                    $newsopbl_mailjet_error_message = 'Erreur Mailjet : ' . $response->get_error_message();
                } else {
                    $code = wp_remote_retrieve_response_code($response);
                    if ( $code !== 201 && $code !== 400 ) { // 400 = contact déjà existant
                        $newsopbl_mailjet_error_message = 'Erreur Mailjet : ' . $code . ' - ' . wp_remote_retrieve_body($response);
                    }
                }
                // Ajout à la liste
                if ( ! empty( $list_id ) ) {
                    $list_url = 'https://api.mailjet.com/v3/REST/contactslist/' . urlencode($list_id) . '/managecontact';
                    $list_body = array(
                        'Email' => $email,
                        'Name' => $name,
                        'Action' => 'addforce',
                    );
                    $args_list = array(
                        'headers' => array(
                            'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret),
                            'Content-Type' => 'application/json',
                        ),
                        'body' => wp_json_encode($list_body),
                        'timeout' => 10,
                        'method' => 'POST',
                    );
                    $list_response = wp_remote_post($list_url, $args_list);
                    if ( is_wp_error($list_response) ) {
                        $newsopbl_mailjet_error_message = 'Erreur ajout liste Mailjet : ' . $list_response->get_error_message();
                    } else {
                        $list_code = wp_remote_retrieve_response_code($list_response);
                        if ( $list_code !== 201 && $list_code !== 400 ) {
                            $newsopbl_mailjet_error_message = 'Erreur ajout liste Mailjet : ' . $list_code . ' - ' . wp_remote_retrieve_body($list_response);
                        }
                    }
                }
            }

            $table_name = $wpdb->prefix . 'newsopbl_submissions';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table insert required for plugin functionality
            $wpdb->insert(
                $table_name,
                array(
                    'email' => $email,
                    'submission_date' => current_time( 'mysql' ),
                    'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
                )
            );
        }
    }
}

/**
 * Add the Mailjet error to the AJAX response if it exists.
 */
function newsopbl_add_mailjet_error_to_response( $response, $result ) {
    global $newsopbl_mailjet_error_message;

    if ( ! empty( $newsopbl_mailjet_error_message ) ) {
        $response['mailjet_error'] = $newsopbl_mailjet_error_message;
    }

    return $response;
}
add_filter( 'wpcf7_ajax_json_echo', 'newsopbl_add_mailjet_error_to_response', 10, 2 );

/**
 * Enqueue the scripts.
 */
function newsopbl_enqueue_scripts() {
    wp_enqueue_script(
        'newsopbl-main-js',
        plugin_dir_url( __FILE__ ) . '../public/js/main.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    $options = get_option( 'newsopbl_settings' );
    $thank_you_message = isset( $options['newsopbl_thank_you_message'] ) ? $options['newsopbl_thank_you_message'] : '';

    wp_localize_script( 'newsopbl-main-js', 'newsopbl_vars', array(
        'thank_you_message' => $thank_you_message,
    ) );
}
