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

declare(strict_types = 1);

/**
 * Manages the civicrm_gdpr_terms table,
 *  holding the records of the legal terms of the consents given
 */
class CRM_Gdprx_Terms {

  protected \CRM_Core_DAO $record;

  protected function __construct(\CRM_Core_DAO $record) {
    $this->record = $record;
  }

  /**
   * get the internal ID of this record
   *
   * @return mixed
   */
  public function getID() {
    return $this->record->record_id;
  }

  /**
   * Get the record
   *
   * @param string $terms_full
   */
  public static function getOrCreate($terms_full): ?self {
    $hash = sha1($terms_full);
    $terms = self::findByHash($hash);
    if ($terms !== NULL) {
      return $terms;
    }

    // doesn't exist -> create
    CRM_Core_DAO::executeQuery('
      INSERT INTO civicrm_gdpr_terms (create_date,name,text_hash,text_full) VALUES (NOW(),%1,%2,%3);',
      [
        1 => [substr($terms_full, 0, 29) . '...', 'String'],
        2 => [$hash, 'String'],
        3 => [$terms_full, 'String'],
      ]);
    // and return the result
    return self::findByHash($hash);
  }

  /**
   * get an existing terms by the hash value
   *
   * @param string $terms_hash
   */
  public static function findByHash($terms_hash): ?self {
    $record = CRM_Core_DAO::executeQuery(
      'SELECT *, id AS record_id FROM civicrm_gdpr_terms WHERE text_hash = %1 LIMIT 1',
      [1 => [$terms_hash, 'String']]
    );
    if ($record->fetch()) {
      return new CRM_Gdprx_Terms($record);
    }
    else {
      return NULL;
    }
  }

  /**
   * get an existing terms by its ID
   *
   * @param int $id
   */
  public static function findByID($id): ?self {
    $record = CRM_Core_DAO::executeQuery('SELECT *, id AS record_id FROM civicrm_gdpr_terms WHERE id = %1',
      [1 => [$id, 'Integer']]);
    if ($record->fetch()) {
      return new CRM_Gdprx_Terms($record);
    }
    else {
      return NULL;
    }
  }

  /**
   * Get a list of all known terms
   *
   * @return array<int|string, mixed>
   */
  public static function getList(): array {
    $list = [];
    $record = CRM_Core_DAO::executeQuery(
      'SELECT id AS term_id, name FROM civicrm_gdpr_terms ORDER BY create_date DESC;'
    );
    while ($record->fetch()) {
      $list[$record->term_id] = $record->name;
    }
    return $list;
  }

  /**
   * Get a list ID -> full text
   *
   * @return array<int|string, mixed>
   */
  public static function getFullTexts(): array {
    $list = [];
    $record = CRM_Core_DAO::executeQuery('SELECT id AS term_id, text_full FROM civicrm_gdpr_terms;');
    while ($record->fetch()) {
      $list[$record->term_id] = $record->text_full;
    }
    return $list;
  }

}
