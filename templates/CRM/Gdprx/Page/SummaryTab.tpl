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
      <td>
        {$record.record_terms_name}
      </td>
      {/if}
      {if $gdprx.use_consent_note}
      <td title="{$record.record_note}">
        {$record.record_note_short}
      </td>
      {/if}
    </tr>
    {/foreach}
  </tbody>
</table>
