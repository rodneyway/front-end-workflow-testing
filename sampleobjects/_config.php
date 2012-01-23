<?php 
	DataObject::add_extension('SiteConfig','RiskAssessmentSiteConfig');
	DataObject::add_extension('RiskWorksheet', 'WorkflowApplicable');
	Director::addRules(20, array('worksheets//$Action/$ID/$OtherID' => 'WorksheetController'));
?>