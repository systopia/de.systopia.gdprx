# SYSTOPIA's GDPRX Extension

This extension aims to be a toolkit to help you comply with the GDPR.

## What does it do?

The core of this toolkit is a new custom group where you can store consent 
records with your contacts.  These records can be explicit or implicit consent, 
opt-outs, and similar things.

It integrates neatly with the UI, and gives you some search and data entry 
helpers. 

Each record has the following fields:
* date and time
* category (e.g. "newsletter")
* source (e.g. "main website")
* type (e.g. "opt-in", "opt-out", "soft opt-in")

In addition, there are also some optional fields for each consent record:
* expiry date (consent expires after the given date/time)
* terms and conditions. (the full text of the TOC that the user agreed to*)
* note (in case you want to add a remark) 

(*) it has a clever mechanism so that it doesn't store the 
same TOC text over and over.

## How do I use this?

Since the actions or consequences derived from the collected data differ 
greatly between organisations, this toolkit doesn't do anything automatically.
However, it *can* be used as a basis for further automation, e.g. by using the 
[SQL Taks extension](https://civicrm.org/extensions/sql-tasks-extension-configurable-recurring-tasks) 
to tag contacts for deletion if the basis for a contact's retention is 
not there anymore.

## That's it?

Yes and no. There are some additional features that might interest you as 
a developer:
1. There is a feature to make the communication preferences read-only, in case you want to derive them from the contact's consent record.
2. There is a fully functioning API
3. It defines a custom hook to be triggered if the consent records are modified for a contact. This way you can add code update the contact's permissions/subscripts/whatever right away.

## What's next?

We'll keep on extending the built-in functionality of this extension, while aiming to keep it compatible 
with earlier versions. However, since the implementation of the GDPR is very 
specific to the individual organisation, this will never be "plug&play" solution for any user.

Feel free to [raise ticket](https://github.com/systopia/de.systopia.gdprx/issues) if you have a question.

## Documentation
https://docs.civicrm.org/gdprx/en/latest/