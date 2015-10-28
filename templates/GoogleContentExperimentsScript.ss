<% if GoogleContentExperiment %>
<script src="//www.google-analytics.com/cx/api.js"></script>
<script>
<% loop getGoogleContentExperimentsData %>
	cxApi.setChosenVariation('{$VariationID}','{$ExperimentID}');
<% end_loop %>
</script>
<% end_if %>