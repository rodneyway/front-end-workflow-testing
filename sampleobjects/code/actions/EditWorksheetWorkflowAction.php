<?php
/**
 * A workflow action that notifies users attached to the workflow path that they have a task awaiting them.
 *
 * @license    BSD License (http://silverstripe.org/bsd-license/)
 * @package    advancedworkflow
 * @subpackage actions
 */
class EditWorksheetsWorkflowAction extends WorkflowAction {

	public static $db = array(

	);

	public static $icon = 'advancedworkflow/images/notify.png';

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		return $fields;
	}

	public function execute(WorkflowInstance $workflow) {
		return true;
	}

	public function updateFrontendWorkflowFields($fields, $workflow){
		$dofields = $workflow->getTarget()->getFrontendFields();
		
		foreach ($dofields as $field) {
			$fields->push($field);
		}
		return $fields;
	}

}
