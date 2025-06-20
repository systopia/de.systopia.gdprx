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

use CRM_Gdprx_ExtensionUtil as E;

class CRM_Gdprx_ConsentUI {

  /**
   * handles the build form hook action
   */
  public static function buildForm($formName, &$form) {
    if (!empty($form->_contactId)) {
      // we are in edit mode, nothing to do here!
      return;
    }

    // check if this is enabled
    $config = CRM_Gdprx_Configuration::getSingleton();
    if (!$config->getSetting('enforce_record_for_new_contacts')) {
      return;
    }

    // add date, prefilled with current date
    $currentVer = CRM_Core_BAO_Domain::version();
    $form->assign('civi_version', $currentVer);
    // add date, prefilled with current date
    $form->add(
      'datepicker',
      'consent_ui_date',
      E::ts("Date"),
      ['class' => ''],
      TRUE,
      ['time' => FALSE]
    );
    $form->setDefaults(['consent_ui_date' => date('Y-m-d')]);

    if ($config->getSetting('use_consent_expiry_date')) {
      $form->add(
        'datepicker',
        'consent_ui_expiry_date',
        E::ts("Expires"),
        ['class' => ''],
        FALSE,
        ['time' => FALSE]
      );
    }

    // add category dropdown from option group
    $form->add('select',
      "consent_ui_category",
      E::ts("Category"),
      array('0' => E::ts("- please select -")) + CRM_Gdprx_Consent::getCategoryList(),
      TRUE,
      array('class' => 'user-category')
    );

    // add source category
    $form->add('select',
      "consent_ui_source",
      E::ts("Source"),
      array('0' => E::ts("- please select -")) + CRM_Gdprx_Consent::getSourceList(),
      TRUE,
      array('class' => 'user-source')
    );

    // add type dropdown from option group
    if ($config->getSetting('use_consent_type')) {
      $form->add('select',
        "consent_ui_type",
        E::ts("Type"),
        CRM_Gdprx_Consent::getTypeList(),
        FALSE,
        array('class' => 'user-type')
      );
    }

    // terms
    if ($config->getSetting('use_consent_terms')) {
      $form->add('select',
        "consent_ui_terms",
        E::ts("Terms"),
        array('0' => E::ts("- none -")) + CRM_Gdprx_Terms::getList(),
        FALSE,
        array('class' => 'user-type')
      );
    }

    // remark (note)
    if ($config->getSetting('use_consent_note')) {
      $form->add(
        'text',
        "consent_ui_note",
        E::ts("Note")
      );
    }

    // set default values
    $form->setDefaults([
      'consent_ui_category'   => '0', // CRM_Gdprx_Consent::getCategoryDefault()
      'consent_ui_source'     => CRM_Gdprx_Consent::getSourceDefault(),
    ]);

    // add template path for these fields
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/Gdprx/ConsentForm.tpl"
    ));
  }


  /**
   * handles the validate form hook action
   */
  public static function validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if (!empty($form->_contactId)) {
      // we are in edit mode, nothing to do here!
      return;
    }

    // check if this is enabled
    $config = CRM_Gdprx_Configuration::getSingleton();
    if (!$config->getSetting('enforce_record_for_new_contacts')) {
      return;
    }

    $category = CRM_Utils_Array::value( 'consent_ui_category', $fields );
    if (!$category || $category == '0') {
      $errors['consent_ui_category'] = E::ts('Category is mandatory');
    }

    $source = CRM_Utils_Array::value( 'consent_ui_source', $fields );
    if (!$source || $source == '0') {
      $errors['consent_ui_source'] = E::ts('Source is mandatory');
    }

    $contact_origin = CRM_Utils_Array::value( 'consent_ui_note', $fields );
    if (strlen($contact_origin) > 1024) {
      $errors['consent_ui_note'] = E::ts('Note cannot be more the 1024 characters');
    }
  }

  /**
   * handles the post process hook action
   */
  public static function postProcess($formName, &$form) {
    if (empty($form->_contactId)) {
      // contact doesn't exist yet
      return;
    }

    // check if this is enabled
    $config = CRM_Gdprx_Configuration::getSingleton();
    if (!$config->getSetting('enforce_record_for_new_contacts')) {
      return;
    }

    $values = $form->exportValues();
    if (!empty($values['consent_ui_category'])) {
      CRM_Gdprx_Consent::createConsentRecord($form->_contactId,
                                             $values['consent_ui_category'],
                                             $values['consent_ui_source'],
                                             $values['consent_ui_date'],
                                             CRM_Utils_Array::value('consent_ui_note', $values),
                                             CRM_Utils_Array::value('consent_ui_type', $values),
                                             CRM_Utils_Array::value('consent_ui_terms', $values),
                                             CRM_Utils_Array::value('consent_ui_expiry_date', $values));
    }
  }
}
