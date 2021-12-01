<?php

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

//most popular channel ids
$channelsArr = array();
for($ic=0;$ic<$numberOfChannels;$ic++){
    array_push($channelsArr, $findChannels["channels"][$ic]["destination"]);
}
$nodeValues = array_count_values($channelsArr);
arsort($nodeValues);
$popular = array_slice(array_keys($nodeValues), 0, 100, true);


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

//database stuff
$conn = mysqli_connect("localhost", "shabanguser", "", "shabanghistory");
	if (mysqli_connect_errno()){
	echo "Connection to DB failed" . mysqli_connect_error();
	}

$getDatabase = "SELECT * FROM lamesa WHERE blockheight > 507000 ORDER BY idd DESC LIMIT 10";
$queryDatabase = mysqli_query($conn, $getDatabase);

$historyNodes = "SELECT * FROM lamesa WHERE blockheight > 507000 ORDER BY idd ASC";
$queryNodes = mysqli_query($conn, $historyNodes);
$numLightningNodesArr = array();
$segwitAdoption = array();
$lighningChannelsHist = array();
$channelsFundedHist = array();
$blockHeightHist = array();
while($cycleNodes = mysqli_fetch_assoc($queryNodes)){
    $numLightningNodesArr[] = $cycleNodes["lightningnodes"];
    $segwitAdoption[] = $cycleNodes["segwit"];
    $lightningChannelsHist[] = $cycleNodes["lightningchannels"];
    $channelsFundedHist[] = $cycleNodes["lightningfunds"];
	$blockHeightHist[] = $cycleNodes["blockheight"];
}

$avgSegwit = "SELECT * FROM lamesa WHERE segwitavg > 0 ORDER BY idd ASC";
$querySegwit = mysqli_query($conn, $avgSegwit);
$avgSegWitArray = array();
while($insertSegwitAvg = mysqli_fetch_assoc($querySegwit)){
	$avgSegWitArray[] = $insertSegwitAvg["segwitavg"];
}


?>
<!DOCTYPE html>
<html>
<head>
<title>Bitcoin Lightning Network Data</title>
<style>
html, body{
    font-family: "Arial", sans-serif;
}
h1 {
    color: purple;
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
    width: 100%;
    text-align: center;
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
</style>
    <script src="https://code.highcharts.com/highcharts.src.js"></script>
    <script src="https://code.highcharts.com/modules/series-label.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
</head>
<body>
<center>
   
<br>
<img src="logo.png" width="250">
</center>
<br>
<br>
<div id="container">

    <div class="lightning-stats-after">
        <?php echo $numberOfNodes; ?>
        <br>
        <span class="statsDescription"># NODES</span>
    </div>
    <div class="lightning-stats-after">
        <?php echo $numberOfChannels; ?>
        <br>
        <span class="statsDescription"># CHANNELS</span>
    </div>
    <div class="lightning-stats-after">
        <?php echo $numberOfBlocks; ?>
        <br>
        <span class="statsDescription">BLOCKHEIGHT</span>
    </div>
    
</div>

<br>
<br>

    <center>
        <p>Every 15 minutes we take a snapshot of the network and log the results below.</p>
<table>
<tr>
    <td class="tdheader">Blockheight</td>
    <td class="tdheader"># Inputs</td>
    <td class="tdheader">Segwit Inputs</td>
    <td class="tdheader">Segwit %</td>
    <td class="tdheader">Lightning Nodes</td>
    <td class="tdheader">Lightning Channels</td>
    <td class="tdheader">Max Channels Funded This Block*</td>
    <!-- <td class="tdheader">Total Fundings This Block</td> -->
    
</tr>
<?php
    
while($doDatabase = mysqli_fetch_assoc($queryDatabase)){
    echo "<tr class='trdata'><td>";
    echo $doDatabase["blockheight"];
    echo "</td><td>";
    echo $doDatabase["totsinputs"];
    echo "</td><td>";
     echo $doDatabase["segwitinputs"];
    echo "</td><td>";
     echo $doDatabase["segwit"];
    echo "%</td><td>";
     echo $doDatabase["lightningnodes"];
    echo "</td><td>";
     echo $doDatabase["lightningchannels"];
    echo "</td><td>";
     echo $doDatabase["lightningfunds"];
    //echo "</td><td>";
    //$convertValue = $doDatabase["lightningvalue"] / 1000000000;
    //$convertValue = number_format($convertValue, 9);
     //echo $convertValue;
    echo "</td></tr>";
}
?>
</table>
      <br>
        Historical: <a href="https://shabang.io/stats.json">https://shabang.io/stats.json</a>  </center>
      <br>
<div class="body">
    <h2>Network Data Collection</h2>
    <div id="chartCont"></div>
        <br>
        <br>
        <div id="chartContSegwit"></div>
        <br>
        <br>
        <div id="chartContChannels"></div>
        <br>
        <br>
        <div id="chartContFundings"></div>
    <br>
    <br>
    <h2>Bitcoin Lightning Network Stats JSON Endpoints</h2>
    <br>
    <span class="cexample"><a href="https://shabang.io/nodes.json">https://shabang.io/nodes.json</a></span>
    <br><br>
    <span class="cexample"><a href="https://shabang.io/channels.json">https://shabang.io/channels.json</a></span>
    <br><br>
    <span class="cexample"><a href="https://shabang.io/stats.json">https://shabang.io/stats.json</a></span>
    <br>
    <br>
     <h3>Most Well Connected Lightning Nodes</h3>
    
        <center>
            <table>
            <tr>
                <td class="tdheader">Rank</td>
                <td class="tdheader">Node ID</td>
            </tr>
            
            <?php 
            for($in=0;$in<100;$in++){
                $sequence = $in +1;
                echo "<tr class='trdata'><td>";
                echo $sequence."</td><td>";
                echo $popular[$in];
                echo "</td></tr>";
            }
            ?>
            </table>
        </center>
        <br>
        <br>
        <h4>How are these numbers calculated?</h4>
        <b>Segwit Inputs</b> - Transaction inputs that contain witness data.
        <br>
        <b>Lightning Nodes</b> - The number of nodes connected to this node (running clightning).
        <br>
        <b>Lightning Channels</b> - The number of channels returned from the listchannels command.
        <br>
        <b>Max Channels Funded This Block</b> - This is a count of number of bech32 P2WSH outputs. Lightning channels use bech32 pay to script hash addresses (P2WSH), not all P2WSH outputs are lightning fundings. 
        <br>
        <br>
        <center>Donate: 3LnBzPmb3BkDUZBHLHdEj5vgxS6D6HjKLW</center>
    
        <script>        
        Highcharts.chart('chartCont', {

        title: {
            text: 'Mainnet Lightning Nodes'
        },

        subtitle: {
            text: ''
        },

        yAxis: {
            title: {
                text: 'Nodes'
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
                pointStart: 1
            }
        },

        series: [{
            name: 'Lightning Nodes',
            data: [<?php echo join($numLightningNodesArr, ', '); ?>]
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

        });
        
        Highcharts.chart('chartContSegwit', {

        title: {
            text: 'Segwit % 100 Block Simple Moving Averaage'
        },

        subtitle: {
            text: ''
        },

        yAxis: {
            title: {
                text: 'Segwit Transactions'
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
                pointStart: 1
            }
        },

        series: [{
            name: 'Segwit Transaction %',
            data: [<?php echo join($avgSegWitArray, ', '); ?>]
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

        });
        
        Highcharts.chart('chartContChannels', {

        title: {
            text: 'Mainnet Lightning Channels'
        },

        subtitle: {
            text: ''
        },

        yAxis: {
            title: {
                text: 'Channels'
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
                pointStart: 1
            }
        },

        series: [{
            name: 'Lightning Channels',
            data: [<?php echo join($lightningChannelsHist, ', '); ?>]
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

        });
        
        Highcharts.chart('chartContFundings', {

        title: {
            text: 'Mainnet Lightning Channels Funded'
        },

        subtitle: {
            text: 'Per Block'
        },

        yAxis: {
            title: {
                text: 'Fundings Per Block'
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
                pointStart: 1
            }
        },

        series: [{
            name: 'Channels Funded',
            data: [<?php echo join($channelsFundedHist, ', '); ?>]
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

        });
        
    </script>
</div>

</body>
</html>