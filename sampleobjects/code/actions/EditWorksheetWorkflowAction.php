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

	/**
	 * @param  DataObject $target
	 * @return array
	 */
	public function getContextFields(DataObject $target) {
		$fields = $target->summaryFields();
		$result = array();

		foreach($fields as $field) {
			$result[$field] = $target->$field;
		}

		if($target instanceof SiteTree) {
			$result['CMSLink'] = singleton('CMSMain')->Link("show/{$target->ID}");
		} else if ($target->hasMethod('WorkflowLink')) {
			$result['CMSLink'] = $target->WorkflowLink();
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getMemberFields() {
		$member = Member::currentUser();
		$result = array();

		if($member) foreach($member->summaryFields() as $field => $title) {
			$result[$field] = $member->$field;
		}

		if($member && !array_key_exists('Name', $result)) {
			$result['Name'] = $member->getName();
		}

		return $result;
	}

}
