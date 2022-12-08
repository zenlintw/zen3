<html>
<head>
</HEAD>
<BODY marginwidth="0" marginheight="0" topmargin="0" leftmargin="0" >
<p align="left">
<OBJECT ID="NSPlay" WIDTH=72 HEIGHT=27 classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
standby="Loading Microsoft Windows Media Player components..."
type="application/x-oleobject">

    <PARAM NAME="FileName" VALUE="mms://<?echo $_GET['MMS_Server'];?>/<?echo $_GET['SCUID']?>">

    <PARAM NAME="ShowControls" VALUE="1">
    <PARAM NAME="ShowDisplay" VALUE="0">
    <PARAM NAME="AutoSize" VALUE="0">
    <PARAM NAME="AutoStart" VALUE="1">
  
</OBJECT>

</BODY>
</HTML>
