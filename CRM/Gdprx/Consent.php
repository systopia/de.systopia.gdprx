<?php
/*-------------------------------------------------------+
| SYSTOPIA GDPR Compliance Extension                     |
| Copyright (C) 2017 SYSTOPIA                            |
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
class CRM_Gdprx_Consent {

  private static $category_list = NULL;
  private static $sources_list  = NULL;


  /**
   * Get a list id -> label for the categories
   */
  public static function getCategoryList() {
    if (self::$category_list === NULL) {
      self::$category_list = array();
      $query = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'consent_category',
        'option.limit'    => 0,
        'sequential'      => 1,
        'return'          => 'value,label'));
      foreach ($query['values'] as $option_value) {
        self::$category_list[$option_value['value']] = $option_value['label'];
      }
    }
    return self::$category_list;
  }

  /**
   * Get a list id -> label for the sources
   */
  public static function getSourceList() {
    if (self::$sources_list === NULL) {
      self::$sources_list = array();
      $query = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'consent_source',
        'option.limit'    => 0,
        'sequential'      => 1,
        'return'          => 'value,label'));
      foreach ($query['values'] as $option_value) {
        self::$sources_list[$option_value['value']] = $option_value['label'];
      }
    }
    return self::$sources_list;
  }

  /**
   * add a new user consent entry for the contact
   */
  public static function createConsentRecord($contact_id, $category, $source, $date = 'now', $note = '') {
    // look up values
    if (!is_numeric($source)) {
      $source = CRM_Core_OptionGroup::getValue('consent_source', $source, 'label');
    }
    if (!is_numeric($category)) {
      $category = CRM_Core_OptionGroup::getValue('consent_category', $category, 'label');
    }

    if (empty($category) || empty($source)) {
      // these fields are mandatory
      CRM_Core_Error::debug_log_message("Couldn't create consent record, category/source missing!");
      return;
    }

    $data = array(
      'consent.consent_date'     => date('YmdHis', strtotime($date)),
      'consent.consent_category' => $category,
      'consent.consent_source'   => $source,
      'consent.consent_note'     => $note,
    );

    // resolve custom fields
    CRM_Gdprx_CustomData::resolveCustomFields($data, array('consent'));

    // since this is a multi-entry group, we need to clarify the index (-1 = new entry)
    $request = array('entity_id' => $contact_id);
    foreach ($data as $key => $value) {
      $request[$key . ':-1'] = $value;
    }

    civicrm_api3('CustomValue', 'create', $request);
  }
}