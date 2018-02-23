{*-------------------------------------------------------+
| HBS UI Modififications                                 |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres  (endres@systopia.de)                |
| Author: P. Batroff (batroff@systopia.de)               |
| Source: http://www.systopia.de/                        |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*}

<table class="consent-ui">
    <tr>
        <td>
            <div id="user-clearance-date">
                {$form.consent_ui_date.label}
                {$form.consent_ui_date.html}
                {if $needs_calendar_include}
                    {include file="CRM/common/jcalendar.tpl" elementName=consent_ui_date}
                {/if}
            </div>
        </td>
        <td>
            <div id="user-clearance-category">
                {$form.consent_ui_category.label}
                {$form.consent_ui_category.html}
            </div>
        </td>
        <td>
            <div id="user-clearance-source">
                {$form.consent_ui_source.label}
                {$form.consent_ui_source.html}
            </div>
        </td>
        <td>
            <div id="user-clearance-note">
                {$form.consent_ui_note.label}
                {$form.consent_ui_note.html}
            </div>
        </td>
    </tr>
</table>

<script type="text/javascript">
cj('table.consent-ui').prependTo('#contactDetails');
</script>
