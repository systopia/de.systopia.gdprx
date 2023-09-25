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
  private static $types_list    = NULL;
  private static $null          = NULL;

  /**
   * Get a list id -> label for the categories
   */
  public static function getCategoryList() {
    if (self::$category_list === NULL) {
      self::$category_list = array();
      $query = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'consent_category',
        'option.limit'    => 0,
        'option.sort'     => 'weight asc',
        'sequential'      => 1,
        'is_active'       => 1,
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
        'option.sort'     => 'weight asc',
        'sequential'      => 1,
        'is_active'       => 1,
        'return'          => 'value,label'));
      foreach ($query['values'] as $option_value) {
        self::$sources_list[$option_value['value']] = $option_value['label'];
      }
    }
    return self::$sources_list;
  }

  /**
   * Get the default value for source, or null if there is none
   *
   * @return string|null
   */
  public static function getSourceDefault() {
    static $source_default = null;
    if ($source_default === null) {
      $source_default = '';
      try {
        $source_default = civicrm_api3('OptionValue', 'getvalue', [
            'option_group_id' => 'consent_source',
            'option.limit'    => 1,
            'option.sort'     => 'weight desc',
            'is_active'       => 1,
            'is_default'      => 1,
            'return'          => 'value'
        ]);
      } catch (CiviCRM_API3_Exception $ex) {
        // probably not found...
      }
    }
    return $source_default;
  }

  /**
   * Get the default value for source, or null if there is none
   *
   * @return string|null
   */
  public static function getCategoryDefault() {
    static $category_default = null;
    if ($category_default === null) {
      $category_default = '';
      try {
        $category_default = civicrm_api3('OptionValue', 'getvalue', [
            'option_group_id' => 'consent_category',
            'option.limit'    => 1,
            'option.sort'     => 'weight desc',
            'is_active'       => 1,
            'is_default'      => 1,
            'return'          => 'value'
        ]);
      } catch (CiviCRM_API3_Exception $ex) {
        // probably not found...
      }
    }
    return $category_default;
  }

  /**
   * Get a list id -> label for the sources
   */
  public static function getTypeList() {
    if (self::$types_list === NULL) {
      self::$types_list = array();
      $query = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'consent_type',
        'option.limit'    => 0,
        'option.sort'     => 'weight asc',
        'sequential'      => 1,
        'is_active'       => 1,
        'return'          => 'value,label'));
      foreach ($query['values'] as $option_value) {
        self::$types_list[$option_value['value']] = $option_value['label'];
      }
    }
    return self::$types_list;
  }

  /**
   * add a new user consent entry for the contact
   */
  public static function createConsentRecord($contact_id, $category, $source, $date = 'now', $note = '', $type = NULL, $terms_id = NULL, $expiry_date = NULL) {
    return self::updateConsentRecord('-1', $contact_id, $category, $source, $date, $note, $type, $terms_id, $expiry_date);
  }

  /**
   * update existing consent record
   */
  public static function updateConsentRecord($record_id, $contact_id, $category, $source, $date = 'now', $note = '', $type = NULL, $terms_id = NULL, $expiry_date = NULL) {
    if (GDPRX_DEBUG_LOGGING) {
      Civi::log()->debug("create/update consent record: {$contact_id}, {$category}, {$source}, {$date}, {$note} {$type} {$terms_id} {$expiry_date}");
    }

    // look up SOURCE
    $original_source = $source;
    if (!is_numeric($source)) {
      $source = CRM_Gdprx_CustomData::getOptionValue('consent_source', $source, 'label');
    }
    if (empty($source)) {
      if (GDPRX_DEBUG_LOGGING) {
        Civi::log()->debug("Couldn't map source '{$original_source}'");
      }
      return;
    }

    // look up CATEGORY
    $original_category = $category;
    if (!is_numeric($category)) {
      $category = CRM_Gdprx_CustomData::getOptionValue('consent_category', $category, 'label');
    }
    if (empty($category)) {
      if (GDPRX_DEBUG_LOGGING) {
        Civi::log()->debug("Couldn't map category '{$original_category}'");
      }
      return;
    }

    // create record
    $data = array(
      'consent.consent_date'     => date('YmdHis', strtotime($date)),
      'consent.consent_category' => $category,
      'consent.consent_source'   => $source,
    );

    if (!empty($expiry_date)) {
      $data['consent.consent_expiry_date'] = date('YmdHis', strtotime($expiry_date));
    } else {
      $data['consent.consent_expiry_date'] = '';
    }

    if (!empty($type)) {
      $data['consent.consent_type'] = $type;
    } else {
      $data['consent.consent_type'] = '';
    }

    if (!empty($terms_id)) {
      $data['consent.consent_terms'] = $terms_id;
    } else {
      $data['consent.consent_terms'] = '';
    }

    if ($note !== null) {
      $data['consent.consent_note'] = $note;
    }

    // resolve custom fields
    $symbolised_data = $data;
    CRM_Gdprx_CustomData::resolveCustomFields($data, array('consent'));

    // since this is a multi-entry group, we need to clarify the index (-1 = new entry)
    $request = array('entity_id' => $contact_id);
    foreach ($data as $key => $value) {
      $request[$key . ':'. $record_id] = $value;
    }

    $record = civicrm_api3('CustomValue', 'create', $request);

    if ($record_id == '-1') {
      self::callConsentHook('create', $contact_id, NULL, $symbolised_data);
    } else {
      self::callConsentHook('update', $contact_id, $record_id, $symbolised_data);
    }

    return $record;
  }

  /**
   * Get a valid consent record.
   *
   * @param $contact_id
   * @param $category
   * @param $date
   * @param array $positive_types
   * @param array $negative_types
   *
   * @return string the date of the given consent, or NULL if no currently valid consent recorded
   */
  public static function hasConsent($contact_id, $category, $positive = TRUE, $date = 'now', $positive_types = array(2,4,5), $negative_types = array(3)) {
    // if we're looking for negative consent, swap the search patterns
    if (!$positive) {
      $tmp = $positive_types;
      $positive_types = $negative_types;
      $negative_types = $tmp;
    }
    $contact_id = (int) $contact_id;
    $category = (int) $category;
    $date = date('YmdHis', strtotime($date));

    // build query
    $positive_types_list = implode(',', $positive_types);
    $negative_types_list = implode(',', $negative_types);
    $query_sql = "
    SELECT
      MAX(positive.date) last_positive_consent, 
      MAX(negative.date) last_negative_consent 
    FROM civicrm_contact contact
    LEFT JOIN civicrm_value_gdpr_consent positive ON positive.entity_id = contact.id 
                                                  AND positive.type IN ({$positive_types_list})
                                                  AND positive.category = {$category}
                                                  AND positive.date <= '{$date}'
    LEFT JOIN civicrm_value_gdpr_consent negative ON negative.entity_id = contact.id 
                                                  AND negative.type IN ({$negative_types_list})
                                                  AND negative.category = {$category}
                                                  AND negative.date <= '{$date}'
    WHERE contact.id = {$contact_id}
    GROUP BY contact.id;";
    $query = CRM_Core_DAO::executeQuery($query_sql);
    $query->fetch();
    if ($query->last_positive_consent) {
      if ($query->last_negative_consent) {
        // negative wins if the date is the same
        if ($query->last_positive_consent > $query->last_negative_consent
            || (!$positive && $query->last_positive_consent == $query->last_negative_consent)) {
          return $query->last_positive_consent;
        } else {
          return NULL;
        }
      } else {
        return $query->last_positive_consent;
      }
    } else {
      return NULL;
    }
  }

  /**
   * get a consent record by ID
   */
  public static function getRecord($id) {
    $data = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_value_gdpr_consent WHERE id = %1",
      array(1 => array($id, 'Integer')));
    if ($data->fetch()) {
      return array(
        'entity_id'           => $data->entity_id,
        'consent_date'        => $data->date,
        'consent_expiry_date' => $data->expiry,
        'consent_category'    => $data->category,
        'consent_source'      => $data->source,
        'consent_type'        => $data->type,
        'consent_terms'       => $data->terms_id,
        'consent_note'        => $data->note,
      );
    } else {
      return NULL;
    }
  }

  /**
   * This hook is called after a consent record has been created or updated
   *
   * You can implement this hook e.g. to update the contact's privacy settings
   *  based on the recorded consents
   *
   * @param $mode       string 'create' if new record or 'update'
   * @param $contact_id int    contact this record belongs to
   * @param $record_id  int    record ID if this is an 'update', NULL otherwise
   * @param $data       array  the content of the record written
   *
   * @return mixed
   */
  public static function callConsentHook($mode, $contact_id, $record_id, $data) {
    $names = ['mode', 'contact_id', 'record_id', 'data'];
    return CRM_Utils_Hook::singleton()->invoke($names, $mode, $contact_id, $record_id, $data, self::$null, self::$null, 'civicrm_gdprx_postConsent');
  }
}
