<?php
class RiskWorksheet extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar(255)',
		'Stakeholders'	=> 'Text',
		'Context'		=> 'Text'
	);
	
	// think these are actually defined in the workflow
	
	public function getFrontendFields(){
		$fields = new FieldSet(array(
			new TextField('Title', 'Title'),
			new TextAreaField('Stakeholders', 'Key Stakeholders'),
			new TextAreaField('Context', 'Operating Environment & Context'),
			new HiddenField('ID', 'ID')
		));
		return $fields;
	}
	
	public function getFrontendActions(){
		$actions = new FieldSet(array());
		$this->extend('updateFrontendActions', $actions);
		return $actions;
	}
	
	/*
	 *  @todo implement canView function
	 */
	public function canView() {
		return true;
	}
	
	/*
	 *  @todo implement canEdit function
	 */
	public function canEdit() {
		return true;
	}
	
	
	public function getRequiredFields(){
		$validator = new RequiredFields(array('Title'));
		return $validator;
	}
	
	public function Link(){
		return WorksheetController::Link('view/' . $this->ID);
	}
	
	public function EditLink(){
		return WorksheetController::Link('edit/' . $this->ID);
	}
	
	public function AddRiskLink(){
		return WorksheetController::Link('addrisk/' . $this->ID);
	}
	
	public function SubmitLink(){
		return WorksheetController::Link('submitworksheet/' . $this->ID);
	}
	
}