<?php 

function pre($data,$exit = 0){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    if(!empty($exit)){
        die ;
    }
}


