<?php

add_action( 'rest_api_init', function () {
    register_rest_route( 'soundit/v1', '/contact-form', array(
      'methods' => 'POST',
      'callback' => 'contact_form',
      'permission_callback' => '__return_true'
    ));

    register_rest_route( 'soundit/v1', '/newsletter', array(
        'methods' => 'POST',
        'callback' => 'newsletter',
        'permission_callback' => '__return_true'
    ));
} );


function contact_form(WP_REST_Request $request) {
    $nonce = check_ajax_referer( 'wp_rest', '_nonce', false );

    # Check the nonce
    if ( ! $nonce ) {
        return new WP_Error(
        'invalid-nonce',
        esc_html__( 'No naughty business please!', 'project' ),
        [ 'status' => 403 ] );
    }

    # Form Fields
    $form = [
        'name' => sanitize_text_field($request->get_param( 'contact_form_name' )),
        'company' => sanitize_text_field($request->get_param( 'contact_form_company' )),
        'email' => sanitize_email($request->get_param( 'contact_form_email' )),
    ];

    # Build HTML Email
    $to      = sanitize_email(get_field('email', 'option'));
    $subject = esc_html__( 'Soundit → Contact Form', 'soundit' );
    $body = '<html><body>';
    $body .= '<h1>'.__('Contact Details', 'soundit').'</h1>';
    $body .= '<ul>';
    $body .= '<li>'.__('Name', 'soundit').': '. $form['name'] .'</li>';
    $body .= '<li>'.__('Email', 'soundit').': '. $form['email'] . '</li>';
    if ($form['company']) $body .= '<li>'.__('Company', 'soundit').': '. $form['company'] . '</li>';
    $body .= '</ul>';
    $body .= '</body></html>';
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );


    # Send Email
    $r = wp_mail( $to, $subject, $body, $headers );

    if ( $r ) {
        $response = [
            'code'    => 'sent-with-success',
            'data'    => [
                'status' => 200,
            ],
            'message' => esc_html__( 'Sent with success', 'soundit' )
        ];

        $recipient_body = '<html><body>';
        $recipient_body .= '<h1>'.__('Welcome to Soundit!', 'soundit').'</h1>';
        $recipient_body .= '<p>'.__('First of all, we would like to thank you for reaching us out. Some things just have to be experienced, so get ready! Soon you will recieve details about your live demo.', 'soundit').'</p>';
        $recipient_body .= '<p>'.__('Talk to you soon!', 'soundit').'</p>';
        $recipient_body .= '</body></html>';
        wp_mail( $form['email'], 'Welcome to Soundit!', $recipient_body, $headers );
        return new WP_REST_Response( $response, 200 );

    } else {
        return new WP_Error( 'could-not-send-email', esc_html__( 'Could not send email, please try again later.', 'soundit' ), [ 'status' => 403 ] );
    }
}

function newsletter(WP_REST_Request $request) {
    $nonce = check_ajax_referer( 'wp_rest', '_nonce', false );

    # Check the nonce
    if ( ! $nonce ) {
        return new WP_Error(
        'invalid-nonce',
        esc_html__( 'No naughty business please!', 'project' ),
        [ 'status' => 403 ] );
    }

    # Form Fields
    $email = $email = sanitize_email($request->get_param( 'newsletter_email' ));

    # Register on DB
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter';

    if ( is_email($email) ) {
        $wpdb->insert( $table_name, array(
            'email' => $email
        ) );

        $response = [
            'code'    => 'sent-with-success',
            'data'    => [
                'status' => 200,
            ],
            'message' => esc_html__( 'Sent with success', 'soundit' )
        ];
        
        return new WP_REST_Response( $response, 200 );

    } else {
        return new WP_Error( 'could-not-send-email', esc_html__( 'Could not send email, please try again later.', 'soundit' ), [ 'status' => 403 ] );
    }
}


/**
 *  Export Emails to CSV
 */
function custom_table_csv_pull() {
    global $wpdb;

    $filename = 'newsletter_csv';
    $date = date("Y-m-d H:i:s");
    $output = fopen('php://output', 'w');
    $result = $wpdb->get_results('SELECT * FROM     wp_newsletter', ARRAY_A);
    fputcsv( $output, array('email'));
    foreach ( $result as $key => $value ) {
        $modified_values = array(
        $value['email']
        );
        fputcsv( $output, $modified_values );
    }
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"" . $filename . " " . $date . ".csv\";" );
    header("Content-Transfer-Encoding: binary");
    exit;
}

add_action('wp_ajax_csv_pull','custom_table_csv_pull');
add_action('admin_menu', 'add_export_button');

function add_export_button() {
    add_menu_page( 'Export Emails', 'Export Emails', 'manage_options', 'custom_admin_page_slug', 'pg_building_function','',3 );
}

function pg_building_function() {
	$ajax_url = admin_url('admin-ajax.php?action=csv_pull');
    echo "<script>window.open('".$ajax_url."');</script>";
    exit;
}