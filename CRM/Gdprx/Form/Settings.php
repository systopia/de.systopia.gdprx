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

    $this->add('select',
               "consent_source_default",
               E::ts("Default Source"),
               ['' => E::ts("- no default -")] + CRM_Gdprx_Consent::getSourceList(),
               false,
               ['class' => 'user-source']
    );
    $this->setDefaults(['consent_source_default' => CRM_Gdprx_Consent::getSourceDefault()]);

    // add privacy defaults
    $this->addElement('checkbox',
                      "disable_privacy_edit",
                      E::ts("Disable Editing of Privacy Settings"));

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
    $config->setSetting("disable_privacy_edit", CRM_Utils_Array::value("disable_privacy_edit", $values, FALSE));

    $config->writeSettings();

    // write out now default
    self::setDefaultOptionGroupValue('consent_source', $values['consent_source_default']);


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

  /**
   * Set the new default value of the option group
   *
   * @param $option_group_id integer|string
   *
   * @param $new_value string
   */
  public static function setDefaultOptionGroupValue($option_group_id, $new_value)
  {
    $is_default_already = false;

    // unset all (except the new default)
    $current_defaults = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => $option_group_id,
        'is_default'      => 1,
        'option.limit'    => 0,
        'sequential'      => 0,
        'return'          => 'id,value,is_default'
    ]);
    foreach ($current_defaults['values'] as $current_default) {
      if ($current_default['value'] == $new_value) {
        $is_default_already = true;
      } else {
        civicrm_api3('OptionValue', 'create', ['id' => $current_default['id'], 'is_default' => 0]);
      }
    }

    // finally, mark the new value as default, if that's not teh case already
    if (!$is_default_already && !empty($new_value)) {
      try {
        $new_default_id = civicrm_api3('OptionValue', 'getvalue', [
            'option_group_id' => $option_group_id,
            'value'           => $new_value,
            'return'          => 'id'
        ]);
        civicrm_api3('OptionValue', 'create', [
            'id'         => $new_default_id,
            'is_default' => 1]);
      } catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Session::setStatus(
            E::ts("Couldn't set default value. Error was: %1", [1 => $ex->getMessage()]),
            E::ts("Set Default Failed"),
            "error"
        );
      }
    }
  }
}
