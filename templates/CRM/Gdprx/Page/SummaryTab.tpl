{*-------------------------------------------------------+
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
+-------------------------------------------------------*}

<div class="action-link">
  <a accesskey="N" href="{crmURL p='civicrm/gdprx/consent/edit' q="id=new&cid=$contact_id&reset=1&action=update"}" class="button crm-popup"><span><div class="icon ui-icon-circle-plus"></div>{ts}Add Record{/ts}</span></a>
  <br><br>
</div>

<table class='gdprx gdprx-tab'>
  <thead>
    <tr>
      <th>{ts domain="de.systopia.gdprx"}Recorded{/ts}</th>
      {if $gdprx.use_consent_expiry_date}
      <th>{ts domain="de.systopia.gdprx"}Expires{/ts}</th>
      {/if}
      <th>{ts domain="de.systopia.gdprx"}Category{/ts}</th>
      <th>{ts domain="de.systopia.gdprx"}Source{/ts}</th>
      {if $gdprx.use_consent_type}
      <th>{ts domain="de.systopia.gdprx"}Type{/ts}</th>
      {/if}
      {if $gdprx.use_consent_terms}
      <th>{ts domain="de.systopia.gdprx"}Terms{/ts}</th>
      {/if}
      {if $gdprx.use_consent_note}
      <th>{ts domain="de.systopia.gdprx"}Note{/ts}</th>
      {/if}
      <th></th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$records item=record}
    <tr>
      <td title="{$record.record_date_full}">
        {$record.record_date}
      </td>
      {if $gdprx.use_consent_expiry_date}
      <td title="{$record.record_expiry_full}">
        {if $record.record_expiry}
          {$record.record_expiry}
        {/if}
      </td>
      {/if}
      <td>
        {$record.record_category}
      </td>
      <td>
        {$record.record_source}
      </td>
      {if $gdprx.use_consent_type}
      <td>
        {$record.record_type}
      </td>
      {/if}
      {if $gdprx.use_consent_terms}
      <td title="{$record.record_terms_full}">
        {$record.record_terms_name}
      </td>
      {/if}
      {if $gdprx.use_consent_note}
      <td title="{$record.record_note}">
        {$record.record_note_short}
      </td>
      {/if}
      <td>
        {assign value=$record.record_id var=record_id}
        <span><a href="{crmURL p='civicrm/gdprx/consent/edit' q="id=$record_id&cid=$contact_id&reset=1"}" class="action-item crm-hover-button crm-popup" title="{ts}Edit{/ts}">{ts}Edit{/ts}</a></span>
      </td>
    </tr>
    {/foreach}
  </tbody>
</table>
