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

use CRM_Gdprx_ExtensionUtil as E;

/**
 * Conset record edit/create form
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdprx_Form_ConsentEdit extends CRM_Core_Form {

  // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
  public function buildQuickForm(): void {
    $record_id  = _gdprx_int(CRM_Utils_Request::retrieve('id', 'String'));
    $contact_id = _gdprx_int(CRM_Utils_Request::retrieve('cid', 'Integer'));
    $multi      = (bool) CRM_Utils_Request::retrieve('multi', 'Integer');
    $config = CRM_Gdprx_Configuration::getSingleton();

    if ($record_id > 0) {
      CRM_Utils_System::setTitle(E::ts('Edit Consent Record'));
      $this->add('hidden', 'record_id', (string) $record_id);
    }
    else {
      CRM_Utils_System::setTitle(E::ts('Create Consent Record'));
      $this->add('hidden', 'record_id', '0');
    }

    $this->add('hidden', 'contact_id', (string) $contact_id);

    // add date, prefilled with current date
    $this->add(
        'datepicker',
        'consent_ui_date',
        E::ts('Date'),
        ['formatType' => 'activityDateTime'],
        TRUE
    );
    $this->setDefaults(['consent_ui_date' => date('Y-m-d H:i:s')]);

    if ($config->isEnabled('use_consent_expiry_date')) {
      $this->add(
          'datepicker',
          'consent_ui_expiry_date',
          E::ts('Expires'),
          ['formatType' => 'activityDateTime'],
          TRUE
      );
    }

    // add category dropdown from option group
    if ($multi && $record_id === 0) {
      $category_list = CRM_Gdprx_Consent::getCategoryList();
      $this->add('select',
          'consent_ui_category',
          E::ts('Categories'),
          $category_list,
          TRUE,
          ['class' => 'user-category crm-select2 huge', 'multiple' => 'multiple']
      );
      $this->setDefaults(['consent_ui_category' => array_keys($category_list)]);
    }
    else {
      $this->add('select',
          'consent_ui_category',
          E::ts('Category'),
          ['0' => E::ts('- please select -')] + CRM_Gdprx_Consent::getCategoryList(),
          TRUE,
          ['class' => 'user-category']
          );
    }

    // add source category
    $this->add('select',
      'consent_ui_source',
      E::ts('Source'),
      ['0' => E::ts('- please select -')] + CRM_Gdprx_Consent::getSourceList(),
      TRUE,
      ['class' => 'user-source']
    );

    // add type dropdown from option group
    if ($config->isEnabled('use_consent_type')) {
      $this->add('select',
        'consent_ui_type',
        E::ts('Type'),
        CRM_Gdprx_Consent::getTypeList(),
        FALSE,
        ['class' => 'user-type']
      );
    }

    // terms
    if ($config->isEnabled('use_consent_terms')) {
      $this->add('select',
        'consent_ui_terms',
        E::ts('Terms'),
        ['0' => E::ts('new')] + CRM_Gdprx_Terms::getList(),
        FALSE,
        ['class' => 'user-type huge']
      );
      $this->add(
        'textarea',
        'consent_ui_terms_full',
        E::ts('Terms'),
        ['class' => 'big']
      );
    }

    // optional note field
    if ($config->isEnabled('use_consent_note')) {
      $this->add(
        'textarea',
        'consent_ui_note',
        E::ts('Note'),
        ['class' => 'big']
      );
    }

    // assign config and data
    $this->assign('config', $config->getSettings());
    $this->assign('contact_id', $contact_id);
    $this->assign('record_id', $record_id);

    // add all term texts
    // TODO: use AJAX call instead
    $all_terms = CRM_Gdprx_Terms::getFullTexts();
    $this->assign('all_terms', json_encode($all_terms));

    // set default values
    $data = $record_id > 0 ? CRM_Gdprx_Consent::getRecord($record_id) : NULL;
    if ($data !== NULL) {
      // there is already a record
      $consent_date = (int) strtotime(_gdprx_str($data['consent_date'] ?? ''));
      $expiry_date_raw = (int) strtotime(_gdprx_str($data['consent_expiry_date'] ?? ''));

      $this->setDefaults([
        'consent_ui_category'    => $data['consent_category'] ?? NULL,
        'consent_ui_source'      => $data['consent_source'] ?? NULL,
        'consent_ui_type'        => $data['consent_type'] ?? NULL,
        'consent_ui_note'        => $data['consent_note'] ?? NULL,
        'consent_ui_terms'       => $data['consent_terms'] ?? NULL,
        'consent_ui_date'        => date('Y-m-d H:i:s', $consent_date),
        'consent_ui_expiry_date' => date('Y-m-d H:i:s', $expiry_date_raw),
      ]);
    }
    else {
      // pre-fill source only (dates have been set above); category is left
      // for the user to choose
      $this->setDefaults([
        'consent_ui_source'      => CRM_Gdprx_Consent::getSourceDefault(),
      ]);
    }

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    parent::buildQuickForm();
  }

  // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
  public function postProcess(): void {
    $values = $this->exportValues();

    // get terms_id
    $terms_id = NULL;
    if ((string) ($values['consent_ui_terms'] ?? '0') !== '0') {

      // an ID was set
      $terms_id = (int) $values['consent_ui_terms'];
    }
    elseif (isset($values['consent_ui_terms_full']) && $values['consent_ui_terms_full'] !== '') {
      $terms = CRM_Gdprx_Terms::getOrCreate($values['consent_ui_terms_full']);
      $terms_id = $terms->getID();
    }

    // get expiry date
    $expiry_date = $values['consent_ui_expiry_date'] ?? NULL;

    // resolve the date/time fields entered in the form
    $consent_date = date('YmdHis', (int) strtotime(
      CRM_Utils_Date::processDate($values['consent_ui_date'], $values['consent_ui_date_time'])
    ));
    $expiry_datetime = NULL;
    if ($expiry_date) {
      $expiry_datetime = date('YmdHis', (int) strtotime(
        CRM_Utils_Date::processDate($values['consent_ui_expiry_date'], $values['consent_ui_expiry_date_time'])
      ));
    }

    if (!isset($values['record_id']) || (int) $values['record_id'] === 0) {
      $categories = is_array($values['consent_ui_category'])
        ? $values['consent_ui_category']
        : [$values['consent_ui_category']];
      // create one record per selected category
      foreach ($categories as $category) {
        CRM_Gdprx_Consent::createConsentRecord(
            $values['contact_id'],
            $category,
            $values['consent_ui_source'],
            $consent_date,
            $values['consent_ui_note'] ?? NULL,
            $values['consent_ui_type'] ?? NULL,
            $terms_id,
            $expiry_datetime);
      }
    }
    else {
      // update
      CRM_Gdprx_Consent::updateConsentRecord(
        $values['record_id'],
        $values['contact_id'],
        $values['consent_ui_category'],
        $values['consent_ui_source'],
        $consent_date,
        $values['consent_ui_note'] ?? NULL,
        $values['consent_ui_type'] ?? NULL,
        $terms_id,
        $expiry_datetime);
    }

    parent::postProcess();
  }

}
