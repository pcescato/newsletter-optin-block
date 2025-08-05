<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Inject the form into the content.
 */
function fai_inject_form( $content ) {
    $options = get_option( 'fai_settings' );
    $form_id = isset( $options['fai_form_id'] ) ? $options['fai_form_id'] : '';
    $threshold = isset( $options['fai_injection_threshold'] ) ? intval( $options['fai_injection_threshold'] ) : 60;
    $split_mode = isset( $options['fai_split_mode'] ) ? $options['fai_split_mode'] : 'paragraphs';

    if ( ! is_single() || empty( $form_id ) ) {
        return $content;
    }

    $shortcode = '[contact-form-7 id="' . esc_attr( $form_id ) . '"]';

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

    return $content;
}

/**
 * Handle the form submission.
 */
function fai_handle_submission( $contact_form ) {
    global $wpdb, $fai_mailjet_error_message;

    $submission = WPCF7_Submission::get_instance();

    if ( $submission ) {
        $posted_data = $submission->get_posted_data();
        $email = isset( $posted_data['mailjetemail '] ) ? sanitize_email( $posted_data['mailjetemail '] ) : '';
		$name = isset( $posted_data['mailjetname '] ) ? sanitize_text_field( $posted_data['mailjetname '] ) : '';

        if ( ! empty( $email ) ) {
            $options = get_option( 'fai_settings' );
            $api_key = isset( $options['fai_api_key'] ) ? $options['fai_api_key'] : '';
            $api_secret = isset( $options['fai_api_secret'] ) ? $options['fai_api_secret'] : '';

            if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
                try {
                    $mj = new \Mailjet\Client( $api_key, $api_secret, true, ['version' => 'v3'] );
                    $body = [
                        'IsExcludedFromCampaigns' => true,
                        'Name' => $name,
                        'Email' => $email
                    ];
                    $response = $mj->post( \Mailjet\Resources::$Contact, ['body' => $body] );

                    if ( ! $response->success() ) {
                        $fai_mailjet_error_message = $response->getStatus() . ' ' . $response->getReasonPhrase() . ' - ' . json_encode($response->getBody());
                    }
                } catch ( \Exception $e ) {
                    $fai_mailjet_error_message = 'Mailjet Library Exception: ' . $e->getMessage();
                }
            }

            $table_name = $wpdb->prefix . 'fai_submissions';
            $wpdb->insert(
                $table_name,
                array(
                    'email' => $email,
                    'submission_date' => current_time( 'mysql' ),
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                )
            );
        }
    }
}

/**
 * Add the Mailjet error to the AJAX response if it exists.
 */
function fai_add_mailjet_error_to_response( $response, $result ) {
    global $fai_mailjet_error_message;

    if ( ! empty( $fai_mailjet_error_message ) ) {
        $response['mailjet_error'] = $fai_mailjet_error_message;
    }

    return $response;
}
add_filter( 'wpcf7_ajax_json_echo', 'fai_add_mailjet_error_to_response', 10, 2 );

/**
 * Enqueue the scripts.
 */
function fai_enqueue_scripts() {
    wp_enqueue_script(
        'fai-main-js',
        plugin_dir_url( __FILE__ ) . '../public/js/main.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    $options = get_option( 'fai_settings' );
    $thank_you_message = isset( $options['fai_thank_you_message'] ) ? $options['fai_thank_you_message'] : '';

    wp_localize_script( 'fai-main-js', 'fai_vars', array(
        'thank_you_message' => $thank_you_message,
    ) );
}
