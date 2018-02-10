<?php
/**
 * Created by PhpStorm.
 * User: Poney
 * Date: 01/02/2018
 * Time: 16:44
 */

$tofeed = array(
    "foo" => "bar",
    "bar" => "foo",
    "clef1" => "val1",
    "clef2" => "val2",
    "clef3" => "val3"
);

$prefix="adh_";

feedTable($tofeed,"adh_");

function feedTable(array $tofeed, $prefix=''){
    //TODO : remplacer adh par le $prefix




    $keys = "(`$prefix".implode("`,`$prefix", array_keys($tofeed))."`)";
    $values = "('".implode("','", array_values($tofeed))."')";

    echo ($keys);
    echo ($values);

    return $keys."<br/>".$values;
}
?>


//(`adh_foo`,`adh_bar`,`adh_clef1`,`adh_clef2`,`adh_clef3`)('bar','foo','val1','val2','val3')

