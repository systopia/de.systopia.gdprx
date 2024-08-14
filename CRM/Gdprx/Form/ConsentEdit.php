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
    $this->multi      = CRM_Utils_Request::retrieve('multi', 'Integer');
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
    $this->add(
        'datepicker',
        'consent_ui_date',
        E::ts('Date'),
        ['formatType' => 'activityDateTime'],
        true
    );
    $this->setDefaults(['consent_ui_date' => date('Y-m-d H:i:s')]);

    if ($config->getSetting('use_consent_expiry_date')) {
      $this->add(
          'datepicker',
          'consent_ui_expiry_date',
          E::ts('Expires'),
          ['formatType' => 'activityDateTime'],
          true
      );
    }

    // add category dropdown from option group
    if ($this->multi && !$this->record_id) {
      $category_list = CRM_Gdprx_Consent::getCategoryList();
      $this->add('select',
          "consent_ui_category",
          E::ts("Categories"),
          $category_list,
          TRUE,
          array('class' => 'user-category crm-select2 huge', 'multiple' => 'multiple')
      );
      $this->setDefaults(array('consent_ui_category' => array_keys($category_list)));
    } else {
      $this->add('select',
          "consent_ui_category",
          E::ts("Category"),
          array('0' => E::ts("- please select -")) + CRM_Gdprx_Consent::getCategoryList(),
          TRUE,
          array('class' => 'user-category')
      );
    }

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
      $date_values['consent_ui_date'] = date('Y-m-d H:i:s', strtotime($data['consent_date']));
      $date_values['consent_ui_expiry_date'] = date('Y-m-d H:i:s', strtotime($data['consent_expiry_date']));

      $this->setDefaults(array(
        'consent_ui_category'    => $data['consent_category'],
        'consent_ui_source'      => $data['consent_source'],
        'consent_ui_type'        => $data['consent_type'],
        'consent_ui_note'        => $data['consent_note'],
        'consent_ui_terms'       => $data['consent_terms'],
      ) + $date_values);
    } else {
      // set default values? dates have been set above...
      $this->setDefaults([
//        'consent_ui_category'    => CRM_Gdprx_Consent::getCategoryDefault(),
        'consent_ui_source'      => CRM_Gdprx_Consent::getSourceDefault(),
      ]);
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
      $categories = is_array($values['consent_ui_category']) ? $values['consent_ui_category'] : array($values['consent_ui_category']);
      // create new record(s)
      foreach ($categories as $category) {
        CRM_Gdprx_Consent::createConsentRecord(
            $values['contact_id'],
            $category,
            $values['consent_ui_source'],
            date('YmdHis', strtotime(CRM_Utils_Date::processDate($values['consent_ui_date'], $values['consent_ui_date_time']))),
            CRM_Utils_Array::value('consent_ui_note', $values, NULL),
            CRM_Utils_Array::value('consent_ui_type', $values, NULL),
            $terms_id,
            $expiry_date ? date('YmdHis', strtotime(CRM_Utils_Date::processDate($values['consent_ui_expiry_date'], $values['consent_ui_expiry_date_time']))) : NULL);
      }
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
