<?php
/* zapisz poprzednie */
if(isset($_GET['savehistory'])){
	file_put_contents('songhistory.json', json_encode($_GET, JSON_PRETTY_PRINT));
}

/* dodaj naco pieśniom, które go nie miały */
$songs_to_update = []; $nacos_to_update = [];
$nacos_target = [
    "a_piesn_wejscie" =>     "/^()0(\/.\/.\/.\/.)/",
    "a_piesn_dary" =>        "/^(.\/)0(\/.\/.\/.)/",
    "a_piesn_komunia1" =>    "/^(.\/.\/)0(\/.\/.)/",
    "a_piesn_komunia2" =>    "/^(.\/.\/)0(\/.\/.)/",
    "a_piesn_komunia3" =>    "/^(.\/.\/)0(\/.\/.)/",
    "a_piesn_uwielbienie" => "/^(.\/.\/.\/)0(\/.)/",
	"a_piesn_zakonczenie" => "/^(.\/.\/.\/.\/)0()/"
];
foreach($nacos_target as $key => $val){
	if(!isset($_GET[$val])) continue;
	$songs_to_update[$key] = $_GET[$key];
}
$q = "SELECT tytuł, naco FROM pieśni WHERE tytuł IN (".implode(", ", $songs_to_update).")";
$r = $conn->query($q) or die($conn->error);
while($a = $r->fetch_assoc()){ $nacos_to_update[$a['tytuł']] = $a['naco']; }
$r->free_result();
foreach($songs_to_update as $position => $title){
	$q = "UPDATE pieśni SET naco = \"".str_replace($nacos_target[$position], "$11$2", $nacos_to_update[$title])."\" WHERE tytuł = \"$title\"";
	$r = $conn->query($q) or die($conn->error);
	$r->free_result();
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