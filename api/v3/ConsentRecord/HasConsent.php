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
 * See if the contact has given consent (positive or negative) to the given category
 */
function civicrm_api3_consent_record_has_consent($params) {
  // prepare date
  $date = date('YmdHis', strtotime($params['date']));
  if (empty($date)) {
    return civicrm_api3_create_error("Invalid date given!");
  }

  // prepare positive types
  if (!is_array($params['positive_types'])) {
    $params['positive_types'] = explode(',', $params['positive_types']);
  }
  $positive_types = array();
  foreach ($params['positive_types'] as $positive_type) {
    $positive_type = (int) $positive_type;
    if ($positive_type) {
      $positive_types[] = $positive_type;
    }
  }
  if (empty($positive_types)) {
    return civicrm_api3_create_error("Invalid positive_types given!");
  }

  // prepare negative types
  if (!is_array($params['negative_types'])) {
    $params['negative_types'] = explode(',', $params['negative_types']);
  }
  $negative_types = array();
  foreach ($params['negative_types'] as $negative_type) {
    $negative_type = (int) $negative_type;
    if ($negative_type) {
      $negative_types[] = $negative_type;
    }
  }
  if (empty($negative_types)) {
    return civicrm_api3_create_error("Invalid negative_types given!");
  }

  // run the query
  $consent_date = CRM_Gdprx_Consent::hasConsent(
      $params['contact_id'],
      $params['category'],
      $params['type'],
      $date,
      $positive_types,
      $negative_types);

  $null = NULL;
  if ($consent_date) {
    return civicrm_api3_create_success(1, $params, 'ConsentRecord', 'has_consent', $null, array(
        'has_consent'  => 1,
        'consent_date' => $consent_date
    ));
  } else {
    return civicrm_api3_create_success(0, $params, 'ConsentRecord', 'has_consent', $null, array(
        'has_consent'  => 0
    ));
  }
}


/**
 * BPK.lookup parameters
 */
function _civicrm_api3_consent_record_has_consent_spec(&$params) {
  $params['contact_id'] = array(
    'name'         => 'contact_id',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Contact ID',
    'description'  => 'Contact ID to query for a currently existing consent',
    );
  $params['category'] = array(
    'name'         => 'category',
    'api.required' => 1,
    'type'         => CRM_Utils_Type::T_INT,
    'title'        => 'Consent Category',
    );
  $params['type'] = array(
      'name'         => 'type',
      'api.default'  => 1,
      'type'         => CRM_Utils_Type::T_STRING,
      'title'        => 'Positive?',
      'description'  => 'Are you looking for a "positive/1" (consent) or "negative/0" (opt-out)',
  );
  $params['date'] = array(
      'name'         => 'date',
      'api.default'  => 'now',
      'type'         => CRM_Utils_Type::T_STRING,
      'title'        => 'Date and Time',
      'description'  => 'For what point in time? Default is "now"'
  );
  $params['positive_types'] = array(
      'name'         => 'positive_types',
      'api.default'  => array(2,4,5),
      'type'         => CRM_Utils_Type::T_STRING,
      'title'        => 'Positive Consent Types',
      'description'  => 'Define the positive consent types, default is 2,4,5',
  );
  $params['negative_types'] = array(
      'name'         => 'negative_types',
      'api.default'  => array(3),
      'type'         => CRM_Utils_Type::T_STRING,
      'title'        => 'Negative Consent Types',
      'description'  => 'Define the negative consent types, default is 3',
  );
}
