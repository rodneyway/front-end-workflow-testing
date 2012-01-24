<?php
class WorksheetController extends FrontendWorkflowController {

	public function index() {
		return $this->renderWith(array('Page'));
	}

	public function WorksheetForm(){
		$svc = singleton('WorkflowService');
		
		$active = $svc->getWorkflowFor($this->getContextObject());
		$current = $active->CurrentAction();
		$wfFields = $active->getWorkflowFields(); 
		
		$fields = $wfFields;
		$actions = new FieldSet();
		$validator = singleton('RiskWorksheet')->getRequiredFields();
                
		$this->extend('updateFrontendActions', $actions);
		$this->extend('updateFrontendFields', $fields);
                
		$form = new Form($this, 'BusinessForm', $fields, $actions, $validator);
                
		return $form;
	}
	
	public function Link($action = null){
    	return 'worksheets/' . $action;
	}
		
	function getContextType() {
		return 'RiskWorksheet';
	}
	
	function getContextObject() {
		$obj = DataObject::get_by_id($this->getContextType(),$this->getContextID());
		return $obj;	
	}
	
	function getContextID() {
		$id = $this->request->param('ID');
		return $id;
	}
	
	//workflowservice::getWorkflowForm
	
	/* Provide method for possible different use cases */
	function getWorkflowDefinition() {
		//siteconfig for us
	}
	
	function start() {
		$ws = new RiskWorksheet();
		$ws->WorkflowDefinitionID = SiteConfig::current_site_config()->RiskAssessmentWorkflowID;
		
		$ws->write();

		$svc = singleton('WorkflowService');
		$svc->startWorkflow($ws);
		
		//Debug::show($svc);
		
		$this->redirect($this->Link('edit/'.$ws->ID));
	}
	
	function edit() {
		$this->Form = $this->WorksheetForm();	
		return $this->renderWith(array('Page'));
	}	
		
	
}