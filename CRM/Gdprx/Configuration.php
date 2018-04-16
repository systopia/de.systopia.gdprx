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

/**
 * Generic functions regarding the consent records
 */
class CRM_Gdprx_Configuration {

  private static $singleton = NULL;
  private $config;

  /**
   * Get the configuration singleton
   */
  public static function getSingleton() {
    if (self::$singleton === NULL) {
      self::$singleton = new CRM_Gdprx_Configuration();
    }
    return self::$singleton;
  }

  private function __construct() {
    // load current config
    $this->config = CRM_Core_BAO_Setting::getItem('de.systopia.gdprx', 'gdprx_settings');
    if (empty($this->config)) {
      // TODO: default values?
      $this->config = array();
    }
  }

  /**
   * Get the given setting value
   */
  public function getSetting($name, $default = NULL) {
    return CRM_Utils_Array::value($name, $this->config, $default);
  }

  /**
   * Get all current settings
   */
  public function getSettings() {
    return $this->config;
  }

  /**
   * Set the given setting to value
   */
  public function setSetting($name, $value, $write = FALSE) {
    $this->config[$name] = $value;
    if ($write) {
      $this->writeSettings();
    }
  }

  /**
   * Write the current settings to DB
   */
  public function writeSettings() {
    CRM_Core_BAO_Setting::setItem($this->config, 'de.systopia.gdprx', 'gdprx_settings');
  }

  /**
   * inject the contact's default privacy settings
   *  if enabled
   */
  public function addDefaultPrivacySettings(&$params) {
    if ($this->getSetting('default_privacy_settings_enabled')) {
      $params['do_not_email'] = $this->getSetting('default_privacy_do_not_email');
      $params['do_not_phone'] = $this->getSetting('default_privacy_do_not_phone');
      $params['do_not_mail']  = $this->getSetting('default_privacy_do_not_mail');
      $params['do_not_sms']   = $this->getSetting('default_privacy_do_not_sms');
      $params['do_not_trade'] = $this->getSetting('default_privacy_do_not_trade');
      $params['is_opt_out']   = $this->getSetting('default_privacy_is_opt_out');
    }
  }
}