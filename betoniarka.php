<script type="text/javascript">
var piesni = new Array();
<?php
global $piesni;
$conn = new mysqli("localhost", "root", "", "szszsz");
if($conn->connect_error) echo "Nie można połączyć się z bazą: ".$conn->connect_error;
$conn->set_charset("utf8");

$q = "SELECT * FROM pieśni";
$r = $conn->query($q) or die($q.$conn->error);
while($a = $r->fetch_assoc()){?>
piesni["<?php echo $a['tytuł']; ?>"] = new Array('klasa', 'katsiedlecki', 'nr', 'tonacja', 'naco', 'tekst');
<?php
foreach($a as $name => $value){
	if(!in_array($name, ['tytuł', 'id'])){ ?>
piesni["<?php echo $a['tytuł']; ?>"]['<?php echo $name; ?>'] = '<?php echo $a[$name]; ?>';
<?php
$piesni[$a['tytuł']][$name] = $a[$name];
}}}
$r->free_result;
?>
</script>