<?php
/* wpięcie do bazy */
$conn = new mysqli("localhost", "root", "", "szszsz");
if($conn->connect_error) echo "Nie można połączyć się z bazą: ".$conn->connect_error;
$conn->set_charset("utf8");

if(isset($_POST["s_sub"])){
  foreach($_POST as $key => $val){
    if(in_array($key, ["s_sub", "s_id"])) continue;
    $_POST[$key] = ($val == "") ? "null" :
      "\"".str_replace("\"", "\\\"", str_replace("\\", "\\\\", str_replace("/(\".{1-3})o(.?\")/", "$1°$2", $val)))."\"";
  }
  $q = "UPDATE części_stałe SET
      kolor = $_POST[s_kolor],
      part = $_POST[s_part],
      nuty = $_POST[s_nuty]
      WHERE id LIKE $_POST[s_id]";
  $r = $conn->query($q) or die($conn->error);
  $heraldic_text = "Część stała zmodyfikowana";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <link rel=stylesheet type='text/css' href='style.css'>
  <title>Edytor części stałych | SzSzSz</title>
  <script src="offlinecore/jquery-3.6.1.min.js"></script>
  <script src="offlinecore/abcjs-basic-min.js"></script>
  <script>
<?php
/* zebranie pieśni */
$q = "SELECT * FROM części_stałe";
$r = $conn->query($q) or die($conn->error);
while($a = $r->fetch_assoc()){
  $czst[$a['kolor']][$a['part']] = ["id" => $a['id'], "nuty" => $a['nuty']];
}
$r->free_result();
?>
const czst = <?php echo json_encode($czst); ?>;

$(document).ready(function(){
  /* auto-hide h2 */
  setTimeout(() => {
    $("h2").hide();
  }, 2000);
  /* wypełnianie pól danymi */
  function data_fill(){
    let color = $("select[name=\"s_kolor\"]").val();
    let part = $("select[name=\"s_part\"]").val();
    $("input[name=\"s_id\"]").val(czst[color][part]["id"]);
    $("textarea[name=\"s_nuty\"]").text(czst[color][part]["nuty"]);
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

  $("select[name=\"s_part\"], select[name=\"s_kolor\"]").change(function(){
    data_fill();
  });
  /* aktualizacja nut */
  $("select[name=\"s_part\"], select[name=\"s_kolor\"], textarea[name=\"s_nuty\"]").keyup(() => {notes_render()});
  /* od razu wyświetl po submicie */
  <?php if(isset($heraldic_text)): ?>
  $("select[name=\"s_kolor\"]").val(<?= $_POST['s_kolor'] ?>);
  $("select[name=\"s_part\"]").val(<?= $_POST['s_part'] ?>);
  data_fill(); notes_render();
  <?php endif; ?>
});
  </script>
</head>
<body>
  <form method="post">
    <h1>Edycja części stałych</h1>
    <?php if(isset($heraldic_text)) echo "<h2>$heraldic_text</h2>"; ?>
    <div class="framed">
    <?php
    function s_detail_field(string $display, string $name){
      echo "<div class='a_cell'>";
      echo "<p>$display</p>";
      echo (in_array($name, ["s_nuty"])) ?
        "<textarea style='width: 70vw' name=\"$name\"></textarea>" :
        "<input type=\"text\" name=\"$name\" />";
      echo "</div>";
    }
    function s_detail_list(string $display, string $name, array $options){
      echo "<div class='a_cell'>";
      echo "<p>$display</p>";
      echo "<select name=\"$name\">";
      foreach($options as $label){
        echo ($display == "Kolor") ? 
          "<option style=\"background: $label\" value=\"$label\">$label</option>" : 
          "<option value=\"$label\">$label</option>";
      }
      echo "</select>";
      echo "</div>";
    }
    ?>
      <div class="a_container">
      <?php
      s_detail_list("Kolor", "s_kolor", ['green', 'white', 'purple', 'red', 'blue', 'gold']);
      s_detail_list("Część", "s_part", ["kyrie", "gloria", "psalm", "aklamacja", "sanctus", "agnusdei"]);
      ?>
      </div>
      <div class="a_container">
      <?php
      s_detail_field("Nuty", "s_nuty");
      ?>
      </div>
      <div class="a_cell" id="nuty-preview"></div>
    </div>
    <div class="a_cell">
      <input type="hidden" name="s_id" value=0 />
      <input type="submit" name="s_sub" value="Edytuj" />
    </div>
  </form>
  <h3><a href="index.php">Wróć</a></h3>
</body>
</html>