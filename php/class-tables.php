<?php

if(!class_exists('WP_List_Table'))
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class MailChimp_STS_Sends_Table extends WP_List_Table {

	function prepare_items($items) {

		$this->_column_headers = array($this->get_columns(),array(),array());
		$date_format = get_option('date_format', 'F j, Y') . ' '. get_option('time_format', 'g:i');

		foreach( $items as $key => $array )
			$items[$key]['hour'] = date_i18n($date_format, strtotime($array['hour'].':00'));

		$this->items = $items;
	}

	function get_columns() {

		return array(
			'hour' => __('Date', 'mailchimp-sts'),
			'sent' => __('Sends','mailchimp-sts'),
			'opens' => __('Opens','mailchimp-sts'),
			'clicks' => __('Clicks','mailchimp-sts'),
			'bounces' => __('Bounces','mailchimp-sts'),
			'rejects' => __('Rejects','mailchimp-sts'),
			'complaints' => __('Complaints','mailchimp-sts'),
		);
	}

	function column_default($item, $column_name) {

		if( !isset($item[$column_name]) )
			return '';

		if( is_numeric($item[$column_name]) && 0 == $item[$column_name])
			return '';

		return esc_html($item[$column_name]);
	}

	function display_tablenav() {}
}

class MailChimp_STS_Tags_Table extends WP_List_Table {

	function prepare_items($items) {

		static $fields = array('sent','bounces','rejects','complaints','opens','clicks');

		$this->_column_headers = array($this->get_columns(),array(),array());

		if(empty($items))
			$items = array();

		$tags = array_unique( wp_list_pluck($items,'tag') );
		sort($tags);

		$output = array();

		foreach($items as $key => $array) {

			$tag = $array['tag'];

			if( isset($output[$tag]) ) {

				foreach($fields as $field)
					$output[$tag][$field] += $array[$field];
			}
			else {

				$output[$tag] = wp_array_slice_assoc($items[$key], $fields);
				$output[$tag]['tag'] = $tag;
			}
		}

		$this->items = $output;
	}

	function get_columns() {

		return array(
			'tag' => __('Tag','mailchimp-sts'),
			'sent' => __('Sends','mailchimp-sts'),
			'opens' => __('Opens','mailchimp-sts'),
			'clicks' => __('Clicks','mailchimp-sts'),
			'bounces' => __('Bounces','mailchimp-sts'),
			'rejects' => __('Rejects','mailchimp-sts'),
			'complaints' => __('Complaints','mailchimp-sts'),
		);
	}

	function column_default($item, $column_name) {

		if( !isset($item[$column_name]) )
			return '';

		if( is_numeric($item[$column_name]) && 0 == $item[$column_name])
			return '';

		return esc_html($item[$column_name]);
	}

	function no_items() {

		_e('No tag statistics for the period.','mailchimp-sts');
	}

	function display_tablenav() {}
}

class MailChimp_STS_URLs_Table extends WP_List_Table {

	function prepare_items($since) {

		$this->_column_headers = array($this->get_columns(),array(),array());

		$urls = get_transient('mailchimp-sts-urls');

		if( empty($urls) ) {

			$urls = MailChimp_STS::GetUrls();

			if( !is_wp_error($urls) )
				set_transient('mailchimp-sts-urls', $urls, 60*60);
		}

		$items = get_transient('mailchimp-sts-stats-urls'.$since);

		if( empty($items) ) {

			$items = MailChimp_STS::GetUrlStats(null, $since);

			if( !is_wp_error($items) )
				set_transient('mailchimp-sts-stats-urls'.$since, $items, 60*15);
		}

		if(empty($items))
			$items = array();

		$urls = array_combine(wp_list_pluck($urls,'url_id'), wp_list_pluck($urls,'url'));
		$output = array();

		foreach($items as $key => $array) {

			$items[$key]['url'] = $url = $urls[$array['url_id']];

			if( isset($output[$url]) ) {

				$output[$url]['sent'] += $array['sent'];
				$output[$url]['clicks'] += $array['clicks'];
			}
			else {

				$output[$url] = wp_array_slice_assoc($items[$key], array('url','sent','clicks'));
			}
		}

		$this->items = $output;
	}

	function get_columns() {

		return array(
			'url' => __('URL','mailchimp-sts'),
			'sent' => __('Sends','mailchimp-sts'),
			'clicks' => __('Clicks','mailchimp-sts'),
		);
	}

	function column_default($item, $column_name) {

		if( !isset($item[$column_name]) )
			return '';

		if( is_numeric($item[$column_name]) && 0 == $item[$column_name])
			return '';

		return esc_html($item[$column_name]);
	}

	function no_items() {

		_e('No URL statistics for the period.','mailchimp-sts');
	}

	function display_tablenav() {}
}
