<?php

require_once 'monitoring.civix.php';
use CRM_Monitoring_ExtensionUtil as E;

/**
 * Implements hook_civicrm_permission().
 */
function monitoring_civicrm_permission(&$permissions) {
  $permissions['remote monitoring'] = [
    'label' => E::ts('CiviCRM Remote Monitoring'),
    'description' => E::ts('Grants the necessary API permissions for a monitoring user without Administer CiviCRM'),
  ];
}

function monitoring_civicrm_check(&$messages) {
  monitoring_checkIndices($messages);
  monitoring_checkXdebug($messages);
}

function monitoring_checkXdebug(&$messages) {
  if (function_exists('xdebug_is_debugger_active') && xdebug_is_debugger_active()) {
    $messages[] = new CRM_Utils_Check_Message(
      __FUNCTION__,
      ts('XDebug must be turned off on this site.'),
      ts('XDebug is enabled'),
      \Psr\Log\LogLevel::WARNING,
      'fa-bug'
    );
  }
}

/**
 * This is cribbed directly from core, where the check is disabled (plus a check not to run when db upgrade is needed).
 */
function monitoring_checkIndices(&$messages) {
  if (CRM_Core_BAO_Domain::isDBUpdateRequired()) {
    // Do not run this check when the db has not been updated as it might fail on non-updated schema issues.
    return [];
  }

  $missingIndices = civicrm_api3('System', 'getmissingindices', [])['values'];
  if ($missingIndices) {
    $html = '';
    foreach ($missingIndices as $tableName => $indices) {
      foreach ($indices as $index) {
        $fields = implode(', ', $index['field']);
        $html .= "<tr><td>{$tableName}</td><td>{$index['name']}</td><td>$fields</td>";
      }
    }
    $message = "<p>The following tables have missing indices. Click 'Update Indices' button to create them.<p>
      <p><table><thead><tr><th>Table Name</th><th>Key Name</th><th>Expected Indices</th>
      </tr></thead><tbody>
      $html
      </tbody></table></p>";
    $msg = new CRM_Utils_Check_Message(
      __FUNCTION__,
      ts($message),
      ts('Performance warning: Missing indices'),
      \Psr\Log\LogLevel::WARNING,
      'fa-server'
    );
    $msg->addAction(
      ts('Update Indices'),
      ts('Update all database indices now? This may take a few minutes and cause a noticeable performance lag for all users while running.'),
      'api3',
      ['System', 'updateindexes']
    );
    $messages[] = $msg;
  }
  return $messages;
}

/**
 * Gives the monitoring user permission to access API4 System.check.
 * TODO maybe: StatusPreference.Create authorization provider?
 */
function monitoring_authorizeCheck(\Civi\API\Event\AuthorizeEvent $event) {
  $apiRequest = $event->getApiRequest();
  if ($apiRequest instanceof \Civi\Api4\Action\System\Check) {
    $event->setAuthorized(CRM_Core_Permission::check('remote monitoring'));
  }
}

/**
 * Gives a user with the permission "remote monitoring" to access API4 System.check.
 */
function monitoring_civicrm_alterApiRoutePermissions(&$permissions, $entity, $action) {
  if ($entity === 'System' && $action === 'check') {
    $permissions[0][] = 'remote monitoring';
  }
}

/**
 * Gives the monitoring user permission to access API3 System.check, and to set status preferences (for hushing checks).
 */
function monitoring_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['system']['check'] = [['remote monitoring', 'administer CiviCRM']];
  $permissions['status_preference']['create'] = [['remote monitoring', 'administer CiviCRM']];
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function monitoring_civicrm_config(&$config) {
  _monitoring_civix_civicrm_config($config);
  Civi::dispatcher()->addListener('civi.api.authorize', "monitoring_authorizeCheck");
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function monitoring_civicrm_install() {
  _monitoring_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function monitoring_civicrm_enable() {
  _monitoring_civix_civicrm_enable();
}
