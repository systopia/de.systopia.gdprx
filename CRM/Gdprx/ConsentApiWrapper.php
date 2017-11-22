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

class CRM_Gdprx_ConsentApiWrapper implements API_Wrapper {

  protected $entity;
  protected $action;
  protected $category;
  protected $source;
  protected $note_field;
  protected $date_field;
  protected $contact_field;

  public function __construct($entity, $action, $category, $source, $note_field = 'consent_note', $date_field = 'now', $contact_field = 'contact_id') {
    $this->entity        = $entity;
    $this->action        = $action;
    $this->category      = $category;
    $this->source        = $source;
    $this->note_field    = $note_field;
    $this->date_field    = $date_field;
    $this->contact_field = $contact_field;
  }

  /**
   * Interface for interpreting api input.
   *
   * @param array $apiRequest
   *
   * @return array
   *   modified $apiRequest
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Interface for interpreting api output.
   *
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    if ($apiRequest['entity'] == $this->entity && $apiRequest['action'] == $this->action) {
      if (empty($result['is_error']) && !empty($result[$this->contact_field])) {
        CRM_Gdprx_Consent::createConsentRecord(
          $result[$this->contact_field],
          $this->category,
          $this->source,
          CRM_Utils_Array::value($this->date_field, $result, 'now'),
          CRM_Utils_Array::value($this->note_field, $result, '')
        );
      }
    }

    return $result;
  }
}