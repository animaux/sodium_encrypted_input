<?php

	Class extension_sodium_encrypted_input extends Extension{
		
		public function install() {
			// generate key
			$key = sodium_crypto_aead_xchacha20poly1305_ietf_keygen();
			Symphony::Configuration()->set('key', base64_encode($key), 'sodium_encrypted_input');
			Symphony::Configuration()->write();
			// create settings table
			return Symphony::Database()->query("CREATE TABLE `tbl_fields_sodium_encrypted_input` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `field_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`),
			  UNIQUE KEY `field_id` (`field_id`)
			) TYPE=MyISAM");
		}
		
		public function uninstall() {
			// remove config
			Symphony::Configuration()->remove('sodium_encrypted_input');			
			Symphony::Configuration()->write();
			// remove field settings
			Symphony::Database()->query("DROP TABLE `tbl_fields_sodium_encrypted_input`");
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'initaliseAdminPageHead'
				)
			);
		}
		
		public function initaliseAdminPageHead($context) {
			$page = Administration::instance()->Page;
			$callback = Administration::instance()->getPageCallback();
			
			if ($page instanceOf contentPublish && in_array($callback['context']['page'], array('edit', 'new'))) {
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/sodium_encrypted_input/assets/sodium_encrypted_input.publish.js', 300);
			}
		}
			
	}