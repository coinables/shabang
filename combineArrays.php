
<?php
//combine arrays zipper merge
$a = array(1,2,3,4);
$b = array(500,505,503,505);
$c = array();

$al = count($a);
for($i=0;$i<$al;$i++){
        $newArr = array();
        array_push($newArr, $a[$i], $b[$i]);
        array_push($c, $newArr);
}

$json = json_encode($c);
var_dump($json);

?>
