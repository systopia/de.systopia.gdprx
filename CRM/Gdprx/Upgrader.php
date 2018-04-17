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
 * Collection of upgrade steps.
 */
class CRM_Gdprx_Upgrader extends CRM_Gdprx_Upgrader_Base {

  /**
   * Installer
   */
  public function install() {
    // create new terms table
    $this->executeSqlFile('sql/civicrm_gdpr_terms.sql');

    // run the custom group sync
    require_once 'CRM/Gdprx/CustomData.php';
    $customData = new CRM_Gdprx_CustomData('de.systopia.gdprx');
    $customData->syncOptionGroup(__DIR__ . '/../../resources/consent_category_option_group.json');
    $customData->syncOptionGroup(__DIR__ . '/../../resources/consent_type_option_group.json');
    $customData->syncOptionGroup(__DIR__ . '/../../resources/consent_source_option_group.json');
    $customData->syncCustomGroup(__DIR__ . '/../../resources/consent_custom_group.json');

    return TRUE;
  }

  /**
   * Update to 0.2
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_0020() {
    $this->ctx->log->info('Applying update for 0.2');

    // create new terms table
    $this->executeSqlFile('sql/civicrm_gdpr_terms.sql');

    // re-run the custom group sync
    require_once 'CRM/Gdprx/CustomData.php';
    $customData = new CRM_Gdprx_CustomData('de.systopia.gdprx');
    $customData->syncOptionGroup(__DIR__ . '/../../resources/consent_category_option_group.json');
    $customData->syncOptionGroup(__DIR__ . '/../../resources/consent_type_option_group.json');
    $customData->syncOptionGroup(__DIR__ . '/../../resources/consent_source_option_group.json');
    $customData->syncCustomGroup(__DIR__ . '/../../resources/consent_custom_group.json');

    return TRUE;
  }
}
