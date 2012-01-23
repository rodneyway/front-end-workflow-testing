<?php
class RiskWorksheet extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar(255)',
		'Stakeholders'	=> 'Text',
		'Context'		=> 'Text'
	);
	
	public function getFrontendFields(){
		$fields = new FieldSet(array(
			new TextField('Title', 'Title'),
			new TextAreaField('Stakeholders', 'Key Stakeholders'),
			new TextAreaField('Context', 'Operating Environment & Context'),
			new HiddenField('ID', 'ID')
		));
		return $fields;
	}
	
	public function getFrontendValidator(){
		$validator = new RequiredFields(array('Title'));
		return $validator;
	}
	
	public function Link(){
		return RiskWorksheetController::Link('view/' . $this->ID);
	}
	
	public function EditLink(){
		return RiskWorksheetController::Link('edit/' . $this->ID);
	}
	
	public function AddRiskLink(){
		return RiskWorksheetController::Link('addrisk/' . $this->ID);
	}
	
	public function SubmitLink(){
		return RiskWorksheetController::Link('submitworksheet/' . $this->ID);
	}
	
}