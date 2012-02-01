<?php
class WorksheetController extends FrontendWorkflowController {

	public function handleAction($request){
		
		//Debug::show($request);
		// do stuff here to handle workflow defined actions
				
		$vars = $request->postVars();
		$transPrefix = 'action_transition_';
		
		foreach ($vars as $k => $v) {
			if (substr_count($k, $transPrefix)) {
				$transitionID = (int)str_replace($transPrefix, '', $k);
			}
		}
		
		if (isset($transitionID)) {
			//do stuff here...
		}
		
		Debug::show($request->allParams());
		//Debug::show($request->param('Action'));
		
		
		return parent::handleAction($request);
	}
	
	public function index() {
		return $this->renderWith(array('Page'));
	}
	
	function start() {
		$ws = new RiskWorksheet();
		$ws->WorkflowDefinitionID = SiteConfig::current_site_config()->RiskAssessmentWorkflowID;
		$ws->write();

		$svc = singleton('WorkflowService');
		$svc->startWorkflow($ws);
		
		$this->redirect($this->Link('edit/'.$ws->ID));
	}
	
	function edit() {
		return $this->renderWith(array('Page'));
	}
	
	public function Link($action = null){
    	return 'worksheets/' . $action;
	}
		
	function getContextType() {
		//if($this->request->param('Action') == 'addrisk'){
		//	return 'Risk';
		//}else{
			return 'RiskWorksheet';
		//}
		
	}
	
	function getContextObject() {
		
		//@todo handle scendario where context id is not return (ie. list page /worksheets)
		
		$obj = DataObject::get_by_id($this->getContextType(),$this->getContextID());
		return $obj;
	}
	
	function getContextID() {
		$id = $this->request->param('ID') ? $this->request->param('ID') : $this->request->postVar('ID');
		return $id;
	}
		
	/* Provide method for possible different use cases */
	function getWorkflowDefinition() {
		if($id = $this->SiteConfig()->RiskAssessmentWorkflowID){
			return DataObject::get_by_id('WorkflowDefinition', $id);
		}
	}	
	
	public function SiteConfig(){
		return SiteConfig::current_site_config();
	}
	
}