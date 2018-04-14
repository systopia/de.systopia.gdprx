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
 * BPK Lookup
 */
function civicrm_api3_consent_record_create($params) {
  if (empty($params['date'])) {
    $date = date('YmdHis');
  } else {
    $date = date('YmdHis', strtotime($params['date']));
  }

  if (empty($params['expiry_date'])) {
    $expiry_date = date('YmdHis');
  } else {
    $expiry_date = date('YmdHis', strtotime($params['expiry_date']));
  }

  if (empty($params['note'])) {
    $note = NULL;
  } else {
    $note = $params['note'];
  }

  // check the terms
  if (!empty($params['terms'])) {
    $terms = CRM_Gdprx_Terms::getOrCreate($params['terms']);
  } elseif (!empty($params['terms_hash'])) {
    $terms = CRM_Gdprx_Terms::findByHash($params['terms_hash']);
    if (!$terms) {
      throw new Exception("Terms '{$params['terms_hash']}' are not on record.");
    }
  } else {
    $terms = NULL;
  }

  return CRM_Gdprx_Consent::createConsentRecord(
    $params['contact_id'],
    $params['category'],
    $params['source'],
    $date,
    CRM_Utils_Array::value('note', $params, NULL),
    CRM_Utils_Array::value('type', $params, NULL),
    $terms ? $terms->getID() : NULL,
    $expiry_date);
}


/**
 * BPK.lookup parameters
 */
function _civicrm_api3_consent_record_create_spec(&$params) {
  $params['contact_id'] = array(
    'name'         => 'contact_id',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Contact ID',
    'description'  => 'Looks up bPK for the given contact (for testing)',
    );
  $params['category'] = array(
    'name'         => 'category',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Category',
    );
  $params['source'] = array(
    'name'         => 'source',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Source',
    );
  $params['type'] = array(
    'name'         => 'type',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Type',
    );
  $params['date'] = array(
    'name'         => 'date',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_DATE,
    'title'        => 'Consent Record Date',
    'description'  => 'Date the consent was given, defaults to now.',
    );
  $params['expiry_date'] = array(
    'name'         => 'expiry_date',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_DATE,
    'title'        => 'Consent Record Exipry Date',
    'description'  => 'Date the consent will expire',
    );
  $params['note'] = array(
    'name'         => 'note',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => 'Note',
    );
  $params['terms'] = array(
    'name'         => 'terms',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => 'Terms',
    'description'  => 'Full legal terms of the consent',
    );
  $params['terms_hash'] = array(
    'name'         => 'terms_hash',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => 'Terms (Hash)',
    'description'  => 'SHA1 hash of the full legal terms of the consent. If the hash is not nown to the system, this will throw an error.',
    );
}
