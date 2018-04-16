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

<h3>{ts}General Options{/ts}</h3>
<div class="crm-section">
  <div class="label">{$form.enforce_record_for_new_contacts.label}&nbsp;<a onclick='CRM.help("{ts domain="de.systopia.gdprx"}Enforce Record{/ts}", {literal}{"id":"id-gdprx-enforce-record","file":"CRM\/Gdprx\/Form\/Settings"}{/literal}); return false;' href="#" title="{ts domain="de.systopia.gdprx"}Help{/ts}" class="helpicon">&nbsp;</a></div>
  <div class="content">{$form.enforce_record_for_new_contacts.html}</div>
  <div class="clear"></div>
</div>

<h3>{ts}GDPR Fields{/ts}</h3>
<div class="crm-section">
  <div class="label">{$form.field_expiry_enabled.label}</div>
  <div class="content">{$form.field_expiry_enabled.html}</div>
  <div class="clear"></div>
</div>

<h3>{ts}Default Privacy Settings{/ts}</h3>
<div class="crm-section">
  <div class="label">{$form.default_privacy_settings_enabled.label}&nbsp;<a onclick='CRM.help("{ts domain="de.systopia.gdprx"}Default Privacy Settings{/ts}", {literal}{"id":"id-gdprx-default-privacy","file":"CRM\/Gdprx\/Form\/Settings"}{/literal}); return false;' href="#" title="{ts domain="de.systopia.gdprx"}Help{/ts}" class="helpicon">&nbsp;</a></div>
  <div class="content">{$form.default_privacy_settings_enabled.html}</div>
  <div class="clear"></div>
</div>
<div id="gdprx_default_privacy" style="padding-left: 50px;">
  <div class="crm-section">
    <div class="label">{$form.default_privacy_do_not_email.label}</div>
    <div class="content">{$form.default_privacy_do_not_email.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_privacy_do_not_email.label}</div>
    <div class="content">{$form.default_privacy_do_not_email.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_privacy_do_not_phone.label}</div>
    <div class="content">{$form.default_privacy_do_not_phone.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_privacy_do_not_mail.label}</div>
    <div class="content">{$form.default_privacy_do_not_mail.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_privacy_do_not_sms.label}</div>
    <div class="content">{$form.default_privacy_do_not_sms.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_privacy_do_not_trade.label}</div>
    <div class="content">{$form.default_privacy_do_not_trade.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_privacy_is_opt_out.label}</div>
    <div class="content">{$form.default_privacy_is_opt_out.html}</div>
    <div class="clear"></div>
  </div>
</div>

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<script type="text/javascript">
{literal}
function toggleGdprxPrivacy() {
  var enabled = cj("#default_privacy_settings_enabled").prop('checked');
  if (enabled) {
    cj("#gdprx_default_privacy").show(200);
  } else {
    cj("#gdprx_default_privacy").hide(200);
  }
}

cj(document).ready(toggleGdprxPrivacy);
cj("#default_privacy_settings_enabled").change(toggleGdprxPrivacy);
{/literal}
</script>