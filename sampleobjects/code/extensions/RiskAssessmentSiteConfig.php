<?php

/**
 * undocumented class
 *
 * @package risk-assessment
 * @author Shea Dawson <shea@silverstripe.com.au
 **/
class RiskAssessmentSiteConfig extends DataObjectDecorator
{
	function extraStatics() {
		return array(
            'has_one' => array(
                'RiskAssessmentWorkflow' => 'WorkflowDefinition'
            )
        );
    }
	
	public function updateCMSFields(&$fields){
		$fields->addFieldToTab('Root.RiskAssessmentSettings', new DropdownField('RiskAssessmentWorkflowID', 'Risk Assessment Workflow Definition', $this->getWorkflowOptions()));
	}
	
	function getWorkflowOptions(){
	    if($options = DataObject::get('WorkflowDefinition')){
	    	return $options->map('ID', 'Title', 'Please Select');
	    }else{
	    	return array('No Workflow Definitions found');
	    }
	}

}
