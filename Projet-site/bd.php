<?php
function getBD(){
$bdd = new PDO('mysql:host=localhost;dbname=jobonheur;charset=utf8', 'root', 'root');
return $bdd;
}
?>