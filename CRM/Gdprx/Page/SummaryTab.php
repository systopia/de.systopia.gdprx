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

class CRM_Gdprx_Page_SummaryTab extends CRM_Core_Page {
  public function run() {
    // build a consent table
    $contact_id  = CRM_Utils_Request::retrieve('cid', 'Integer');
    $config      = CRM_Gdprx_Configuration::getSingleton();
    $civi_config = CRM_Core_Config::singleton();
    $records     = array();

    // load option group IDs
    $groups = $config->getOptionGroups();

    // query the DB
    $data = CRM_Core_DAO::executeQuery("
      SELECT
        record.id       AS record_id,
        record.date     AS record_date,
        record.expiry   AS record_expiry,
        category.label  AS record_category,
        source.label    AS record_source,
        type.label      AS record_type,
        terms.name      AS record_terms_name,
        terms.id        AS record_terms_id,
        terms.text_full AS record_terms_full,
        record.note     AS record_note
      FROM civicrm_value_gdpr_consent record
      LEFT JOIN civicrm_gdpr_terms    terms      ON terms.id = terms_id
      LEFT JOIN civicrm_option_value  category   ON category.value = record.category AND category.option_group_id = %2
      LEFT JOIN civicrm_option_value  source     ON source.value   = record.source   AND source.option_group_id = %3
      LEFT JOIN civicrm_option_value  type       ON type.value     = record.type     AND type.option_group_id = %4
      WHERE entity_id = %1
      ORDER BY record.date DESC;", array(
        1 => array($contact_id,        'Integer'),
        2 => array($groups['consent_category']['id'], 'Integer'),
        3 => array($groups['consent_source']['id'],   'Integer'),
        4 => array($groups['consent_type']['id'],     'Integer'),
      ));
    while ($data->fetch()) {
      $records[] = array(
        'record_id'          => $data->record_id,
        'record_date'        => CRM_Utils_Date::customFormat($data->record_date, $civi_config->dateformatFull),
        'record_date_full'   => CRM_Utils_Date::customFormat($data->record_date, $civi_config->dateformatDatetime),
        'record_expiry'      => $data->record_expiry ? CRM_Utils_Date::customFormat($data->record_expiry, $civi_config->dateformatFull) : '',
        'record_expiry_full' => $data->record_expiry ? CRM_Utils_Date::customFormat($data->record_expiry, $civi_config->dateformatDatetime) : '',
        'record_category'    => $data->record_category,
        'record_source'      => $data->record_source,
        'record_type'        => $data->record_type,
        'record_terms_name'  => $data->record_terms_name,
        'record_terms_full'  => $data->record_terms_full,
        'record_terms_id'    => $data->record_terms_id,
        'record_note_short'  => count($data->record_note) > 16 ? (substr($data->record_note, 0, 13) . '...') : $data->record_note,
        'record_note'        => $data->record_note,
      );
    }

    $this->assign('records', $records);
    $this->assign('gdprx',   $config->getSettings());
    $this->assign('contact_id', $contact_id);
    parent::run();
  }

  /**
   * Get the record count
   */
  public static function getRecordCount($contact_id) {
    return CRM_Core_DAO::singleValueQuery("SELECT COUNT(id) FROM civicrm_value_gdpr_consent WHERE entity_id = %1",
              array(1 => array($contact_id, 'Integer')));
  }
}
