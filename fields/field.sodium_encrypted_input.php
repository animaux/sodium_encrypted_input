<?php
	
	/* Verschlüsselung funktioniert prima, wenn man nur fields[feldname] benutzt, aber wenn man fields[feldname][ciphered] und fields[feldname][nonce] benutzt gibt es einen nicht weiter benannten Fehler beim Speichern. Da wir aber die Nonce in einem zweiten Feld speicher müssen, was auch klappt, geht es nicht so einfach. */
	
	require_once(CORE . '/class.cacheable.php');
	
	Class FieldSodium_Encrypted_Input extends Field {
		
		function __construct(){
			parent::__construct();
			$this->_name = 'Encrypted Input (Sodium)';
		}


		// derived from from maplocationfield
		public function createTable(){
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `ciphered` MEDIUMTEXT default NULL,
				  `nonce` MEDIUMTEXT default NULL,
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  FULLTEXT KEY `ciphered` (`ciphered`),
				  FULLTEXT KEY `nonce` (`nonce`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}

	
		/*-------------------------------------------------------------------------
			Settings:
		-------------------------------------------------------------------------*/		
		
		public function displaySettingsPanel(XMLElement &$wrapper, $errors = NULL) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$div = new XMLElement('div', NULL, array('class' => 'compact'));
			$wrapper->appendChild($div);
		}
		
		public function commit(){
			if(!parent::commit()) return false;

			$id = $this->get('id');
			if($id === false) return false;

			$fields = array();
			$fields['field_id'] = $id;

      return FieldManager::saveSettings($id, $fields);
		}
				
		/*-------------------------------------------------------------------------
			Publish:
		-------------------------------------------------------------------------*/
			
		public function displayPublishPanel(XMLElement &$wrapper, $data = NULL, $flagWithError = NULL, $fieldnamePrefix = NULL, $fieldnamePostfix = NULL, $entry_id = NULL){
		  
		  $data['ciphered'] = $data['ciphered'] ?? null;
		  $data['nonce'] = $data['nonce'] ?? null;
			$value = General::sanitize($data['ciphered']);
			
			$label = Widget::Label($this->get('label'));
			
			if(empty($value)) {
			    // value
					$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').'][ciphered]'.$fieldnamePostfix, (strlen($value) != 0 ? $value : NULL)));
					
			    if($flagWithError != NULL) {
			        $wrapper->appendChild(Widget::Error($label, $flagWithError));
			    } else {
			        $wrapper->appendChild($label);
			    }
			} else {
				$wrapper->setAttribute('class', $wrapper->getAttribute('class') . ' file');
				$label->appendChild(new XMLElement('span', $value, array('class' => 'frame')));
				//value
				$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').'][ciphered]'.$fieldnamePostfix, 'encrypted:' . $data['ciphered'], 'hidden'));
				
				// nonce
				$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').'][nonce]'.$fieldnamePostfix, $data['nonce'], 'hidden'));
				$wrapper->appendChild($label);
			}
			
		}

		public function processRawFieldData($data, &$status, &$message=null, $simulate=false, $entry_id=null) {
			$status = self::__OK__;
						
			if (empty($data['ciphered'])) {
			  // store empty (NULL) value without encryption if the field is optional
				return array(
					'ciphered' => '',
					'nonce' => '',
				);
			}
						
			if (is_array($data) && preg_match("/^encrypted:/", $data['ciphered'])) {
			    // has already been encrypted
			    return array(
    				'ciphered' => preg_replace("/^encrypted:/", '', $data['ciphered']),
    				'nonce' => $data['nonce']
    			);
			} else {
			    // encrypt it!
			    $nonce = \random_bytes(\SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
					$encryptedValue = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($data['ciphered'], '', $nonce, base64_decode(Symphony::Configuration()->get('key', 'sodium_encrypted_input')));
			    
			    return array(
    				'ciphered' => base64_encode($encryptedValue),
    				'nonce' => base64_encode($nonce)
    			);
			}
			
		}
		
		
		/*-------------------------------------------------------------------------
			Output:
		-------------------------------------------------------------------------*/		

		function appendFormattedElement(XMLElement &$wrapper, $data = NULL, $encode = false, $mode = NULL, $entry_id = NULL){
			if(!is_array($data) || empty($data['ciphered'])) return;
			
			$value = $this->decrypt($data['ciphered'], $data['nonce']);
			
			$xml = new XMLElement($this->get('element_name'), General::sanitize($value));
			$wrapper->appendChild($xml);
		}

		
		
		


		function decrypt($string, $nonce) { 
		
		    return sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(base64_decode($string), '', base64_decode($nonce), base64_decode(Symphony::Configuration()->get('key', 'sodium_encrypted_input')));
		}

	}