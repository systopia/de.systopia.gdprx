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

    // add date, prefilled with current date
    $form->add(
      'datepicker',
      'consent_ui_date',
      E::ts("Date"),
      array('class' => ''),
      TRUE,
      array('time' => FALSE)
    );

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

    // remark (note)
    $form->add(
      'text',
      "consent_ui_note",
      E::ts("Note")
    );

    // set default values
    $form->setDefaults(array(
      'consent_ui_category'   => '0',
      'consent_ui_source'     => '0',
      'consent_ui_date'       => date("Y-m-d"),
    ));

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

    $values = $form->exportValues();
    if (!empty($values['consent_ui_category'])) {
      CRM_Gdprx_Consent::createConsentRecord($form->_contactId,
                                             $values['consent_ui_category'],
                                             $values['consent_ui_source'],
                                             $values['consent_ui_date'],
                                             $values['consent_ui_note']);
    }
  }
}