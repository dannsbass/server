<?php
foreach(glob(__DIR__.'/classes/*.php') as $file){
    if(strpos($file, 'autoload.php') !== false) continue;
    require $file;
}