<?php
/* zapisz poprzednie */
if(isset($_GET['savehistory'])){
	file_put_contents('songhistory.json', json_encode($_GET, JSON_PRETTY_PRINT));
}

//funkcja do zapisywania pliku
$getparams = substr($_SERVER["REQUEST_URI"], strpos($_SERVER["REQUEST_URI"], "?")+1);
$url = "http://$_SERVER[HTTP_HOST]/projektOrganista/_OUT/out.php?$getparams";
$lines = file_get_contents($url);
$filename = ($_GET['a_identyfikator'] == "") ? $_GET['a_formula'] : $_GET['a_identyfikator'];
$file = fopen("./_OUT/$filename.html", "w");
fwrite($file, $lines);
fclose($file);
?>
<h2>Plik gotowy!</h2>
<a href="_OUT/<?php echo $filename; ?>.html" target="_blank">Otwórz</a>