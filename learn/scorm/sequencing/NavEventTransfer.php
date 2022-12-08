<script language="javascript">
	
	var sco_ID="<?=$_GET['scoID']?>";
	var index="<?=$_GET['idx']?>";
	var navEvent="<?=$_GET['navEvent']?>";

	parent.parent.engine.SequencingEngineObj.ClearNavigationRequest();
	parent.parent.engine.SequencingEngineObj.NavigationRequestProcess(sco_ID, index, navEvent);

</script>