/*---------------------------------------------------------------------
//                         Tollerus
//                Conlang Dictionary System
//      < https://github.com/petermarkley/tollerus >
// 
// Copyright 2023 by Peter Markley <peter@petermarkley.com>.
// Distributed under the terms of the Lesser GNU General Public License.
// 
// This file is part of Tollerus.
// 
// Tollerus is free software: you can redistribute it and/or modify it
// under the terms of the Lesser GNU General Public License as
// published by the Free Software Foundation, either version 2.1 of the
// License, or (at your option) any later version.
// 
// Tollerus is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// Lesser GNU General Public License for more details.
// 
// You should have received a copy of the Lesser GNU General Public
// License along with Tollerus.  If not, see
// < http://www.gnu.org/licenses/ >.
// 
//----------------------------------------------------------------------*/

SET GLOBAL sql_mode = '';

/* ==================
   Enumeration Tables
   ================== */

CREATE TABLE `enum_languages` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64),
	`human` VARCHAR (64)
);

CREATE TABLE `enum_scripts` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64),
	`human` VARCHAR (64)
);

CREATE TABLE `enum_classes` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `enum_classes` (`value`) VALUES
	('adjective'),
	('adverb'),
	('auxiliary verb'),
	('combining form'),
	('contraction'),
	('conjunction'),
	('determiner'),
	('noun'),
	('postposition'),
	('preposition'),
	('pronoun'),
	('proper noun'),
	('verb');

CREATE TABLE `enum_extras` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `enum_extras` (`value`) VALUES
	('reference'),
	('figure');


/* =================
   Inflection Tables
   ================= */

-- Used when `class_type` is 'noun' & 'pronoun'

CREATE TABLE `inflect_definiteness` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_definiteness` (`value`) VALUES
	('general'),
	('indefinite'),
	('definite');

CREATE TABLE `inflect_number` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_number` (`value`) VALUES
	('singular'),
	('plural');

CREATE TABLE `inflect_case` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_case` (`value`) VALUES
	('subject'),
	('object'),
	('predicate');

-- Used when `class_type` is 'verb' or 'auxiliary verb'

CREATE TABLE `inflect_verb_role` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_verb_role` (`value`) VALUES
	('infinitive'),
	('finite'),
	('participle');

CREATE TABLE `inflect_tense` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_tense` (`value`) VALUES
	('past'),
	('present'),
	('future');

CREATE TABLE `inflect_aspect` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_aspect` (`value`) VALUES
	('perfect'),
	('simple'),
	('progressive'),
	('prospective');

CREATE TABLE `inflect_person` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_person` (`value`) VALUES
	('first'),
	('second'),
	('third');

CREATE TABLE `inflect_voice` (
	`key`   INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`value` VARCHAR (64)
);
INSERT INTO `inflect_voice` (`value`) VALUES
	('active'),
	('passive');


/* =================
   Enumeration Cache
   ================= */

CREATE TABLE `enum` (
	`table` VARCHAR (64),
	`key`   INTEGER,
	`value` VARCHAR (64),
	`human` VARCHAR (64)
);
INSERT INTO `enum` SELECT 'enum_classes'         AS `table`, `key`, `value`, null AS `human` FROM `enum_classes`         UNION
	                SELECT 'enum_extras'          AS `table`, `key`, `value`, null AS `human` FROM `enum_extras`          UNION
	                SELECT 'inflect_definiteness' AS `table`, `key`, `value`, null AS `human` FROM `inflect_definiteness` UNION
	                SELECT 'inflect_number'       AS `table`, `key`, `value`, null AS `human` FROM `inflect_number`       UNION
	                SELECT 'inflect_case'         AS `table`, `key`, `value`, null AS `human` FROM `inflect_case`         UNION
	                SELECT 'inflect_verb_role'    AS `table`, `key`, `value`, null AS `human` FROM `inflect_verb_role`    UNION
	                SELECT 'inflect_tense'        AS `table`, `key`, `value`, null AS `human` FROM `inflect_tense`        UNION
	                SELECT 'inflect_aspect'       AS `table`, `key`, `value`, null AS `human` FROM `inflect_aspect`       UNION
	                SELECT 'inflect_person'       AS `table`, `key`, `value`, null AS `human` FROM `inflect_person`       UNION
	                SELECT 'inflect_voice'        AS `table`, `key`, `value`, null AS `human` FROM `inflect_voice`;

/* ========
   WARNING!
   ======== */
-- Please remember to update this table with any rows added to `enum_languages` or `enum_scripts` during data entry.
-- Example:
-- 	INSERT INTO `enum` SELECT 'enum_languages' AS `table`, `key`, `value` FROM `enum_languages` WHERE `key` = @lang;


/* ===========
   Data Tables
   =========== */

CREATE TABLE `intros` (
	`lang` INTEGER NOT NULL,
	`content` TEXT CHARSET 'utf8'
);
ALTER TABLE `intros` ADD FOREIGN KEY (`lang`) REFERENCES `enum_languages` (`key`);
CREATE TABLE `script_data` (
	`lang` INTEGER NOT NULL,
	`script` INTEGER NOT NULL,
	`title` VARCHAR (64),
	`content` TEXT CHARSET 'utf8'
);
ALTER TABLE `script_data` ADD FOREIGN KEY (`lang`) REFERENCES `enum_languages` (`key`);
ALTER TABLE `script_data` ADD FOREIGN KEY (`script`) REFERENCES `enum_scripts` (`key`);

CREATE TABLE `entries` (
	`lang`          INTEGER             NOT NULL,
	`entry_id`      INTEGER PRIMARY KEY NOT NULL,
	`primary_morph` INTEGER,
	`etym` TEXT CHARSET 'utf8'
);
ALTER TABLE `entries` ADD FOREIGN KEY (`lang`) REFERENCES `enum_languages` (`key`);

CREATE TABLE `classes` (
	`lang`       INTEGER             NOT NULL,
	`entry_id`   INTEGER             NOT NULL,
	`class_id`   INTEGER PRIMARY KEY NOT NULL,
	`class_type` INTEGER             NOT NULL,
	`priority`   INTEGER
--	`def` TEXT CHARSET 'utf8'
);
ALTER TABLE `classes` ADD FOREIGN KEY (`lang`)       REFERENCES `enum_languages` (`key`);
ALTER TABLE `classes` ADD FOREIGN KEY (`entry_id`)   REFERENCES `entries`        (`entry_id`);
ALTER TABLE `classes` ADD FOREIGN KEY (`class_type`) REFERENCES `enum_classes`   (`key`);

CREATE TABLE `morph` (
	-- Clerical Data Columns
	`lang`       INTEGER             NOT NULL,
	`script`     INTEGER             NOT NULL,
	`entry_id`   INTEGER             NOT NULL,
	`class_id`   INTEGER             NOT NULL,
	`class_type` INTEGER             NOT NULL,
	`morph_id`   INTEGER PRIMARY KEY NOT NULL,
	
	-- Presentational Data Columns
	`native`   VARCHAR (256) CHARSET 'utf8',
	`roman`    VARCHAR (256) CHARSET 'utf8',
	`phonemic` VARCHAR (256) CHARSET 'utf8',
	
	-- Syntactical Data Columns (Usage depends on `class_type`.)
	`definiteness` INTEGER,
	`number`       INTEGER,
	`case`         INTEGER,
	`verb_role` INTEGER,
	`tense`     INTEGER,
	`aspect`    INTEGER,
	`person`    INTEGER,
	`voice`     INTEGER,
	`irregular` BOOLEAN DEFAULT 0
);
ALTER TABLE `morph` ADD FOREIGN KEY (`lang`)         REFERENCES `enum_languages`       (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`script`)       REFERENCES `enum_scripts`         (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`entry_id`)     REFERENCES `entries`              (`entry_id`);
ALTER TABLE `morph` ADD FOREIGN KEY (`class_id`)     REFERENCES `classes`              (`class_id`);
ALTER TABLE `morph` ADD FOREIGN KEY (`class_type`)   REFERENCES `enum_classes`         (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`definiteness`) REFERENCES `inflect_definiteness` (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`number`)       REFERENCES `inflect_number`       (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`case`)         REFERENCES `inflect_case`         (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`verb_role`)    REFERENCES `inflect_verb_role`    (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`tense`)        REFERENCES `inflect_tense`        (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`aspect`)       REFERENCES `inflect_aspect`       (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`person`)       REFERENCES `inflect_person`       (`key`);
ALTER TABLE `morph` ADD FOREIGN KEY (`voice`)        REFERENCES `inflect_voice`        (`key`);

ALTER TABLE `entries` ADD FOREIGN KEY (`primary_morph`) REFERENCES `morph` (`morph_id`);

CREATE TABLE `senses` (
	`lang`     INTEGER NOT NULL,
	`entry_id` INTEGER NOT NULL,
	`class_id` INTEGER NOT NULL,
	`sense_id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL, -- It's less important how we're able to reference these from outside the database.
	`num`      INTEGER,
	`p` TEXT CHARSET 'utf8',
	`use` VARCHAR (128)
);
ALTER TABLE `senses` ADD FOREIGN KEY (`lang`)     REFERENCES `enum_languages` (`key`);
ALTER TABLE `senses` ADD FOREIGN KEY (`entry_id`) REFERENCES `entries`        (`entry_id`);
ALTER TABLE `senses` ADD FOREIGN KEY (`class_id`) REFERENCES `classes`        (`class_id`);

CREATE TABLE `subsenses` (
	`lang`        INTEGER NOT NULL,
	`entry_id`    INTEGER NOT NULL,
	`class_id`    INTEGER NOT NULL,
	`sense_id`    INTEGER NOT NULL,
	`subsense_id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL DEFAULT NULL,
	`num`         INTEGER,
	`p` TEXT CHARSET 'utf8',
	`use` VARCHAR (128)
);
ALTER TABLE `subsenses` ADD FOREIGN KEY (`lang`)     REFERENCES `enum_languages` (`key`);
ALTER TABLE `subsenses` ADD FOREIGN KEY (`entry_id`) REFERENCES `entries`        (`entry_id`);
ALTER TABLE `subsenses` ADD FOREIGN KEY (`sense_id`) REFERENCES `senses`         (`sense_id`);
ALTER TABLE `subsenses` ADD FOREIGN KEY (`class_id`) REFERENCES `classes`        (`class_id`);

CREATE TABLE `derivatives` (
	`parent_entry_id` INTEGER NOT NULL,
	`target_entry_id` INTEGER NOT NULL
);
ALTER TABLE `derivatives` ADD FOREIGN KEY (`parent_entry_id`) REFERENCES `entries` (`entry_id`);
ALTER TABLE `derivatives` ADD FOREIGN KEY (`target_entry_id`) REFERENCES `entries` (`entry_id`);

CREATE TABLE `extras` (
	`parent_entry_id` INTEGER NOT NULL,
	`extra_type`      INTEGER NOT NULL,
	`target_entry_id` INTEGER, -- Only used when `extra_type` is 'reference'
	`resource`    VARCHAR (256) CHARSET 'utf8', -- Image source URL for 'figure', or hyperlink (if present) for 'reference'
	`display_num` VARCHAR (64),                 -- Only used with 'figure'
	`caption`     TEXT          CHARSET 'utf8'  -- Only used with 'figure'
);
ALTER TABLE `extras` ADD FOREIGN KEY (`parent_entry_id`) REFERENCES `entries`     (`entry_id`);
ALTER TABLE `extras` ADD FOREIGN KEY (`target_entry_id`) REFERENCES `entries`     (`entry_id`);
ALTER TABLE `extras` ADD FOREIGN KEY (`extra_type`)      REFERENCES `enum_extras` (`key`);
