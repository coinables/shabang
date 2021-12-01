
<?php

$blockCypher = "https://api.blockcypher.com/v1/btc/main";
//$fgc = file_get_contents($blockCypher);
//$json = json_decode($fgc, true);
$ch = curl_init($blockCypher);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
if(!$obj){
	//use a different source
	$btccom = "https://chain.api.btc.com/v3/block/latest";
	$ch = curl_init($btccom);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
	$latestBlockHash = $obj["data"]["hash"];
	$latestHeight = $obj["data"]["height"];	
} else {
$latestBlockHash = $obj["hash"];
$latestHeight = $obj["height"];	
}

echo $latestBlockHash;

function block($latestBlockHash){
    $uri="https://blockchain.info/block/".$latestBlockHash."?format=json";
    //$ch = file_get_contents($uri);
    //$obj = json_decode($ch, true);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}

$notsegwit = 0;
$issegwit = 0;
$totsinputs = 0;
$lightfund = 0;
$lightvalue = 0;

$block = block($latestBlockHash);
$length = count($block["tx"]);

for($i=0;$i<$length;$i++){
    $inputs = $block["tx"][$i]["inputs"];
    $numinputs = count($inputs);
    for($ii=0;$ii<$numinputs;$ii++){
        $totsinputs++;
        if($inputs[$ii]["witness"] === ""){
            //not segwit
            $notsegwit++;
        } else {
           //is segwit
           $issegwit++;
        }
    }
    
    //check outputs for lightning funding transactions addr will have length > 55
    $outputs = $block["tx"][$i]["out"];
    $numoutputs = count($outputs);
    for($oi=0;$oi<$numoutputs;$oi++){
        $addrLength = strlen($outputs[$oi]["addr"]);
        if($addrLength > 55){
            $lightfund++;
            $outputvalue = $outputs[$oi]["value"];
            $lightvalue += $outputvalue;
        }
    }
    
}

echo "segwit ".$issegwit;
echo "<br>";
echo "not segwit ".$notsegwit;

$segwitPercentage = $issegwit/$totsinputs;
$segwitPercentage = $segwitPercentage * 100;

//fetch lightning data
//get number of lightning nodes
function getNodes(){
    $uri = "https://shabang.io/nodes.json";
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
//end fetching lightning nodes



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

$findChannels = getChannels();
$numberOfChannels = count($findChannels["channels"]);
//end fetching lightning channels



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
$numberOfBlocks = $latestHeight;
//end fetching info

//database stuff
$conn = mysqli_connect("localhost", "shabanguser", "", "shabanghistory");
	if (mysqli_connect_errno()){
	echo "Connection to DB failed" . mysqli_connect_error();
	}

$findSegWitAvg = "SELECT * FROM lamesa WHERE blockheight > 500000 ORDER BY IDD DESC LIMIT 100";
$querySegWitAvg = mysqli_query($conn, $findSegWitAvg);
$segWitAvgArr = array();
while($segWitAvgLoop = mysqli_fetch_assoc($querySegWitAvg)){
	array_push($segWitAvgArr, $segWitAvgLoop["segwit"]);
}	
$segWitAvgSum = array_sum($segWitAvgArr);
$segWitAverage = $segWitAvgSum / 100;
    
$addSnapShot = "INSERT INTO lamesa (blockheight, lightningnodes, lightningchannels, segwit, segwitavg, segwitinputs, totsinputs, lightningfunds, lightningvalue) VALUES ('$numberOfBlocks','$numberOfNodes','$numberOfChannels','$segwitPercentage', '$segWitAverage', '$issegwit', '$totsinputs', '$lightfund', '$lightvalue')";
$inputSnap = mysqli_query($conn, $addSnapShot);
//  create json file
$getMainNet = "SELECT * FROM lamesa WHERE blockheight > 507000 ORDER BY idd DESC";
$doMainNet = mysqli_query($conn, $getMainNet);
$statsArray = array();
$jsonArray = array();
while($jsonLoop = mysqli_fetch_assoc($doMainNet)){
    $statsArray["height"] = $jsonLoop["blockheight"];
    $statsArray["lightning_nodes"] = $jsonLoop["lightningnodes"];
    $statsArray["lightning_channels"] = $jsonLoop["lightningchannels"];
    $statsArray["segwit_percentage"] = $jsonLoop["segwit"];
    $statsArray["segwit_inputs"] = $jsonLoop["segwitinputs"];
    $statsArray["total_inputs"] = $jsonLoop["totsinputs"];
    $statsArray["lightning_channels_funded"] = $jsonLoop["lightningfunds"];
    array_push($jsonArray, $statsArray);
}
$encJson = json_encode($jsonArray);
$makeJson = "stats.json";
file_put_contents($makeJson, $encJson);

?>