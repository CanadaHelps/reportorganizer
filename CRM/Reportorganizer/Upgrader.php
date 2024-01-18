<?php
use CRM_Reportorganizer_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Reportorganizer_Upgrader extends CRM_Reportorganizer_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  public function postInstall() {
    // Add entries in component report instance section.
    $contribComponent = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_component WHERE name = 'CiviContribute'");
    $contactComponent = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_component WHERE name = 'CiviContact'");
    $opportunityComponent = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_component WHERE name = 'CiviGrant'");

    // Add entries in component report template section.
    $templateSections = [
      $contribComponent => [
        "General Contribution Reports" => [
          "Contributions (Summary)",
          "Contributions (Detailled)",
          "Repeat Contributions",
          "Top Donors",
          "SYBUNT",
          "LYBNT",
          "Contributions by Organization",
          "Contributions by Household",
          "Contributions by Relationship",
          "Contributions for Bookkeeping",
          "Contributions (Extended, Summary)",
          "Contributions (Detailed)",
          "Contributions (Extended, Pivot Chart)",
          "Contributions (Extended, Extra Fields)",
          "Contributions for Bookkeeping (Detailed)",
        ],
        "Recurring Contribution Reports" => [
          "Recurring Contributions (Summary)",
          "Recurring Contributions (Detailled)",
          "Recurring Contributions (Extended, Pivot Chart)",
          "Recurring Contributions (Detailed)",
        ],
        "Receipt Reports" => [
          "Tax Receipts (Issued)",
          "Tax Receipts (Not Yet Issued)",
        ],
      ],
      $contactComponent => [
        "General Contact Reports" => [
          "Contacts (Summary)",
          "Contacts (Detailled)",
          "Contacts (Detailed)",
          "Contacts (Extended, Pivot Chart)",
          "Database Log",
          "Address History",
        ],
        "Activity Reports" => [
          "Activities (Summary)",
          "Activities (Detailled)",
          "Activities (Extended)",
          "Activities (Extended, Pivot Chart)",
          "Activities (Detailed)",
        ],
        "Relationship Reports" => [
          "Relationships",
          "Current Employer",
          "Relationships (Detailed)",
        ]
      ],
    ];
    foreach ($templateSections as $component => $sectionHeader) {
      foreach($sectionHeader as $header => $reportTemplate) {
        $optionVal = civicrm_api3('OptionValue', 'create', [
          'option_group_id' => 'component_template_section',
          'label' => $header,
          'component_id' => $component,
        ]);
        foreach ($reportTemplate as $reportTitle) {
          // Fetch the report template by label.
          $template = civicrm_api3("ReportTemplate", "get", [
            "sequential" => 1,
            "label" => $reportTitle,
          ]);
          if (!empty($optionVal['id']) && !empty($template['id'])) {
            $dao = new CRM_Reportorganizer_BAO_ReportTemplateOrganizer();
            $dao->component_id = $component;
            $dao->section_id = $optionVal['values'][$optionVal['id']]['value'];
            $dao->report_template_id = $template['id'];
            $dao->find(TRUE);
            $dao->save();
            $dao->free();
          }
        }
      }
    }

    $instanceSections = [
      $contribComponent => [
        "Contribution History by Campaign" => [
          "Contribution History by Campaign (Summary)",
          "Contribution History by Campaign (Detailed)",
          "Contribution History by Campaign (Monthly)",
          "Contribution History by Campaign (Yearly)",
        ],
        "Contribution History by Campaign Group" => [
          "Contribution History by Campaign Group (Summary)",
          "Contribution History by Campaign Group (Detailed)",
        ],
        "Contribution History by Fund" => [
          "Contribution History by CH Fund (Summary)",
          "Contribution History by Fund (Summary)",
          "Contribution History by Fund (Detailed)",
          "Contribution History by Fund (Monthly)",
          "Contribution History by Fund (Yearly)",
        ],
        "Contribution History by GL Account" => [
          "Contribution History by GL Account (Summary)",
          "Contribution History by GL Account (Detailed)",
        ],
        "Custom Contribution Reports" => [],
      ],
      $contactComponent => [
        "Custom Contact Reports" => []
      ],
      $opportunityComponent => [
        "Custom Opportunity Reports" => [],
      ]
    ];
    foreach ($instanceSections as $component => $sectionHeader) {
      foreach ($sectionHeader as $header => $instanceTitles) {
        $optionVal = civicrm_api3('OptionValue', 'create', [
          'option_group_id' => 'component_section',
          'label' => $header,
          'component_id' => $component,
        ]);
        foreach ($instanceTitles as $instanceTitle) {
          if (!empty($instanceTitle)) {
            $instance = civicrm_api3("ReportInstance", "get", [
              "sequential" => 1,
              "title" => $instanceTitle,
            ]);
            if (!empty($instance['id']) && $optionVal['id']) {
              $dao = new CRM_Reportorganizer_DAO_ReportOrganizer();
              $dao->component_id = $component;
              $dao->section_id = $optionVal['values'][$optionVal['id']]['value'];
              $dao->report_instance_id = $instance['id'];
              $dao->find(TRUE);
              $dao->save();
              $dao->free();
            }
          }
        }
      }
    }

    // Add the remainder of the report instances to custom section.
    $excludeReports = [
      $contactComponent => [
        "Contact Report (Detailed)",
        "Activity Report",
        "New Email Replies",
        "Relationship Report",
      ],
      $contribComponent => [
        "Contribution History by Campaign (Summary)",
        "Contribution History by Campaign (Detailed)",
        "Contribution History by Campaign (Monthly)",
        "Contribution History by Campaign (Yearly)",
        "Contribution History by Campaign Group (Summary)",
        "Contribution History by Campaign Group (Detailed)",
        "Contribution History by CH Fund (Summary)",
        "Contribution History by Fund (Summary)",
        "Contribution History by Fund (Detailed)",
        "Contribution History by Fund (Monthly)",
        "Contribution History by Fund (Yearly)",
        "Contribution History by GL Account (Summary)",
        "Contribution History by GL Account (Detailed)",
        "Contribution History by Source (Summary)",
        "Recurring Contributions (Summary)",
        "Receipts",
      ],
      $opportunityComponent => [
        "Opportunity Report",
      ]
    ];
    foreach ($excludeReports as $component => $reportsToExclude) {
      $sql = "SELECT r.id FROM civicrm_report_instance r
      INNER JOIN civicrm_option_value v ON r.report_id = v.value
      INNER JOIN civicrm_option_group g ON g.id = v.option_group_id AND g.name = 'report_template'
      WHERE r.title NOT IN ('" . implode("', '", $reportsToExclude) . "')
      AND v.component_id = %1";
      $customReports = CRM_Core_DAO::executeQuery($sql, [1 => [$component, 'Integer']])->fetchAll();
      foreach ($customReports as $customReport) {
        $dao = new CRM_Reportorganizer_DAO_ReportOrganizer();
        $dao->report_instance_id = $customReport['id'];
        $dao->find(TRUE);
        $dao->component_id = $component;
        $dao->section_id = CRM_Core_DAO::singleValueQuery("SELECT v.value
        FROM civicrm_option_value v
        INNER JOIN civicrm_option_group g ON g.id = v.option_group_id AND g.name = 'component_section'
        WHERE v.component_id = %1 AND v.label LIKE 'Custom%'", [1 => [$component, 'Integer']]);
        $dao->report_instance_id = $customReport['id'];
        $dao->save();
        $dao->free();
      }
    }
  }

  public function upgrade_4211() {
    $this->ctx->log->info('CRM-1206-Typo in "Contributions (Detailed)" Report Template when you select "Create New Report" with SuperAdmin cred.'); // PEAR Log interface

    $reportLabelUpdateData =   ['CRM_Report_Form_Contact_Detail' => 'Contacts (Detailed)', 
    'CRM_Report_Form_Activity' => 'Activities (Detailed)', 
    'CRM_Report_Form_Contribute_Detail'=> 'Contributions (Detailed)', 
    'CRM_Report_Form_Contribute_Recur'=>'Recurring Contributions (Detailed)'];
    foreach($reportLabelUpdateData as $reportKey =>$reportValue) {
      $sql = "UPDATE `civicrm_option_value` SET `label`='$reportValue' WHERE `name` = '$reportKey' AND `option_group_id` = (SELECT `id` from `civicrm_option_group` WHERE `name` = 'report_template')";
      CRM_Core_DAO::executeQuery($sql);
    }
    return TRUE;
  }



  ### BELOW THIS POINT: use new format. ### 
  ### Example: upgrade_102001 => Version 1.2.x, upgrade function 001 ###

}
