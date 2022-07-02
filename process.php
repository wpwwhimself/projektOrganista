<?php
/* zapisz poprzednie */
if(isset($_GET['savehistory'])){
	file_put_contents('songhistory.json', json_encode($_GET, JSON_PRETTY_PRINT));
}

//funkcja do zapisywania pliku
$getparams = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "?")+1);
$url = "http://$_SERVER[HTTP_HOST]/projektOrganista/out.php?$getparams";
$lines = file_get_contents($url);
$file = fopen("./_OUT/".$_GET['a_formula'].".html", "w");
fwrite($file, $lines);
fclose($file);
?>
<h2>Plik gotowy!</h2>