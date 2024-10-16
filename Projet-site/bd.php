<?php
function getBD(){
$bdd = new PDO('mysql:host=localhost;dbname=modaliza;charset=utf8', 'root', 'root');
return $bdd;
}
?>