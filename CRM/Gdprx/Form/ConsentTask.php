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
class CRM_Gdprx_Form_ConsentTask extends CRM_Contact_Form_Task {

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts("Add Consent Records"));
    $config = CRM_Gdprx_Configuration::getSingleton();

    // add date, prefilled with current date
    $currentVer = CRM_Core_BAO_Domain::version();
    $this->assign('civi_version', $currentVer);
    // add date, prefilled with current date
    $this->add(
      'datepicker',
      'consent_ui_date',
      E::ts("Date"),
      ['class' => ''],
      TRUE,
      ['time' => FALSE]
    );
    $this->setDefaults(['consent_ui_date' => date('Y-m-d')]);

    if ($config->getSetting('use_consent_expiry_date')) {
      $this->add(
        'datepicker',
        'consent_ui_expiry_date',
        E::ts("Expires"),
        ['class' => ''],
        FALSE,
        ['time' => FALSE]
      );
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

    // add all term texts
    // TODO: use AJAX call instead
    $all_terms = CRM_Gdprx_Terms::getFullTexts();
    $this->assign('all_terms', json_encode($all_terms));

    // set default values
    // TODO:

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Create'),
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

    // create a new records
    foreach ($this->_contactIds as $contact_id) {
      CRM_Gdprx_Consent::createConsentRecord(
        $contact_id,
        $values['consent_ui_category'],
        $values['consent_ui_source'],
        date('YmdHis', strtotime($values['consent_ui_date'])),
        CRM_Utils_Array::value('consent_ui_note', $values, NULL),
        CRM_Utils_Array::value('consent_ui_type', $values, NULL),
        $terms_id,
        $expiry_date ? date('YmdHis', strtotime($expiry_date)) : NULL);
    }

    parent::postProcess();
  }

  /**
   * get the template file name
   */
  public function getTemplateFileName() {
    return 'CRM/Gdprx/Form/ConsentEdit.tpl';
  }
}
