-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_report_template_organizer`;
DROP TABLE IF EXISTS `civicrm_report_instance_organizer`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_report_instance_organizer
-- *
-- * Table that contains the sections for report instances
-- *
-- *******************************************************/
CREATE TABLE `civicrm_report_instance_organizer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ReportOrganizer ID',
  `component_id` int unsigned COMMENT 'FK to CiviCRM Component',
  `section_id` int unsigned COMMENT 'Pseudo FK to civicrm_option.value WHERE option_group = report_template',
  `report_instance_id` int unsigned COMMENT 'FK to CiviCRM Report Instance',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_component_id_section_id_report_instance_id`(component_id, section_id, report_instance_id),
  CONSTRAINT FK_civicrm_report_instance_organizer_component_id FOREIGN KEY (`component_id`) REFERENCES `civicrm_component`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_report_instance_organizer_report_instance_id FOREIGN KEY (`report_instance_id`) REFERENCES `civicrm_report_instance`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_report_template_organizer
-- *
-- * Table that contains the sections for report templates
-- *
-- *******************************************************/
CREATE TABLE `civicrm_report_template_organizer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ReportOrganizer ID',
  `component_id` int unsigned COMMENT 'FK to CiviCRM Component',
  `section_id` int unsigned COMMENT 'Pseudo FK to civicrm_option.value WHERE option_group = component_section',
  `report_template_id` int unsigned COMMENT 'Pseudo FK to civicrm_option.value WHERE option_group = report_template',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_component_id_section_id_report_template_id`(component_id, section_id, report_template_id),
  CONSTRAINT FK_civicrm_report_template_organizer_component_id FOREIGN KEY (`component_id`) REFERENCES `civicrm_component`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;
