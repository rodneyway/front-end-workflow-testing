<?php

/**
 * Admin controller for managing Risks
 *
 * @author Rodney Way <rodney@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class RiskAssessmentAdmin extends ModelAdmin {
	public static $managed_models = array(
		'RiskWorksheet',
	);
	
	public static $url_segment = 'riskadmin';
	public static $menu_title = "Risk Admin";
	
}