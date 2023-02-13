<?php
/** @noinspection PhpUnused */
function getUpdates23_03_00(): array {
	$curTime = time();
	return [
		/*'name' => [
			'title' => '',
			'description' => '',
			'continueOnError' => false,
			'sql' => [
				''
			]
		], //sample*/

		//mark

		//kirstien
		'add_ldap_to_sso' => [
			'title' => 'Add LDAP configuration to SSO Settings',
			'description' => 'Adds initial LDAP configuration options for single sign-on settings',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE sso_setting ADD COLUMN ldapHosts VARCHAR(500) default NULL',
				'ALTER TABLE sso_setting ADD COLUMN ldapUsername VARCHAR(75) default NULL',
				'ALTER TABLE sso_setting ADD COLUMN ldapPassword VARCHAR(75) default NULL',
				'ALTER TABLE sso_setting ADD COLUMN ldapBaseDN VARCHAR(500) default NULL',
				'ALTER TABLE sso_setting ADD COLUMN ldapIdAttr VARCHAR(75) default NULL',
				'ALTER TABLE sso_setting ADD COLUMN ldapOrgUnit VARCHAR(225) default NULL'
			]
		],
		//add_ldap_to_sso
		'add_ldap_label' => [
			'title' => 'Add LDAP Label to SSO Settings',
			'description' => 'Add field to give LDAP service a user-facing name for single sign-on settings',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE sso_setting ADD COLUMN ldapLabel VARCHAR(75) default NULL',
			]
		],
		//add_ldap_label
		'add_account_profile_library_settings' => [
			'title' => 'Add account profile to library settings',
			'description' => 'Add account profile to library settings, then run script to update value to existing ils profile',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN accountProfileId INT(10) default -1',
				'updateAccountProfileInLibrarySettings',
			]
		],
		//add_account_profile_library_settings
		'add_sso_settings_account_profile' => [
			'title' => 'Add SSO settings to account profile',
			'description' => 'Add column to store assigned single sign-on settings in account profile',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE account_profiles ADD COLUMN ssoSettingId TINYINT(11) default -1',
			]
		],
		//add_sso_settings_account_profile
		'add_fallback_sso_mapping' => [
			'title' => 'Add fallback column to SSO Mapping',
			'description' => 'Add column to store fallback value for SSO user data mapping table',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE sso_mapping ADD COLUMN fallbackValue VARCHAR(255) default NULL',
			]
		],
		//add_fallback_sso_mapping
		'add_sso_account_profiles' => [
			'title' => 'Modify authenticationMethod in Account Profiles',
			'description' => 'Modify enum authenticationMethod to include sso option in Account Profiles',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE account_profiles MODIFY COLUMN authenticationMethod enum('ils','sip2','db','ldap', 'sso')",
			]
		],
		//add_sso_account_profiles
		'add_sso_auth_only' => [
			'title' => 'Add option to SSO Settings to authenticate only with SSO',
			'description' => 'Add option to SSO settings to authenticate only with SSO and not DB or ILS',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE sso_setting ADD COLUMN ssoAuthOnly TINYINT(1) default 0",
			]
		],
		//add_sso_auth_only
		'migrate_library_sso_settings' => [
			'title' => 'Migrate Library SSO Settings to SSO Settings',
			'description' => 'Migrate any existing SSO Settings in Library Systems to SSO Settings',
			'continueOnError' => false,
			'sql' => [
				'moveLibrarySSOSettings',
			]
		],
		//migrate_library_sso_settings

		//kodi

		//other
	];
}

/** @noinspection PhpUnused */
function updateAccountProfileInLibrarySettings(/** @noinspection PhpUnusedParameterInspection */ &$update) {
	require_once ROOT_DIR . '/sys/Account/AccountProfile.php';
	require_once ROOT_DIR . '/sys/LibraryLocation/Library.php';

	$accountProfileId = -1;
	$accountProfile = new AccountProfile();
	$accountProfile->name = 'ils';
	if($accountProfile->find(true)) {
		$accountProfileId = $accountProfile->id;
	}

	$libraries = [];
	$library = new Library();
	$library->orderBy('isDefault desc');
	$library->orderBy('displayName');
	$library->find();
	while($library->fetch()) {
		$libraries[$library->libraryId] = clone $library;
	}

	if(!empty($libraries)) {
		foreach ($libraries as $librarySettings) {
			$librarySettings->accountProfileId = $accountProfileId;
			$librarySettings->update();
		}
	}
}

/** @noinspection PhpUnused */
function moveLibrarySSOSettings(/** @noinspection PhpUnusedParameterInspection */ &$update) {
	global $aspen_db;
	$oldLibrarySettingsSQL = 'SELECT libraryId, displayName, ssoXmlUrl, ssoUsernameAttr, ssoUniqueAttribute, ssoPhoneAttr, ssoPatronTypeFallback, ssoPatronTypeAttr, ssoName, ssoMetadataFilename, ssoLibraryIdFallback, ssoLibraryIdAttr, ssoLastnameAttr, ssoIdAttr, ssoFirstnameAttr, ssoEntityId, ssoEmailAttr, ssoDisplayNameAttr, ssoCityAttr, ssoCategoryIdFallback, ssoCategoryIdAttr, ssoAddressAttr FROM library WHERE ssoSettingId = -1';
	$oldLibrarySettingsRS = $aspen_db->query($oldLibrarySettingsSQL, PDO::FETCH_ASSOC);
	$oldLibrarySettingsRow = $oldLibrarySettingsRS->fetch();

	require_once ROOT_DIR . '/sys/Authentication/SSOSetting.php';
	while ($oldLibrarySettingsRow != null) {
		$ssoSettingId = '-1';
		$ssoSetting = new SSOSetting();
		$ssoSetting->ssoEntityId = $oldLibrarySettingsRow['ssoEntityId'];
		$ssoSetting->ssoXmlUrl = $oldLibrarySettingsRow['ssoXmlUrl'];
		$ssoSetting->ssoUsernameAttr = $oldLibrarySettingsRow['ssoUsernameAttr'];
		$ssoSetting->ssoUniqueAttribute = $oldLibrarySettingsRow['ssoUniqueAttribute'];
		$ssoSetting->ssoPhoneAttr = $oldLibrarySettingsRow['ssoPhoneAttr'];
		$ssoSetting->ssoPatronTypeAttr = $oldLibrarySettingsRow['ssoPatronTypeAttr'];
		$ssoSetting->ssoPatronTypeFallback = $oldLibrarySettingsRow['ssoPatronTypeFallback'];
		$ssoSetting->ssoName = $oldLibrarySettingsRow['ssoName'];
		$ssoSetting->ssoMetadataFilename = $oldLibrarySettingsRow['ssoMetadataFilename'];
		$ssoSetting->ssoLibraryIdAttr = $oldLibrarySettingsRow['ssoLibraryIdAttr'];
		$ssoSetting->ssoLibraryIdFallback = $oldLibrarySettingsRow['ssoLibraryIdFallback'];
		$ssoSetting->ssoLastnameAttr = $oldLibrarySettingsRow['ssoLastnameAttr'];
		$ssoSetting->ssoIdAttr = $oldLibrarySettingsRow['ssoIdAttr'];
		$ssoSetting->ssoFirstnameAttr = $oldLibrarySettingsRow['ssoFirstnameAttr'];
		$ssoSetting->ssoEmailAttr = $oldLibrarySettingsRow['ssoEmailAttr'];
		$ssoSetting->ssoDisplayNameAttr = $oldLibrarySettingsRow['ssoDisplayNameAttr'];
		$ssoSetting->ssoCityAttr = $oldLibrarySettingsRow['ssoCityAttr'];
		$ssoSetting->ssoCategoryIdAttr = $oldLibrarySettingsRow['ssoCategoryIdAttr'];
		$ssoSetting->ssoCategoryIdFallback = $oldLibrarySettingsRow['ssoCategoryIdFallback'];
		$ssoSetting->ssoAddressAttr = $oldLibrarySettingsRow['ssoAddressAttr'];
		$ssoSetting->service = 'saml';
		if ($ssoSetting->find(true)) {
			$ssoSettingId = $ssoSetting->id;
		} else {
			$ssoSetting->name = $oldLibrarySettingsRow['displayName'] . ' SAML Settings';
			$ssoSetting->service = 'saml';
			if ($ssoSetting->insert()) {
				$ssoSettingId = $ssoSetting->id;
			}
		}

		$library = new Library();
		$library->libraryId = $oldLibrarySettingsRow['libraryId'];
		if ($library->find(true)) {
			$library->ssoSettingId = $ssoSettingId;
			$library->update();
		}

		$oldLibrarySettingsRow = $oldLibrarySettingsRS->fetch();
	}
}