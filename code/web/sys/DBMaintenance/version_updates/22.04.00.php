<?php
/** @noinspection PhpUnused */
function getUpdates22_04_00() : array
{
	return [
		/*'name' => [
			'title' => '',
			'description' => '',
			'sql' => [
				''
			]
		], //sample*/
		'restrictLoginToLibraryMembers' => [
			'title' => 'Restrict Login to Library Members',
			'description' => 'Allow restricting login to patrons of a specific home system',
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowLoginToPatronsOfThisLibraryOnly TINYINT(1) DEFAULT 0',
				'ALTER TABLE library ADD COLUMN messageForPatronsOfOtherLibraries TEXT'
			]
		], //restrictLoginToLibraryMembers
		'catalogStatus' => [
			'title' => 'Catalog Status',
			'description' => 'Allow placing Aspen into offline mode via System Variables',
			'continueOnError' => true,
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN catalogStatus TINYINT(1) DEFAULT 0',
				"ALTER TABLE system_variables ADD COLUMN offlineMessage TEXT",
				"UPDATE system_variables set offlineMessage = 'The catalog is down for maintenance, please check back later.'",
				"DROP TABLE IF EXISTS offline_holds"
			]
		], //catalogStatus
		'user_hoopla_confirmation_checkout_prompt2' => array(
			'title' => 'Hoopla Checkout Confirmation Prompt - recreate',
			'description' => 'Stores user preference whether or not to prompt for confirmation before checking out a title from Hoopla',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE `user` ADD COLUMN `hooplaCheckOutConfirmation` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;"
			),
		), //user_hoopla_confirmation_checkout_prompt2
		'user_hideResearchStarters' => [
			'title' => 'User Hide Research Starters - recreate',
			'description' => 'Recreates column to hide research starters',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE user ADD COLUMN hideResearchStarters TINYINT(1) DEFAULT 0"
			),
		], //user_hideResearchStarters
		'user_role_uniqueness' => [
			'title' => 'User Role Uniqueness',
			'description' => 'Update Uniqueness for User Roles',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE user_roles DROP PRIMARY KEY",
				"ALTER TABLE user_roles ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
			),
		], //user_role_uniqueness
		'browse_category_times_shown' => [
			'title' => 'Browse Category Times Shown',
			'description' => 'Make times shown an int rather than medium int',
			'continueOnError' => true,
			'sql' => array(
				"ALTER TABLE browse_category CHANGE COLUMN numTimesShown numTimesShown INT NOT NULL DEFAULT  0",
			),
		], //browse_category_times_shown
        'permissions_create_events_springshare' => [
            'title' => 'Alters permissions for Events',
            'description' => 'Create permissions for Springshare LibCal; update permissions for LibraryMarket LibraryCalendar',
            'sql' => [
                "UPDATE permissions SET name = 'Administer LibraryMarket LibraryCalendar Settings', description = 'Allows the user to administer integration with LibraryMarket LibraryCalendar for all libraries.' WHERE name = 'Administer Library Calendar Settings'",
                "INSERT INTO permissions (sectionName, name, requiredModule, weight, description) VALUES ('Events', 'Administer Springshare LibCal Settings', 'Events', 20, 'Allows the user to administer integration with Springshare LibCal for all libraries.')",
                "INSERT INTO role_permissions(roleId, permissionId) VALUES ((SELECT roleId from roles where name='opacAdmin'), (SELECT id from permissions where name='Administer Springshare LibCal Settings'))"
            ]
        ], // permissions_create_events_springshare
        'springshare_libcal_settings' => [
            'title' => 'Define events settings for Springshare LibCal integration',
            'description' => 'Initial setup of the Springshare LibCal integration',
            'sql' => [
                'CREATE TABLE IF NOT EXISTS springshare_libcal_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL UNIQUE,
					baseUrl VARCHAR(255) NOT NULL,
                    calId SMALLINT NOT NULL,
                    clientId SMALLINT NOT NULL,
                    clientSecret VARCHAR(36) NOT NULL,
                    userName VARCHAR(36) NOT NULL,
                    password VARCHAR(36) NOT NULL
				) ENGINE INNODB',
            ]
        ], // springshare_libcal_settings
        'springshare_libcal_events' => [
            'title' => 'Springshare LibCal Events Data' ,
            'description' => 'Setup tables to store events data for Springshare LibCal',
            'sql' => [
                'CREATE TABLE IF NOT EXISTS springshare_libcal_events (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					settingsId INT NOT NULL,
					externalId varchar(36) NOT NULL,
					title varchar(255) NOT NULL,
					rawChecksum BIGINT,
					rawResponse MEDIUMTEXT,
					deleted TINYINT default 0,
					UNIQUE (settingsId, externalId)
				)'
            ]
        ], // springshare_libcal_events
	];
}