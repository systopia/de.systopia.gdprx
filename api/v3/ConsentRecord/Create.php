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
 * BPK Lookup
 *
 * @param array<string, mixed> $params
 *
 * @return array<string, mixed>|null
 */
function civicrm_api3_consent_record_create(array $params): ?array {
  $date = (!isset($params['date']) || $params['date'] === '')
    ? date('YmdHis')
    : date('YmdHis', (int) strtotime(_gdprx_str($params['date'])));

  $expiry_date = (!isset($params['expiry_date']) || $params['expiry_date'] === '')
    ? date('YmdHis')
    : date('YmdHis', (int) strtotime(_gdprx_str($params['expiry_date'])));

  $note = (!isset($params['note']) || $params['note'] === '') ? NULL : _gdprx_str($params['note']);

  // check the terms
  if (isset($params['terms']) && $params['terms'] !== '') {
    $terms_id = CRM_Gdprx_Terms::getOrCreate(_gdprx_str($params['terms']))->getID();
  }
  elseif (isset($params['terms_hash']) && $params['terms_hash'] !== '') {
    $terms_hash = _gdprx_str($params['terms_hash']);
    $terms = CRM_Gdprx_Terms::findByHash($terms_hash);
    if ($terms === NULL) {
      throw new CRM_Core_Exception("Terms '{$terms_hash}' are not on record.");
    }
    $terms_id = $terms->getID();
  }
  else {
    $terms_id = NULL;
  }

  return CRM_Gdprx_Consent::createConsentRecord(
    _gdprx_int($params['contact_id']),
    _gdprx_str($params['category']),
    _gdprx_str($params['source']),
    $date,
    $note,
    isset($params['type']) ? _gdprx_str($params['type']) : NULL,
    $terms_id,
    $expiry_date);
}

/**
 * BPK.lookup parameters
 *
 * @param array<string, mixed> $params
 */
function _civicrm_api3_consent_record_create_spec(array &$params): void {
  $params['contact_id'] = [
    'name'         => 'contact_id',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Contact ID',
    'description'  => 'Contact to record the consent for',
  ];
  $params['category'] = [
    'name'         => 'category',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Category',
  ];
  $params['source'] = [
    'name'         => 'source',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Source',
  ];
  $params['type'] = [
    'name'         => 'type',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Type',
  ];
  $params['date'] = [
    'name'         => 'date',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_DATE,
    'title'        => 'Consent Record Date',
    'description'  => 'Date the consent was given, defaults to now.',
  ];
  $params['expiry_date'] = [
    'name'         => 'expiry_date',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_DATE,
    'title'        => 'Consent Record Exipry Date',
    'description'  => 'Date the consent will expire',
  ];
  $params['note'] = [
    'name'         => 'note',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => 'Note',
  ];
  $params['terms'] = [
    'name'         => 'terms',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => 'Terms',
    'description'  => 'Full legal terms of the consent',
  ];
  $params['terms_hash'] = [
    'name'         => 'terms_hash',
    'api.required' => 0,
    'type'         => CRM_Utils_Type::T_STRING,
    'title'        => 'Terms (Hash)',
    'description'  => 'SHA1 hash of the full legal terms of the consent. '
    . 'If the hash is not nown to the system, this will throw an error.',
  ];
}
