<?php
/* wpięcie do bazy */
$conn = new mysqli("localhost", "root", "", "szszsz");
if($conn->connect_error) echo "Nie można połączyć się z bazą: ".$conn->connect_error;
$conn->set_charset("utf8");

if(isset($_POST["s_sub"])){
  foreach($_POST as $key => $val){
    if(in_array($key, ["s_sub", "s_klasa"])) continue;
    $_POST[$key] = ($val == "") ? "null" :
      "\"".
      str_replace("\"", "\\\"", 
      str_replace("\\", "\\\\", 
      $val)).
      "\"";
  }
  $flag_edit = ($_POST["s_sub"] == "Edytuj");

  $q = ($flag_edit) ?
    "UPDATE pieśni SET
      klasa = $_POST[s_klasa],
      katsiedlecki = $_POST[s_katsiedlecki],
      nr = $_POST[s_nr],
      tonacja = $_POST[s_tonacja],
      naco = $_POST[s_naco],
      tekst = $_POST[s_tekst],
      nuty = $_POST[s_nuty]
      WHERE tytuł LIKE $_POST[s_tytul]" : 
    "INSERT INTO pieśni VALUES
      ($_POST[s_tytul], $_POST[s_klasa], $_POST[s_katsiedlecki], $_POST[s_nr], $_POST[s_tonacja], $_POST[s_naco], $_POST[s_tekst], $_POST[s_nuty])";
  $r = $conn->query($q) or die($conn->error);
  $heraldic_text = ($flag_edit) ? "Pieśń zmodyfikowana" : "Pieśń dodana";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <link rel=stylesheet type='text/css' href='style.css'>
  <style>
	@font-face{
		font-family: "Krona One";
		src: url("offlinecore/KronaOne-Regular.ttf");
	}
	@font-face{
		font-family: "Raleway";
		src: url("offlinecore/Raleway-Regular.ttf");
	}
	</style>
  <title>Edytor pieśni | SzSzSz</title>

  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
  <script src="offlinecore/jquery-3.6.1.min.js"></script>
  <script src="offlinecore/abcjs-basic-min.js"></script>
  <script>
<?php
/* zebranie pieśni */
$q = "SELECT * FROM pieśni";
$r = $conn->query($q) or die($conn->error);
while($a = $r->fetch_assoc()){
  $songs[$a["tytuł"]] = $a;
}
$r->free_result();
/* zebranie kategorii */
$q = "SELECT * FROM kategorie";
$r = $conn->query($q) or die($conn->error);
while($a = $r->fetch_assoc()){
  $categories[$a["id"]] = $a["kategoria"];
}
$r->free_result();
?>
const songs = <?php echo json_encode($songs); ?>;

$(document).ready(function(){
  /* auto-hide h2 */
    setTimeout(() => {
    $("h2").hide();
  }, 2000);
  /* wypełnianie pól danymi */
  function data_fill(title){
    $("input[name=\"s_tytul\"]").val(title);
    $("select[name=\"s_klasa\"]").val(songs[title].klasa);
    for(detail of ["katsiedlecki", "nr", "tonacja", "naco"]){
      $(`input[name=\"s_${detail}\"]`).val(songs[title][detail]);
    }
    $("textarea[name=\"s_tekst\"]").text(songs[title].tekst);
    $("textarea[name=\"s_nuty\"]").text(songs[title].nuty);
    $("input[type=\"submit\"]").attr("value", "Edytuj");
    notes_render();
  }
  function notes_render(){
    ABCJS.renderAbc(
      "nuty-preview",
      $("textarea[name=\"s_nuty\"]").val(),
      { responsive: "resize" }
    );
  }

  $("select[name=\"s_name\"]").change(x => {data_fill(x.target.value)});
  /* nadzorowanie edycji kontra dodawaniu */
  $("input[name=\"s_tytul\"]").change(function(){
    $("input[type=\"submit\"]").attr("value", (Object.keys(songs).includes($(this).val())) ? "Edytuj" : "Dodaj");
  });
  /* aktualizacja nut */
  $("textarea[name=\"s_nuty\"], select[name=\"s_name\"]").keyup(() => {notes_render()});
  /* od razu wyświetl po submicie */
  <?php if(isset($heraldic_text)): ?>
  $("select[name=\"s_name\"]").val(<?= $_POST['s_tytul'] ?>);
  data_fill(<?= $_POST['s_tytul'] ?>); notes_render();
  <?php endif; ?>
});
  </script>
</head>
<body>
  <form method="post">
    <h1>Edycja pieśni</h1>
    <?php if(isset($heraldic_text)) echo "<h2>$heraldic_text</h2>"; ?>
    <div class="a_cell">
      <select name="s_name" autofocus>
        <option value="">--wybierz pieśń albo zacznij pisać--</option>
      <?php
      foreach($songs as $title => $details){
        echo "<option value=\"$title\">$title</option>";
      }
      ?>
      </select>
    </div>
  
    <div class="framed">
    <?php
    function s_detail_field(string $display, string $name){
      echo "<div class='a_cell'>";
      echo "<p>$display</p>";
      echo (in_array($name, ["s_tekst", "s_nuty"])) ?
        "<textarea name=\"$name\"></textarea>" :
        "<input type=\"text\" name=\"$name\" />";
      echo "</div>";
    }
    function s_detail_list(string $display, string $name, array $options){
      echo "<div class='a_cell'>";
      echo "<p>$display</p>";
      echo "<select name=\"$name\">";
      foreach($options as $id => $label){
        echo "<option value=\"$id\">$label</option>";
      }
      echo "</select>";
      echo "</div>";
    }
    ?>
      <div class="a_container">
      <?php
      s_detail_field("Tytuł", "s_tytul");
      s_detail_list("Klasa", "s_klasa", $categories);
      ?>
      </div>
      <div class="a_container">
      <?php
      s_detail_field("Kategoria w Siedleckim", "s_katsiedlecki");
      s_detail_field("Numer w wyświetlaczu", "s_nr");
      s_detail_field("Tonacja", "s_tonacja");
      s_detail_field("Na co (W/PD/K/U/Z/uwagi)", "s_naco");
      ?>
      </div>
      <div class="a_container">
      <?php
      s_detail_field("Tekst", "s_tekst");
      s_detail_field("Nuty", "s_nuty");
      ?>
      </div>
      <div class="a_cell" id="nuty-preview"></div>
    </div>
    <div class="a_cell">
      <input type="submit" name="s_sub" value="Edytuj" />
    </div>
  </form>
  <h3><a href="index.php">Wróć</a></h3>
</body>
</html>