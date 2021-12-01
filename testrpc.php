<?php
require_once('easybitcoin.php');
$bitcoin = new Bitcoin('m19aCK2','','https://shabang.io', '8332');

$blockinfo = $bitcoin->getblockchaininfo();
print_r($blockinfo);
?>
