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

{$form.contact_id.html}{$form.record_id.html}

<div class="crm-section">
  <div class="label">{$form.consent_ui_date.label}</div>
  <div class="content">
    {$form.consent_ui_date.html}
    {if $needs_calendar_include}
      {include file="CRM/common/jcalendar.tpl" elementName=consent_ui_date}
    {/if}
  </div>
  <div class="clear"></div>
</div>

{if $config.use_consent_expiry_date}
<div class="crm-section">
  <div class="label">{$form.consent_ui_expiry_date.label}</div>
  <div class="content">
    {$form.consent_ui_expiry_date.html}
    {if $needs_calendar_include}
        {include file="CRM/common/jcalendar.tpl" elementName=consent_ui_expiry_date}
    {/if}
  </div>
  <div class="clear"></div>
</div>
{/if}

<div class="crm-section">
  <div class="label">{$form.consent_ui_category.label}</div>
  <div class="content">{$form.consent_ui_category.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.consent_ui_source.label}</div>
  <div class="content">{$form.consent_ui_source.html}</div>
  <div class="clear"></div>
</div>

{if $config.use_consent_type}
<div class="crm-section">
  <div class="label">{$form.consent_ui_type.label}</div>
  <div class="content">{$form.consent_ui_type.html}</div>
  <div class="clear"></div>
</div>
{/if}

{if $config.use_consent_terms}
<div class="crm-section">
  <div class="label">{$form.consent_ui_terms.label}</div>
  <div class="content">{$form.consent_ui_terms.html}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.consent_ui_terms_full.label}</div>
  <div class="content">{$form.consent_ui_terms_full.html}</div>
  <div class="clear"></div>
</div>
{/if}

{if $config.use_consent_note}
<div class="crm-section">
  <div class="label">{$form.consent_ui_note.label}</div>
  <div class="content">{$form.consent_ui_note.html}</div>
  <div class="clear"></div>
</div>
{/if}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>


<script type="text/javascript">
{literal}
cj("#consent_ui_terms_full").change(function() {
  cj("[name=consent_ui_terms]").val('0').change();
});

cj("[name=consent_ui_terms]").change(function() {
  var val = cj("[name=consent_ui_terms]").val();
  if (val != 0) {
    cj("#consent_ui_terms_full").val('');
  }
});
{/literal}
</script>