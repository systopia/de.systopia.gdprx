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

require_once 'gdprx.civix.php';

use CRM_Gdprx_ExtensionUtil as E;
use \Symfony\Component\DependencyInjection\ContainerBuilder;

define('GDPRX_DEBUG_LOGGING', FALSE);

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function gdprx_civicrm_container(ContainerBuilder $container) {
  if (class_exists('Civi\Gdprx\ContainerSpecs')) {
    $container->addCompilerPass(new Civi\Gdprx\ContainerSpecs());
  }
}

/**
 * Add a task to create multiple records
 */
function gdprx_civicrm_searchTasks($objectType, &$tasks) {
  if ($objectType == 'contact') {
    $tasks['create_gdprx'] = array(
        'title'  => E::ts("Add Consent Records"),
        'class'  => 'CRM_Gdprx_Form_ConsentTask',
        'result' => false);
  }
}

/**
 * Implements hook_civicrm_pre().
 *
 * Will make sure that edits to contact/bpks will be
 *  handled correctly
 */
function gdprx_civicrm_pre($op, $objectName, $id, &$params) {
  // see if we should apply the default privacy settings
  if ($objectName == 'Individual' || $objectName == 'Organization' || $objectName == 'Household') {
    if (empty($id)) {
      // only apply if it's a new contact (no ID)
      $config = CRM_Gdprx_Configuration::getSingleton();
      if (!empty($params['privacy']) && is_array($params['privacy'])) {
        $config->addDefaultPrivacySettings($params['privacy']);
      } else {
        $config->addDefaultPrivacySettings($params['privacy']);
      }
    }
  }
}

/**
* Implements hook_civicrm_tabset() (updated from hook_civicrm_tabs)
*
* Will inject a custom gdprx tab
*/
function gdprx_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName == 'civicrm/contact/view') {
    // remove the default table
    $group_id = CRM_Gdprx_CustomData::getGroupID('consent');
    $tab_key  = "custom_{$group_id}";
    for ($i = 0; $i < count($tabs); $i++) {
      if ($tabs[$i]['id'] == $tab_key) {
        unset($tabs[$i]);
        break;
      }
    }
    $contactID = $context['contact_id'];
    // add our own tab
    $tabs[] = [
        'id'     => 'gdprx',
        'url'    => CRM_Utils_System::url('civicrm/gdprx/tab', "reset=1&snippet=1&force=1&cid={$contactID}"),
        'title'  => E::ts('Consent'),
        'count'  => CRM_Gdprx_Page_SummaryTab::getRecordCount($contactID),
        'weight' => 400
    ];
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function gdprx_civicrm_config(&$config) {
  _gdprx_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function gdprx_civicrm_install() {
  _gdprx_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function gdprx_civicrm_enable() {
  _gdprx_civix_civicrm_enable();

  // add custom fields
  require_once 'CRM/Gdprx/CustomData.php';
  $customData = new CRM_Gdprx_CustomData('de.systopia.gdprx');
  $customData->syncOptionGroup(__DIR__ . '/resources/consent_category_option_group.json');
  $customData->syncOptionGroup(__DIR__ . '/resources/consent_type_option_group.json');
  $customData->syncOptionGroup(__DIR__ . '/resources/consent_source_option_group.json');
  $customData->syncCustomGroup(__DIR__ . '/resources/consent_custom_group.json');
}

/**
 * Implements hook_civicrm_buildForm()
 */
function gdprx_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    CRM_Gdprx_ConsentUI::buildForm($formName, $form);
  }

  if (  $formName == 'CRM_Contact_Form_Inline_CommunicationPreferences'
     || $formName == 'CRM_Contact_Form_Contact') {
    $config = CRM_Gdprx_Configuration::getSingleton();
    if ($config->getSetting('disable_privacy_edit')) {
      CRM_Core_Resources::singleton()->addVars('gdprx', [
          'privacy_help' => E::ts("These settings cannot be edited directly any more. Please use then consent tab.")]);
      CRM_Core_Resources::singleton()->addScriptFile('de.systopia.gdprx', 'js/DisablePrivacyEditing.js');
    }
  }
}

/**
 * Implements hook_civicrm_validateForm()
 */
function gdprx_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    CRM_Gdprx_ConsentUI::validateForm($formName, $fields, $files, $form, $errors);
  }
}

/**
 * Implements hook_civicrm_postProcess()
 */
function gdprx_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    CRM_Gdprx_ConsentUI::postProcess($formName, $form);
  }
}
