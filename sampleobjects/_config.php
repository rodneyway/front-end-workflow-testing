<?php 
	DataObject::add_extension('SiteConfig','RiskAssessmentSiteConfig');
	DataObject::add_extension('RiskWorksheet', 'WorkflowApplicable');
	DataObject::add_extension('SiteTree', 'WorkflowApplicable');
	Director::addRules(20, array('worksheets//$Action/$ID/$OtherID' => 'WorksheetController'));
?>