<% if GoogleContentExperiment %>
<script src="//www.google-analytics.com/cx/api.js"></script>
<script>
<% control getGoogleContentExperimentsData %>
	cxApi.setChosenVariation('{$VariationID}','{$ExperimentID}');
<% end_control %>
</script>
<% end_if %>