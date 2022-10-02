<?php
/** @noinspection PhpUnused */
function getUpdates22_10_00() : array
{
	$curTime = time();
	return [
		/*'name' => [
			'title' => '',
			'description' => '',
			'sql' => [
				''
			]
        ], //sample*/


		//mark
		'indexing_profile_statusesToSuppressLength' => [
			'title' => 'Increase the length of statuses to suppress',
			'description' => 'Increase the length of statuses to suppress',
			'sql' => [
				'ALTER TABLE indexing_profiles CHANGE COLUMN statusesToSuppress statusesToSuppress varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL'
			]
		], //indexing_profile_statusesToSuppressLength
		'indexing_profile_record_linking' => [
			'title' => 'Indexing Profile - Record Linking',
			'description' => 'Add toggle to process record linking',
			'sql' => [
				'ALTER TABLE indexing_profiles ADD COLUMN processRecordLinking TINYINT(1) DEFAULT 0'
			]
		], //indexing_profile_record_linking
		'record_parents' => [
			'title' => 'Record Parents',
			'description' => 'Add a table to define parents for a record',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS record_parents(
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					childRecordId VARCHAR(50) collate utf8_bin,
					parentRecordId VARCHAR(50) collate utf8_bin,
					UNIQUE (childRecordId, parentRecordId)
				) ENGINE INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
			]
		], //record_parents
		'add_childRecords_more_details_section' => [
			'title' => 'Add Child Records Section to More Details',
			'description' => 'Add Child Records Section to More Details',
			'sql' => [
				"UPDATE grouped_work_more_details SET weight = (weight + 1) where weight >= 3",
				"INSERT INTO grouped_work_more_details (groupedWorkSettingsId, source, collapseByDefault, weight) select grouped_work_display_settings.id, 'childRecords', 0, 3 from grouped_work_display_settings where grouped_work_display_settings.id in (SELECT distinct groupedWorkSettingsId from grouped_work_more_details)",
			]
		], //add_childRecords_more_details_section
		'add_child_title_to_record_parents' => [
			'title' => 'Add Child Title to Record Parents',
			'description' => 'Add Child Title to Record Parents',
			'sql' => [
				'ALTER TABLE record_parents ADD COLUMN childTitle VARCHAR(750) NOT NULL'
			]
		], //add_child_title_to_record_parents
		'add_parentRecords_more_details_section' => [
			'title' => 'Add Parent Records Section to More Details',
			'description' => 'Add Parent Records Section to More Details',
			'sql' => [
				"UPDATE grouped_work_more_details SET weight = (weight + 1) where weight >= 2",
				"INSERT INTO grouped_work_more_details (groupedWorkSettingsId, source, collapseByDefault, weight) select grouped_work_display_settings.id, 'parentRecords', 0, 2 from grouped_work_display_settings where grouped_work_display_settings.id in (SELECT distinct groupedWorkSettingsId from grouped_work_more_details)",
			]
		], //add_parentRecords_more_details_section
		'basic_page_allow_access_by_home_location' => [
			'title' => 'Basic Page - Allow Access By Home Location',
			'description' => 'Basic Page - Allow Access By Home Location',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS web_builder_basic_page_home_location_access (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					basicPageId INT(11) NOT NULL, 
					homeLocationId INT(11) NOT NULL,
					UNIQUE INDEX (basicPageId, homeLocationId)
				) ENGINE INNODB'
			]
		], //basic_page_allow_access_by_home_location
		'location_self_registration_branch' => [
			'title' => 'Location - Self Registration Branch',
			'description' => 'Allow determination of which branches are valid for self registration',
			'sql' => [
				"ALTER TABLE location add column validSelfRegistrationBranch tinyint(4) NOT NULL DEFAULT '1'",
				"ALTER TABLE location ADD INDEX ValidSelfRegistrationBranch (validSelfRegistrationBranch)",
				"UPDATE location set validSelfRegistrationBranch = validHoldPickupBranch",
			]
		], //location_self_registration_branch
		'self_registration_parent_sms' => [
			'title' => 'Library - Self Registration Parent & SMS Notices',
			'description' => 'Allow determination of which branches are valid for self registration',
			'sql' => [
				"ALTER TABLE library add column promptForParentInSelfReg tinyint(1) NOT NULL DEFAULT '0'",
				"ALTER TABLE library ADD column promptForSMSNoticesInSelfReg tinyint(1) NOT NULL DEFAULT '0'",
			]
		], //self_registration_parent_sms
		'self_registration_require_phone_and_email' => [
			'title' => 'Library - Self Registration Require Phone and email',
			'description' => 'Allow determination of if phone and email are required for self registration',
			'sql' => [
				"ALTER TABLE library add column selfRegRequirePhone tinyint(1) NOT NULL DEFAULT '0'",
				"ALTER TABLE library ADD column selfRegRequireEmail tinyint(1) NOT NULL DEFAULT '1'",
			]
		], //self_registration_require_phone_and_email
		'add_holdings_more_details_section' => [
			'title' => 'Add Holdings Records Section to More Details',
			'description' => 'Add Holdings Records Section to More Details',
			'sql' => [
				"UPDATE grouped_work_more_details SET weight = (weight + 1) where weight >= 4",
				"INSERT INTO grouped_work_more_details (groupedWorkSettingsId, source, collapseByDefault, weight) select grouped_work_display_settings.id, 'marcHoldings', 0, 4 from grouped_work_display_settings where grouped_work_display_settings.id in (SELECT distinct groupedWorkSettingsId from grouped_work_more_details)",
			]
		], //add_holdings_more_details_section
		'grouped_work_display_856_as_access_online' => [
			'title' => 'Add 856 links as access online buttons to grouped work display settings',
			'description' => 'Allow 856 links to be shown as access online buttons',
			'sql' => [
				"ALTER TABLE grouped_work_display_settings ADD COLUMN show856LinksAsAccessOnlineButtons TINYINT(1) DEFAULT 0",
			]
		], //grouped_work_display_856_as_access_online

		//kirstien
		'aci_speedpay_sdk_config' => [
			'title' => 'Add SDK settings for ACI Speedpay',
			'description' => 'Add SDK settings for ACI Speedpay integration',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE aci_speedpay_settings ADD COLUMN sdkClientId VARCHAR(100)",
				"ALTER TABLE aci_speedpay_settings ADD COLUMN sdkClientSecret VARCHAR(100)",
				"ALTER TABLE aci_speedpay_settings ADD COLUMN sdkApiAuthKey VARCHAR(100)"
			),
		], //aci_speedpay_sdk_config
		'create_lida_notifications' => [
			'title' => 'Add LiDA Notifications',
			'description' => 'Setup tables to allow custom notifications to LiDA users by library/location and patron type',
			'continueOnError' => true,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS aspen_lida_notifications (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					title VARCHAR(75) NOT NULL,
					message VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
					sendOn INT(11),
					expiresOn INT(11),
					ctaUrl VARCHAR(500) DEFAULT NULL,
					ctaLabel VARCHAR(25) DEFAULT NULL,
					sent INT(1) DEFAULT 0
				) ENGINE INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
				'CREATE TABLE IF NOT EXISTS aspen_lida_notifications_library (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					lidaNotificationId INT(11),
					libraryId INT(11)
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS aspen_lida_notifications_location (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					lidaNotificationId INT(11),
					locationId INT(11)
				) ENGINE INNODB',
				'CREATE TABLE IF NOT EXISTS aspen_lida_notifications_ptype (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					lidaNotificationId INT(11),
					patronTypeId INT(11)
				) ENGINE INNODB',
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Aspen LiDA', 'Send Notifications', '', 6, 'Controls if the user can send notifications to Aspen LiDA users.</em>')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Send Notifications'))",
			]
		], //create_lida_notifications
		'add_notifyCustom' => [
			'title' => 'Add notifyCustom to notification settings',
			'description' => 'Add option to enable custom alerts in notification settings',
			'sql' => [
				'ALTER TABLE aspen_lida_notification_setting ADD COLUMN notifyCustom TINYINT(1) DEFAULT 0'
			]
		], //add_notifyCustom
		'add_userAlertPreferences' => [
			'title' => 'Add notification type preferences to token',
			'description' => 'Allow user to turn on/off specific notification types tied to device push token',
			'sql' => [
				'ALTER TABLE user_notification_tokens ADD COLUMN notifySavedSearch TINYINT(1) DEFAULT 0',
				'ALTER TABLE user_notification_tokens ADD COLUMN notifyCustom TINYINT(1) DEFAULT 0'
			]
		], //add_userAlertPreferences
		'update_userAlertPreferences' => [
			'title' => 'Update users to allow saved search notifications',
			'description' => 'Update existing rows in user_notification_tokens to allow notifications on saved searches',
			'sql' => [
				'UPDATE user_notification_tokens SET notifySavedSearch = 1',
			]
		], //update_userAlertPreferences
		'add_imgOptions' => [
			'title' => 'Add additional options for web builder image cells',
			'description' => 'Add options to enable click to enlarge and provide alt text',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE web_builder_portal_cell ADD COLUMN imgAction TINYINT(1) DEFAULT 0",
				"ALTER TABLE web_builder_portal_cell ADD COLUMN imgAlt VARCHAR(255)",
			),
		], //add_imgOptions
		'add_batchDeletePermissions' => [
			'title' => 'Add batchDelete permission',
			'description' => 'Add permissions to allow users to batch delete objects',
			'continueOnError' => true,
			'sql' => array(
				"INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('System Administration', 'Batch Delete', '', 6, 'Controls if the user is able to batch delete.</em>')",
				"INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Batch Delete'))",
			),
		], //add_batchDeletePermissions
		'add_ctaDeepLinkOptions' => [
			'title' => 'Add config to custom LiDA notifications',
			'description' => 'Add options to easily link into screens within Aspen LiDA',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE aspen_lida_notifications ADD COLUMN linkType TINYINT(1) DEFAULT 0',
				'ALTER TABLE aspen_lida_notifications ADD COLUMN deepLinkPath VARCHAR(75)',
				'ALTER TABLE aspen_lida_notifications ADD COLUMN deepLinkId VARCHAR(255)',
			]
		], //add_ctaDeepLinkOptions
		'add_moveSearchTools' => [
			'title' => 'Add option to move search tools to top',
			'description' => 'Add option to move the search tools to the top of the search results in Grouped Work Display Settings',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE grouped_work_display_settings ADD COLUMN showSearchToolsAtTop TINYINT(1) DEFAULT 0',
			]
		], //add_moveSearchTools
		'add_fullWidthTheme' => [
			'title' => 'Add option to make header and footer full width',
			'description' => 'Add option to make header and footer full width in theme',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE themes ADD COLUMN fullWidth TINYINT(1) DEFAULT 0',
			]
		], //add_fullWidthTheme
		'add_coverStyle' => [
			'title' => 'Add options to change style of cover images',
			'description' => 'Add options to change style of cover images',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE themes ADD COLUMN coverStyle VARCHAR(50) DEFAULT "border" NOT NULL',
			]
		], //add_coverStyle
		'cleanupApiUsage_func' => [
			'title' => 'Fix api_usage rows with incorrect modules',
			'description' => 'Fixing where SearchAPI and ListAPI were incorrectly labeled as SystemAPI module',
			'continueOnError' => true,
			'sql' => [
				'cleanupApiUsageTable',
			]
		], //cleanupApiUsage_func


		//kodi

		//other

	];
}

function cleanupApiUsageTable()
{
	require_once ROOT_DIR . '/sys/SystemLogging/APIUsage.php';

	$listApiUsage = new APIUsage();
	$listApiUsage->module = "ListAPI";
	$listApiUsage->find();
	while($listApiUsage->fetch()) {
		$row = new APIUsage();
		$row->module = 'SystemAPI';
		$row->method = $listApiUsage->method;
		$row->instance = $listApiUsage->instance;
		if($row->find(true)) {
			$row->year = $listApiUsage->year;
			$row->month = $listApiUsage->month;
			if($row->find(true)) {
				$listApiUsage->numCalls = ($listApiUsage->numCalls + $row->numCalls);
				$listApiUsage->update();
				$row->delete();
			} else {
				$row->module = 'ListAPI';
				$row->update();
			}
		}
	}

	$searchApiUsage = new APIUsage();
	$searchApiUsage->module = "SearchAPI";
	$searchApiUsage->find();
	while($searchApiUsage->fetch()) {
		$row = new APIUsage();
		$row->module = 'SystemAPI';
		$row->method = $searchApiUsage->method;
		$row->instance = $searchApiUsage->instance;
		if($row->find(true)) {
			$row->year = $searchApiUsage->year;
			$row->month = $searchApiUsage->month;
			if($row->find(true)) {
				$searchApiUsage->numCalls = ($searchApiUsage->numCalls + $row->numCalls);
				$searchApiUsage->update();
				$row->delete();
			} else {
				$row->module = 'SearchAPI';
				$row->update();
			}
		}
	}
}