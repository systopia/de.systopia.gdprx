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
 * Manages the civicrm_gdpr_terms table,
 *  holding the records of the legal terms of the consents given
 */
class CRM_Gdprx_Terms {

  protected $record;

  protected function __construct($record) {
    $this->record = $record;
  }

  /**
   * get the internal ID of this record
   */
  public function getID() {
    return $this->record->record_id;
  }

  /**
   * Get the record
   */
  public static function getOrCreate($terms_full) {
    $hash = sha1($terms_full);
    $terms = self::findByHash($hash);
    if ($terms) {
      return $terms;
    }

    // doesn't exist -> create
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_gdpr_terms (create_date,name,text_hash,text_full) VALUES (NOW(),%1,%2,%3);",
      array(1 => array(substr($terms_full, 0, 29) . '...', 'String'),
            2 => array($hash, 'String'),
            3 => array($terms_full, 'String')));
    // and return the result
    return self::findByHash($hash);
  }


  /**
   * get an existing terms by the has value
   */
  public static function findByHash($terms_hash) {
    $record = CRM_Core_DAO::executeQuery("SELECT *, id AS record_id FROM civicrm_gdpr_terms WHERE text_hash = %1 LIMIT 1",
      array(1 => array($terms_hash, 'String')));
    if ($record->fetch()) {
      return new CRM_Gdprx_Terms($record);
    } else {
      return NULL;
    }
  }

  /**
   * get an existing terms by the has value
   */
  public static function findByID($id) {
    $record = CRM_Core_DAO::executeQuery("SELECT *, id AS record_id FROM civicrm_gdpr_terms WHERE id = %1",
      array(1 => array($id, 'Integer')));
    if ($record->fetch()) {
      return new CRM_Gdprx_Terms($record);
    } else {
      return NULL;
    }
  }
}