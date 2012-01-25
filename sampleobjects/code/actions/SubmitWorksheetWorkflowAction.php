<?php

class SubmitWorksheetWorkflowAction extends WorkflowAction{

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
