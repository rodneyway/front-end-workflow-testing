<div class="column three" id="sidebar">
	<% include SideMenu %>
</div>
<div class="column nine" id="content">
	$Content
	
	<h2>Risk Worksheet Index Page</h2><br />
	
	<% control RiskWorksheetList %>
		<% if Title %>
			$ID <a href="$EditLink">$Title</a><br />
		<% end_if %>
	<% end_control %>
	
	$PageComments
	<% include BreadCrumbs %>
</div>