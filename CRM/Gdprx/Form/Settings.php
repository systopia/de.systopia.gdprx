<?php
/*-------------------------------------------------------+
| SYSTOPIA GDPR Compliance Extension                     |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Gdprx_ExtensionUtil as E;

/**
 * Settings form controller
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdprx_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm() {
    // add general settings
    $this->addElement('checkbox',
                      "enforce_record_for_new_contacts",
                      E::ts("Require GDPR Record"));

    // add privacy defaults
    $this->addElement('checkbox',
                      "default_privacy_settings_enabled",
                      E::ts("Default Privacy Settings"));
    $fields = self::getPrivacyFields();
    foreach ($fields as $setting => $label) {
      $this->addElement('checkbox',
                        "default_privacy_{$setting}",
                        $label);
    }

    // add optional fields
    $fields = self::getOptionalConsentFields();
    foreach ($fields as $field_name => $label) {
      $this->addElement('checkbox',
                        "use_{$field_name}",
                        $label);
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    parent::buildQuickForm();
  }

  /**
   * set the default (=current) values in the form
   */
  public function setDefaultValues() {
    $config = CRM_Gdprx_Configuration::getSingleton();
    return $config->getSettings();
  }


  /**
   * Process and store changed settings
   */
  public function postProcess() {
    $config = CRM_Gdprx_Configuration::getSingleton();
    $values = $this->exportValues();

    // store default privacy settings
    $config->setSetting('default_privacy_settings_enabled', CRM_Utils_Array::value('default_privacy_settings_enabled', $values, FALSE));
    $fields = self::getPrivacyFields();
    foreach ($fields as $setting => $label) {
      $config->setSetting("default_privacy_{$setting}", CRM_Utils_Array::value("default_privacy_{$setting}", $values, FALSE));
    }

    $fields = self::getOptionalConsentFields();
    foreach ($fields as $setting => $label) {
      $config->setSetting("use_{$setting}", CRM_Utils_Array::value("use_{$setting}", $values, FALSE));
    }

    // store general options
    $config->setSetting("enforce_record_for_new_contacts", CRM_Utils_Array::value("enforce_record_for_new_contacts", $values, FALSE));

    $config->writeSettings();
    parent::postProcess();
  }


  public static function getPrivacyFields() {
    return array(
      'do_not_email'  => ts("Do Not Email"),
      'do_not_phone'  => ts("Do Not Phone"),
      'do_not_mail'   => ts("Do Not Mail"),
      'do_not_sms'    => ts("Do Not SMS"),
      'do_not_trade'  => ts("Do Not Trade"),
      'is_opt_out'    => ts("Is Opt Out"));
  }

  public static function getOptionalConsentFields() {
    return array(
      'consent_expiry_date' => E::ts("Use Expiry Date"),
      'consent_type'        => E::ts("Use Type"),
      'consent_terms'       => E::ts("Use Terms"),
      'consent_note'        => E::ts("Use Note"),
    );
  }

}
