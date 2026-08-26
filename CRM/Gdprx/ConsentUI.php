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

declare(strict_types = 1);

use CRM_Gdprx_ExtensionUtil as E;

class CRM_Gdprx_ConsentUI {

  /**
   * handles the build form hook action
   */
  public static function buildForm($formName, &$form) {
    if (isset($form->_contactId) && (int) $form->_contactId > 0) {
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
      E::ts('Date'),
      ['class' => ''],
      TRUE,
      ['time' => FALSE]
    );
    $form->setDefaults(['consent_ui_date' => date('Y-m-d')]);

    if ($config->getSetting('use_consent_expiry_date')) {
      $form->add(
        'datepicker',
        'consent_ui_expiry_date',
        E::ts('Expires'),
        ['class' => ''],
        FALSE,
        ['time' => FALSE]
      );
    }

    // add category dropdown from option group
    $form->add('select',
      'consent_ui_category',
      E::ts('Category'),
      ['0' => E::ts('- please select -')] + CRM_Gdprx_Consent::getCategoryList(),
      TRUE,
      ['class' => 'user-category']
    );

    // add source category
    $form->add('select',
      'consent_ui_source',
      E::ts('Source'),
      ['0' => E::ts('- please select -')] + CRM_Gdprx_Consent::getSourceList(),
      TRUE,
      ['class' => 'user-source']
    );

    // add type dropdown from option group
    if ($config->getSetting('use_consent_type')) {
      $form->add('select',
        'consent_ui_type',
        E::ts('Type'),
        CRM_Gdprx_Consent::getTypeList(),
        FALSE,
        ['class' => 'user-type']
      );
    }

    // terms
    if ($config->getSetting('use_consent_terms')) {
      $form->add('select',
        'consent_ui_terms',
        E::ts('Terms'),
        ['0' => E::ts('- none -')] + CRM_Gdprx_Terms::getList(),
        FALSE,
        ['class' => 'user-type']
      );
    }

    // optional note field
    if ($config->getSetting('use_consent_note')) {
      $form->add(
        'text',
        'consent_ui_note',
        E::ts('Note')
      );
    }

    // set default values - category is deliberately not pre-filled (unlike source)
    // so the user is forced to pick one
    $form->setDefaults([
      'consent_ui_category'   => '0',
      'consent_ui_source'     => CRM_Gdprx_Consent::getSourceDefault(),
    ]);

    // add template path for these fields
    CRM_Core_Region::instance('page-body')->add([
      'template' => 'CRM/Gdprx/ConsentForm.tpl',
    ]);
  }

  /**
   * handles the validate form hook action
   */
  public static function validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if (isset($form->_contactId) && (int) $form->_contactId > 0) {
      // we are in edit mode, nothing to do here!
      return;
    }

    // check if this is enabled
    $config = CRM_Gdprx_Configuration::getSingleton();
    if (!$config->getSetting('enforce_record_for_new_contacts')) {
      return;
    }

    $category = $fields['consent_ui_category'] ?? NULL;
    if (in_array($category, [NULL, '', '0'], TRUE)) {
      $errors['consent_ui_category'] = E::ts('Category is mandatory');
    }

    $source = $fields['consent_ui_source'] ?? NULL;
    if (in_array($source, [NULL, '', '0'], TRUE)) {
      $errors['consent_ui_source'] = E::ts('Source is mandatory');
    }

    $contact_origin = $fields['consent_ui_note'] ?? NULL;
    if (strlen($contact_origin) > 1024) {
      $errors['consent_ui_note'] = E::ts('Note cannot be more the 1024 characters');
    }
  }

  /**
   * handles the post process hook action
   */
  public static function postProcess($formName, &$form) {
    if (!isset($form->_contactId) || (int) $form->_contactId === 0) {
      // contact doesn't exist yet
      return;
    }

    // check if this is enabled
    $config = CRM_Gdprx_Configuration::getSingleton();
    if (!$config->getSetting('enforce_record_for_new_contacts')) {
      return;
    }

    $values = $form->exportValues();
    if ((string) ($values['consent_ui_category'] ?? '0') !== '0') {
      CRM_Gdprx_Consent::createConsentRecord($form->_contactId,
                                             $values['consent_ui_category'],
                                             $values['consent_ui_source'],
                                             $values['consent_ui_date'],
                                             $values['consent_ui_note'] ?? NULL,
                                             $values['consent_ui_type'] ?? NULL,
                                             $values['consent_ui_terms'] ?? NULL,
                                             $values['consent_ui_expiry_date'] ?? NULL);
    }
  }

}
