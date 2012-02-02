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
		//don't need to add any fields here...
	}

}
