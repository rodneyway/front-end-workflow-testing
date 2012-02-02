<?php
class WorksheetController extends FrontendWorkflowController {
	
	public function handleAction($request){
		// do stuff here to handle workflow defined actions
				
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
		if (!$this->contextObj) {
			$id = $this->getContextID();
			
			if ($id) {
				$cType = $this->getContextType();
				$cObj = DataObject::get_by_id($cType, $id);
					
				$this->contextObj = $cObj->canView() ? $cObj : null;
			}
		}
		
		return $this->contextObj;
	}
	
	function getContextID() {
		$id = $this->contextObj ? $this->contextObj->ID : null;
		
		if (!$id) {
			if ($this->request->param('ID')) {
				$id = (int) $this->request->param('ID');
			} else if ($this->request->requestVar('ID')) {
				$id = (int) $this->request->requestVar('ID');
			}
		}
		
		return $id;
	}
		
	/* Provide method for possible different use cases */
	function getWorkflowDefinition() {
		if($id = $this->SiteConfig()->RiskAssessmentWorkflowID){
			return DataObject::get_by_id('WorkflowDefinition', $id);
		}
	}
	
	public function save(array $data, Form $form, SS_HTTPRequest $request) {
		$obj = $this->getContextObject();
		
		if (!$obj) {
			throw new Exception('Context Object Not Found');
		}
		
		//Only Save data when Transition is 'Active'	
		if ($this->getCurrentTransition()->Type == 'Active') {
			if ($obj->canEdit()) {
				$form->saveInto($obj);
				$obj->write();
			}
		}
		
		//run execute on instance		
		$action = $this->contextObj->getWorkflowInstance()->currentAction();
		$action->BaseAction()->execute($this->contextObj->getWorkflowInstance());
		
		//get valid transitions
		$transitions = $action->getValidTransitions();
		
		//tell instance to execute transition
		if ($transitions->find('ID',$this->transitionID)) {
			$this->contextObj->getWorkflowInstance()->performTransition($this->getCurrentTransition());
		}
		
		$this->redirect($this->contextObj->EditLink());
	}
	
	public function SiteConfig(){
		return SiteConfig::current_site_config();
	}
	
}