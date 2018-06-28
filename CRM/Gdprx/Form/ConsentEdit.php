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

/**
 * Conset record edit/create form
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Gdprx_Form_ConsentEdit extends CRM_Core_Form {

  public function buildQuickForm() {
    $this->record_id  = (int) CRM_Utils_Request::retrieve('id', 'String');
    $this->contact_id = CRM_Utils_Request::retrieve('cid', 'Integer');
    $config = CRM_Gdprx_Configuration::getSingleton();

    if ($this->record_id > 0) {
      CRM_Utils_System::setTitle(E::ts("Edit Consent Record"));
      $this->add('hidden', 'record_id', $this->record_id);
    } else {
      CRM_Utils_System::setTitle(E::ts("Create Consent Record"));
      $this->add('hidden', 'record_id', 0);
    }

    $this->add('hidden', 'contact_id', $this->contact_id);

    // add date, prefilled with current date
    $currentVer = CRM_Core_BAO_Domain::version();
    $this->assign('civi_version', $currentVer);
    if (version_compare($currentVer, '4.7') < 0) {
      // this is 4.6
      $this->addDateTime('consent_ui_date', ts('Date'), TRUE);
      list($date_defaults['consent_ui_date'], $date_defaults['consent_ui_date_time']) = CRM_Utils_Date::setDateDefaults(date('Y-m-d H:i:s'), 'activityDateTime');
      $this->setDefaults($date_defaults);
      $this->assign('needs_calendar_include', 1);

      if ($config->getSetting('use_consent_expiry_date')) {
        $this->addDateTime('consent_ui_expiry_date', ts('Date'), TRUE);
      }
    } else {
      // add date, prefilled with current date
      $this->add(
        'datepicker',
        'consent_ui_date',
        E::ts("Date"),
        array('class' => ''),
        TRUE,
        array('time' => FALSE)
      );
      $this->setDefaults(array('consent_ui_date' => date('Y-m-d')));

      if ($config->getSetting('use_consent_expiry_date')) {
        $this->add(
          'datepicker',
          'consent_ui_expiry_date',
          E::ts("Expires"),
          array('class' => ''),
          FALSE,
          array('time' => FALSE)
        );
      }
    }

    // add category dropdown from option group
    $this->add('select',
      "consent_ui_category",
      E::ts("Category"),
      array('0' => E::ts("- please select -")) + CRM_Gdprx_Consent::getCategoryList(),
      TRUE,
      array('class' => 'user-category')
    );

    // add source category
    $this->add('select',
      "consent_ui_source",
      E::ts("Source"),
      array('0' => E::ts("- please select -")) + CRM_Gdprx_Consent::getSourceList(),
      TRUE,
      array('class' => 'user-source')
    );

    // add type dropdown from option group
    if ($config->getSetting('use_consent_type')) {
      $this->add('select',
        "consent_ui_type",
        E::ts("Type"),
        CRM_Gdprx_Consent::getTypeList(),
        FALSE,
        array('class' => 'user-type')
      );
    }

    // terms
    if ($config->getSetting('use_consent_terms')) {
      $this->add('select',
        "consent_ui_terms",
        E::ts("Terms"),
        array('0' => E::ts("new")) + CRM_Gdprx_Terms::getList(),
        FALSE,
        array('class' => 'user-type huge')
      );
      $this->add(
        'textarea',
        "consent_ui_terms_full",
        E::ts("Terms"),
        array('class' => 'big')
      );
    }

    // remark (note)
    if ($config->getSetting('use_consent_note')) {
      $this->add(
        'textarea',
        "consent_ui_note",
        E::ts("Note"),
        array('class' => 'big')
      );
    }

    // assign config and data
    $this->assign('config',     $config->getSettings());
    $this->assign('contact_id', $this->contact_id);
    $this->assign('record_id',  $this->record_id);

    // add all term texts
    // TODO: use AJAX call instead
    $all_terms = CRM_Gdprx_Terms::getFullTexts();
    $this->assign('all_terms', json_encode($all_terms));

    // set default values
    if ($this->record_id > 0) {
      // there is already a record
      $data = CRM_Gdprx_Consent::getRecord($this->record_id);
      list($date_values['consent_ui_date'], $date_values['consent_ui_date_time']) = CRM_Utils_Date::setDateDefaults(date('Y-m-d H:i:s', strtotime($data['consent_date'])), 'activityDateTime');
      list($date_values['consent_ui_expiry_date'], $date_values['consent_ui_expiry_date_time']) = CRM_Utils_Date::setDateDefaults(date('Y-m-d H:i:s', strtotime($data['consent_ui_expiry_date'])), 'activityDateTime');

      $this->setDefaults(array(
        'consent_ui_category'    => $data['consent_category'],
        'consent_ui_source'      => $data['consent_source'],
        'consent_ui_type'        => $data['consent_type'],
        'consent_ui_note'        => $data['consent_note'],
        'consent_ui_terms'       => $data['consent_terms'],
      ) + $date_values);
    } else {
      // set default values? dates have been set above...
      // TODO:
    }

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    // get terms_id
    $terms_id = NULL;
    if (!empty($values['consent_ui_terms'])) {

      // an ID was set
      $terms_id = (int) $values['consent_ui_terms'];
    } elseif (!empty($values['consent_ui_terms_full'])) {
      $terms = CRM_Gdprx_Terms::getOrCreate($values['consent_ui_terms_full']);
      $terms_id = $terms->getID();
    }


    // get expiry date
    $expiry_date = CRM_Utils_Array::value('consent_ui_expiry_date', $values);

    if (empty($values['record_id'])) {
      // create a new record
      CRM_Gdprx_Consent::createConsentRecord(
        $values['contact_id'],
        $values['consent_ui_category'],
        $values['consent_ui_source'],
        date('YmdHis', strtotime(CRM_Utils_Date::processDate($values['consent_ui_date'], $values['consent_ui_date_time']))),
        CRM_Utils_Array::value('consent_ui_note', $values, NULL),
        CRM_Utils_Array::value('consent_ui_type', $values, NULL),
        $terms_id,
        $expiry_date ? date('YmdHis', strtotime(CRM_Utils_Date::processDate($values['consent_ui_expiry_date'], $values['consent_ui_expiry_date_time']))) : NULL);
    } else {
      // update
      CRM_Gdprx_Consent::updateConsentRecord(
        $values['record_id'],
        $values['contact_id'],
        $values['consent_ui_category'],
        $values['consent_ui_source'],
        date('YmdHis', strtotime(CRM_Utils_Date::processDate($values['consent_ui_date'], $values['consent_ui_date_time']))),
        CRM_Utils_Array::value('consent_ui_note', $values, NULL),
        CRM_Utils_Array::value('consent_ui_type', $values, NULL),
        $terms_id,
       $expiry_date ? date('YmdHis', strtotime(CRM_Utils_Date::processDate($values['consent_ui_expiry_date'], $values['consent_ui_expiry_date_time']))) : NULL);
    }

    parent::postProcess();
  }

}
