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

cj(document).ready(function() {
    // freeze privacy settings
    let fields2freeze = ['do_not_email', 'do_not_phone', 'do_not_mail', 'do_not_sms', 'do_not_trade'];
    for (let idx in fields2freeze) {
        let field_key = fields2freeze[idx];
        let field = cj('#privacy_' + field_key);

        // disable field
        field.attr("disabled", true);

        if (field.attr("checked")) {
            cj('input[name="privacy[' + field_key + ']"]').attr('value', 1);
        } else {
            cj('input[name="privacy[' + field_key + ']"]').attr('value', 0);
        }
    }

    // freeze 'is_opt_out'
    let opt_out_field = cj("#is_opt_out");
    opt_out_field.attr("disabled", true);
    if (opt_out_field.attr('checked')) {
        opt_out_field.after('<input name="is_opt_out" type="hidden" value="1" />');
    }

    // add notes
    cj("#privacy_do_not_email").parent().attr("title", CRM.vars.gdprx.privacy_help);
    cj("#is_opt_out").parent().attr("title", CRM.vars.gdprx.privacy_help);
});
