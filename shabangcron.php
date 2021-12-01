<?php
require_once('easybitcoin.php');
$bitcoin = new Bitcoin('m19aCK2','m6G22Z+#j2K6Kv=B');

$blockinfo = $bitcoin->getblockchaininfo();
$datablock = $bitcoin->getblock($blockinfo["bestblockhash"]);
$hhash = $datablock["hash"];
$cconfirmations = $datablock["confirmations"];
$sstrippedsize = $datablock["strippedsize"];
$ssize = $datablock["size"];
$wweight = $datablock["weight"];
$hheight = $datablock["height"];
$vversion = $datablock["version"];
$vversionhex = $datablock["versionHex"];
$merkleroot = $datablock["merkleroot"];
$txarray = $datablock["tx"];
$ttx = serialize($txarray);
$ttime = $datablock["time"];
$mediantime = $datablock["mediantime"];
$nnonce = $datablock["nonce"];
$bbits = $datablock["bits"];
$ddifficulty = $datablock["difficulty"];
$chainwork  = $datablock["chainwork"];
$previousblockhash = $datablock["previousblockhash"];
$nextblockhash = $datablock["nextblockhash"];


//database stuff
$conn = mysqli_connect("localhost", "shabanguser", "", "shabanghistory");
	if (mysqli_connect_errno()){
	echo "Connection to DB failed" . mysqli_connect_error();
	}
	
    
$insertBlock = "INSERT INTO blocks (hhash, cconfirmations, sstrippedsize, ssize, wweight, hheight, vversion, vversionhex, merkleroot, ttx, ttime, mediantime, nnonce, bbits, ddifficulty, chainwork, previousblockhash, nextblockhash) VALUES ('$hhash', '$cconfirmations', '$sstrippedsize', '$ssize', '$wweight', '$hheight', '$vversion', '$vversionhex', '$merkleroot', '$ttx', '$ttime', '$mediantime', '$nnonce', '$bbits', '$ddifficulty', '$chainwork', '$previousblockhash', '$nextblockhash')";
mysqli_query($conn, $insertBlock);

?>