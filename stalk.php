<?php
namespace jgpf_qQZG0;

defined('ABSPATH') or die();

final class Stalk {
	
    private function __construct() {
    }
	
	public static function execute() {
		
		global $wpdb;
		$signature = 'jgpf_qQZG0';
		$table_name = $wpdb->prefix . $signature . '_' . 'client_provided_details';
		
		// determine if installation required
		$installation_required = false;
		$sql = "SELECT COUNT(*) FROM " . $wpdb->prefix . 'options' . " WHERE option_name = '" . $signature . "'";
		$result = $wpdb->get_var($sql);
		if ($result === null || $result === '0') {
			$installation_required = true;
		}
		
		// hook installation
		if ($installation_required === true) {
			// create required tables
			add_action(
				'wp_loaded', 
				function () use ($table_name, $signature) {
					// create table
					global $wpdb;
					$charset_collate = $wpdb->get_charset_collate();
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); // dependency for dbDelta()
					$sql = "CREATE TABLE " . $table_name . " (
						id INT(11) NOT NULL AUTO_INCREMENT,
						datetime VARCHAR(255),
						remote_addr VARCHAR(255),
						operating_system VARCHAR(255),
						browser VARCHAR(255),
						device_type VARCHAR(255),
						crawler VARCHAR(255),
						PRIMARY KEY  (id)
					) " . $charset_collate . ";";
					dbDelta($sql);
					
					// flag successful installation
					$wpdb->insert( 
						$wpdb->prefix . 'options', 
						array( 
							'option_name' => $signature,
							'option_value' => '1', 
							'autoload' => 'no',
						), 
						array( 
							'%s', 
							'%s',
						) 
					);
				},
				10
			);
		}

		// hook main operation
		add_action(
			'get_header', 
			function () use ($table_name) {
				
				$browser = get_browser(null, true);
				global $wpdb;
				
				// $wpdb db layer takes care of sql injections
				$wpdb->insert( 
					$table_name, 
					array( 
						'datetime' => date(DATE_ATOM),
						'remote_addr' => $_SERVER['REMOTE_ADDR'], 
						'operating_system' => $browser['platform'],
						'browser' => $browser['comment'],
						'device_type' => $browser['device_type'],
						'crawler' => $browser['crawler'],
					), 
					array( 
						'%s', 
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					) 
				);
			},
			15
		);
		
	}
}

Stalk::execute();