<?php

if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_option( 'mailchimp-sts' );
delete_option( 'mailchimp-sts-test' );
delete_transient('mailchimp-sts-stats');
?>