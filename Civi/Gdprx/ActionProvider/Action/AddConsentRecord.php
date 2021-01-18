<?php
/*-------------------------------------------------------+
| SYSTOPIA GDPR Compliance Extension                     |
| Copyright (C) 2021 SYSTOPIA                            |
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

namespace Civi\Gdprx\ActionProvider\Action;

use CRM_Gdprx_ExtensionUtil as E;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

class AddConsentRecord extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag specs
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
        new Specification(
            'category',
            'String',
            E::ts('Default Category'),
            true,
            null,
            null,
            \CRM_Gdprx_Consent::getCategoryList(),
            false
        ),
        new Specification(
            'source',
            'String',
            E::ts('Default Source'),
            true,
            null,
            null,
            \CRM_Gdprx_Consent::getSourceList(),
            false
        ),
        new Specification(
            'type',
            'String',
            E::ts('Default Type'),
            true,
            null,
            null,
            \CRM_Gdprx_Consent::getTypeList(),
            false
        ),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag specs
   */
  public function getParameterSpecification() {
    // add contact specs
    return new SpecificationBag([
        new Specification('contact_id', 'Integer', E::ts('Contact ID'), true, null, null, null, false),
        new Specification('category', 'String', E::ts('Category'), false, null, null, null, false),
        new Specification('source', 'String', E::ts('Source'), false, null, null, null, false),
        new Specification('type', 'String', E::ts('Type'), false, null, null, null, false),
        new Specification('date', 'Timestamp', E::ts('Record Date'), false, null, null, null, false),
        new Specification('note', 'Text', E::ts('Note'), false, null, null, null, false),
        new Specification('expiry_date', 'Timestamp', E::ts('Expiry Date'), false, null, null, null, false),
        new Specification('gtac', 'Text', E::ts('Terms and Conditions'), false, null, null, null, false),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag specs
   */
  public function getOutputSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // gather parameters
    $contact_id = $parameters->getParameter('contact_id');

    $category = $parameters->getParameter('category');
    if (empty($category)) {
      $category = $this->configuration->getParameter('category');
    }

    $source = $parameters->getParameter('source');
    if (empty($source)) {
      $source = $this->configuration->getParameter('source');
    }

    $type = $parameters->getParameter('type');
    if (empty($type)) {
      $type = $this->configuration->getParameter('type');
    }
    if (empty($type)) {
      $type = null;
    }

    $date = $parameters->getParameter('date');
    if (empty($date)) {
      $date = 'now';
    }

    $note = $parameters->getParameter('note');
    if (empty($note)) {
      $note = '';
    }

    $terms_id = $parameters->getParameter('gtac');
    if (empty($terms_id)) {
      $terms_id = null;
    } else {
      $terms = \CRM_Gdprx_Terms::getOrCreate($terms_id);
      $terms_id = $terms->getID();
    }

    $expiry_date = $parameters->getParameter('expiry_date');
    if (empty($expiry_date)) {
      $expiry_date = null;
    }

    // execute
    \CRM_Gdprx_Consent::createConsentRecord($contact_id, $category, $source, $date, $note, $terms_id, $expiry_date);
  }
}
