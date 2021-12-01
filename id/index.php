<?php

//get nodeId
if(!$_GET["node"]){ die("Missing node parameter"); }
$countGet = strlen($_GET["node"]);
if($countGet !== 66){ die("Invalid nodeid"); }
$thisNodeId = $_GET["node"];


//get number of lightning nodes
function getNodes(){
    $uri = "https://shabang.io/nodes.json";
    //$ch = file_get_contents($uri);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    //$obj = json_decode($ch, true);
    return $obj;
}

//get number of channels
function getChannels(){
    $uri = "https://shabang.io/channels.json";
    //$ch = file_get_contents($uri);
    //$obj = json_decode($ch, true);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}


$findNodes = getNodes();
$numberOfNodes = count($findNodes["nodes"]);
$foundNode = 0;
$foundCounter = 0;
while($foundNode < 1){
	if($findNodes["nodes"][$foundCounter]["nodeid"] === $thisNodeId){
		//data
		$inodeid = $findNodes["nodes"][$foundCounter]["nodeid"];
		$ialias = $findNodes["nodes"][$foundCounter]["alias"];
		$icolor = $findNodes["nodes"][$foundCounter]["color"];
		$ilastactivity = $findNodes["nodes"][$foundCounter]["last_timestamp"];
		$iaddresses = $findNodes["nodes"][$foundCounter]["addresses"];
		$inumaddys = count($iaddresses);
			if($inumaddys < 1){
				$iaddytype = "Not Available";
				$iaddyaddr = "Not Available";
				$iaddyport = "Not Available";
			} else {
				$iaddytype = $findNodes["nodes"][$foundCounter]["addresses"][0]["type"];
				$iaddyaddr = $findNodes["nodes"][$foundCounter]["addresses"][0]["address"];
				$iaddyport = $findNodes["nodes"][$foundCounter]["addresses"][0]["port"];
			}
					
		$foundNode = 2;
	} else {
		$foundCounter++;
	}
}
//end fetching lightning nodes

//get info
function getInfo(){
    $uri = "https://shabang.io/info.json";
    //$ch = file_get_contents($uri);
    //$obj = json_decode($ch, true);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}

$findInfo = getInfo();
$numberOfBlocks = $findInfo["blockheight"];
//end fetching info

if(isset($_POST["searchbtn"])){
	$searchInput = trim($_POST["searchnode"]);
	header("Location: https://shabang.io/id/?node=".$searchInput);
}


?>
<!DOCTYPE html>
<html>
<head>
<title>Bitcoin Lightning Network Data</title>
<style>
html, body{
    font-family: "Arial", sans-serif;
	padding: 0;
	margin: 0;
}
#headerBar{
	height: 40px;
	background-color: purple;
	color: #fff;
	padding: 3px;
	font-size: 24px;
}
.cexample{
    padding: 3px;
    background-color: #ccc;
    font-family: "Lucida Console", "Courier New", monospace;
    border-radius: 3px;
}
.lightning-stats-after{
    max-width: 150px;
    display: inline-block;
    padding: 7px;
    border: 1px solid #ddd;
    border-radius: 2px;
    font-size: 32px;
    font-weight: bold;
    position: relative;
    text-align: center;
    margin: auto;
}
.statsDescription{
    display: block;  
    padding: 3px;
    font-family: "Helvetica Narrow","Arial Narrow",Tahoma;
    font-size: 12px;
    font-weight: normal;
}
#container{
    width: 95%;
	padding: 15px;
	margin: auto;
	position: relative;
	display: block;
}
.tdheader{
    padding: 4px;
    border: 1px solid #ddd;
    border-radius: 2px;
    background-color: purple;
    color: #fff;
    font-weight: bold;
    text-align: center;
}
.trdata:hover{
    background-color: purple;
    color: #fff;
}
.body{
    width: 80%;
    margin: auto;
    text-align: center;
}
.channels{
        padding: 5px;
        background-color: darkslateblue;
        color: aliceblue;
        border-radius: 8px;
        border: 1px solid #000039;
        margin: 3px;
		display: inline-block;
}
.nodeAlias{
	padding: 5px;
	background-color: white;
	color: #111111;
	border-radius: 8px;
	border: 1px solid #fff;
	margin: 3px;
	display: inline-block;
}

#searchOutput{
	width: 90%;
	position: relative
	text-align: right;
}
.lightning-stats-nodes{
    max-width: 250px;
    display: inline-block;
    padding: 7px;
    border: 1px solid #ddd;
    border-radius: 2px;
    font-size: 32px;
    font-weight: bold;
    position: relative;
    text-align: center;
    margin: auto;
}
#clear{
	background-color: white;
    border: 1px solid #ddd;
    border-radius: 2px;
    padding: 3px;
    font-family: "Helvetica Narrow","Arial Narrow",Tahoma;
    font-size: 12px;
    font-weight: normal;
}
.channelList{
	display: inline-block;
	height: 18px;
	width: 18px;
	padding: 2px;
}
#qrcodeout{
	width: 150px;
}
</style>
	<script src="../jquery-2.1.1.min.js"></script>
</head>
<body>
<div id="headerBar"><b><a href="https://shabang.io" style="color:#fff">Shabang.io</a></b></div>
<div id="container">
<?php 
echo '<h1 style="color:#'.$icolor.'">'.$ialias.'</h1>';
echo "<b>Nodeid: </b>".$inodeid;
echo "<br>";

echo "<br>";
echo "<b>Last active: </b>".date("Y-m-d\ H:i", $ilastactivity);
echo "<br>";
echo "<b>IP Address: </b>".$iaddytype." ".$iaddyaddr;
echo "<br>";
echo "<b>Port: </b>".$iaddyport;
echo "<br>";
echo "<br>";
echo "<b>URI:</b> ".$inodeid."@".$iaddyaddr.":".$iaddyport;
$nodeURI = $inodeid."%40".$iaddyaddr."%3A".$iaddyport;
echo "<br>";
//echo urlencode(":"); output %40
//echo '<img src="http://chart.googleapis.com/chart?chs=150&cht=qr&chl='.$nodeURI.'">';
echo '<div id="qrcodeout"></div>';
echo "<br>";
echo "Open a channel with ".$ialias."<br>";
echo "Scan with the Eclair Mobile Lightning Wallet";
?>
<br><br>
<h3>Open Channels</h3>
<?php
//search nodeid in channels json, count total, and break down details on up to 10 channels OR each channel
$findChannels = getChannels();
$numberOfChannels = count($findChannels["channels"]);
//end fetching lightning channels
			
			for($ic=0;$ic<$numberOfChannels;$ic++){
				if($findChannels["channels"][$ic]["destination"] === $thisNodeId){
					
					$connectedTo = $findChannels["channels"][$ic]["source"];
					//get a few details on who connected to
					$findNodesCh = getNodes();
					$numberOfNodesCh = count($findNodesCh["nodes"]);
					$foundNodeCh = 0;
					$foundCounterCh = 0;
					while($foundNodeCh < 1){
						if($findNodesCh["nodes"][$foundCounterCh]["nodeid"] === $connectedTo){
							//data
							$inodeidCh = $findNodesCh["nodes"][$foundCounterCh]["nodeid"];
							$ialiasCh = $findNodesCh["nodes"][$foundCounterCh]["alias"];
							$icolorCh = $findNodesCh["nodes"][$foundCounterCh]["color"];
							$ilastactivityCh = $findNodesCh["nodes"][$foundCounterCh]["last_timestamp"];
							$iaddressesCh = $findNodesCh["nodes"][$foundCounterCh]["addresses"];
							$inumaddysCh = count($iaddressesCh);
								if($inumaddysCh < 1){
									$iaddytypeCh = "Not Available";
									$iaddyaddrCh = "Not Available";
									$iaddyportCh = "Not Available";
								} else {
									$iaddytypeCh = $findNodesCh["nodes"][$foundCounterCh]["addresses"][0]["type"];
									$iaddyaddrCh = $findNodesCh["nodes"][$foundCounterCh]["addresses"][0]["address"];
									$iaddyportCh = $findNodesCh["nodes"][$foundCounterCh]["addresses"][0]["port"];
								}
							echo '<div class="channelList" style="background-color:#'.$icolorCh.'">&nbsp;</div><a href="?node='.$inodeidCh.'">'.$ialiasCh.'</a></li><br>';		
							$foundNodeCh = 2;
						} else {
							$foundCounterCh++;
						}
					}
					//end fetching lightning nodes
				}
			} // end channels for loop
?>
</div>
 <script type="text/javascript" src="qrcode.js"></script>
<script>
var urinode = "<?php echo $nodeURI; ?>";
new QRCode(document.getElementById("qrcodeout"), urinode);
</script>
</body>
</html>