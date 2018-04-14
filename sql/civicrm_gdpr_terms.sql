-- +--------------------------------------------------------+
-- | SYSTOPIA GDPR Compliance Extension                     |
-- | Copyright (C) 2018 SYSTOPIA                            |
-- | Author: B. Endres (endres@systopia.de)                 |
-- | http://www.systopia.de/                                |
-- +--------------------------------------------------------+
-- | This program is released as free software under the    |
-- | Affero GPL license. You can redistribute it and/or     |
-- | modify it under the terms of this license which you    |
-- | can read by viewing the included agpl.txt or online    |
-- | at www.gnu.org/licenses/agpl.html. Removal of this     |
-- | copyright header is strictly prohibited without        |
-- | written permission from the original author(s).        |
-- +--------------------------------------------------------+

-- this table will record the different versions of terms and conditions
--  used to record a GDPR consent entry
CREATE TABLE IF NOT EXISTS `civicrm_gdpr_terms` (
     `id`          int unsigned  NOT NULL AUTO_INCREMENT,
     `create_date` datetime      NOT NULL COMMENT 'creation date of the entry',
     `name`        varchar(32)            COMMENT 'Name of this text, defaults to the first 32 charactes.',
     `text_hash`   varchar(40)            COMMENT 'SHA1 hash of the full text',
     `text_full`   text                   COMMENT 'full text of the terms',
     PRIMARY KEY (`id`),
     INDEX `text_hash` (`text_hash`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
