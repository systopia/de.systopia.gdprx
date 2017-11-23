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
  protected $note_source;
  protected $date_source;
  protected $contact_source;

  /**
   * Create an API Wrapper to derive GDPR consent records
   * from a successful API call, e.g. a group sign-up
   *
   * @param $entity          the API entity to process
   * @param $action          the API action to process
   * @param $category        data specifier (see below) forlabel or value of the consent_category option group.
   * @param $source          data specifier (see below) forlabel or value of the consent_source option group.
   * @param $note_source     data specifier (see below) for the note field, or NULL (default)
   * @param $date_source     data specifier (see below) for the date entry
   * @param $contact_source  data specifier (see below) for the contact
   *
   * data specifiers can be either:
   *  'request::<attribute>' in this case the attribute is taken from the API request.params
   *  'reply::<attribute>'   in this case the attribute is taken from the API reply
   *  otherwise              the string is taken literally
   */
  public function __construct($entity, $action, $category, $source, $note_source = NULL, $date_source = 'now', $contact_source = 'reply::contact_id') {
    $this->entity         = $entity;
    $this->action         = $action;
    $this->category       = $category;
    $this->source         = $source;
    $this->note_source    = $note_source;
    $this->date_source    = $date_source;
    $this->contact_source = $contact_source;
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

      // check if the call was successfull
      if (empty($result['is_error'])) {

        // check if the contact is there
        $contact_id = $this->getDataValue($this->contact_source, $apiRequest, $result);
        if (!empty($contact_id)) {

          // all good: create GDPR consent record
          CRM_Gdprx_Consent::createConsentRecord(
            $contact_id,
            $this->getDataValue($this->category, $apiRequest, $result),
            $this->getDataValue($this->source, $apiRequest, $result),
            date('YmdHis', strtotime($this->getDataValue($this->date_source, $apiRequest, $result))),
            $this->getDataValue($this->note_source, $apiRequest, $result)
          );
        }
      }
    }

    return $result;
  }

  /**
   * Extract the speficied value, see constructor definition
   */
  protected function getDataValue($data_spec, $request, $reply) {
    if ($data_spec === NULL || $data_spec === '') {
      return NULL;

    } elseif (substr($data_spec, 0, 9) == 'request::') {
      // parameter should be taken from request parameters
      $attribute = substr($data_spec, 9);
      if (isset($request['params'][$attribute])) {
        return $request['params'][$attribute];
      } else {
        return '';
      }

    } elseif (substr($data_spec, 0, 7) == 'reply::') {
      // parameter should be taken from reply data
      $attribute = substr($data_spec, 7);
      return CRM_Utils_Array::value($attribute, $reply, '');

    } else {
      // if nothing else fits, it's just a string.
      return $data_spec;
    }
  }
}