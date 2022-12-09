<?php
$bcc_pmd_contacts = false; // sends a BCC to PMD contacts

$organization = DonationManager\organizations\get_default_organization();
$org_id = $organization['id'];

$args = [
  'post_type'       => 'donation',
  'order'           => 'DESC',
  'orderby'         => 'date',
  'posts_per_page'  => -1,
  'meta_query' => [
    [
      'key'     => 'organization',
      'value'   => $org_id,
      'compare' => '='
    ]
  ],
  'date_query' => [
    'after'   => '-50 hours',
    'before'  => '-46 hours',
  ]
];

/**
 * Sets content type to `text/html`.
 *
 * @return     string  Returns content type.
 */
function followup_email_set_html_content_type(){
  return 'text/html';
}
add_filter( 'wp_mail_content_type', 'followup_email_set_html_content_type' );

/**
 * Setups `from:` email address
 *
 * @return     string  Our `from:` email address
 */
function followup_email_set_mail_from(){
  return 'support@pickupmydonation.com';
}
add_filter( 'wp_mail_from', 'followup_email_set_mail_from' );

/**
 * Sets the `from:` name
 *
 * @return     string  Our `from:` name
 */
function followup_email_set_mail_from_name(){
  return 'PickUpMyDonation.com Support';
}
add_filter( 'wp_mail_from_name', 'followup_email_set_mail_from_name' );

$donations_query = new WP_Query( $args );
$row = 0;
$table_rows = [];
$headers = []; // email headers
while( $donations_query->have_posts() ){
  $donations_query->the_post();
  $post_id = get_the_ID();
  $table_rows[$row]['ID'] = $post_id;
  $table_rows[$row]['title'] = get_the_title();
  $table_rows[$row]['post_date'] = get_the_date();

  $donor_name = get_post_meta( $post_id, 'donor_name', true );
  $table_rows[$row]['donor_name'] = $donor_name;

  $donor_email = get_post_meta( $post_id, 'donor_email', true );
  $table_rows[$row]['donor_email'] = $donor_email;

  $followup_email_sent = get_post_meta( $post_id, 'followup_email_sent', true );
  $table_rows[$row]['followup_email_sent'] = $followup_email_sent;

  if( $bcc_pmd_contacts )
    $headers[] = 'Bcc:contact@pickupmydonation.com';

  //*
  if( ! $followup_email_sent ){
    $message = file_get_contents( dirname( __FILE__ ) . '/follow-up-email.html' );
    $search = ['{donor_name}'];
    $replace = [$donor_name];
    $message = str_replace( $search, $replace, $message );

    $subject = 'Update on Your Donation Status - PickUpMyDonation.com';
    /**
     * MailHog miss-encodes the subject line (i.e. you get "=?us-ascii?Q?" with no
     * subject showing). Reducing the strlen below 40 chars so we see it during
     * local development.
     *
     * Ref: https://github.com/mailhog/MailHog/issues/282
     */
    if( DONMAN_DEV_ENV ){
      if( 40 < strlen( $subject ) )
        $subject = substr( $subject, 0, 37 ) . '...';
    }

    wp_mail( $donor_email, $subject, $message, $headers );
    update_post_meta( $post_id, 'followup_email_sent', true );
  }
  /**/

  $row++;
}
wp_reset_postdata();
remove_filter( 'wp_mail_content_type', 'followup_email_set_html_content_type' );
remove_filter( 'wp_mail_from', 'followup_email_set_mail_from' );
remove_filter( 'wp_mail_from_name', 'followup_email_set_mail_from_name' );

WP_CLI\Utils\format_items('table', $table_rows, 'ID,title,post_date,donor_name,donor_email,followup_email_sent' );
/**/