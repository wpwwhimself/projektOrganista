<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta charset="UTF-8">
	<link rel=stylesheet type='text/css' href='style.css'>
	<title>Śpiewnik Szybkiego Szukania</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script>
		$(document).ready(function(){
			$(".songchoose").change(function(){
				let id = $(this).attr("id");
				const ids = [
					"piesn_wejscie",
					"piesn_dary",
					"piesn_komunia1",
					"piesn_komunia2",
					"piesn_komunia3",
					"piesn_uwielbienie",
					"piesn_zakonczenie"
				]
				for(id of ids){
					$("h_"+id).css("color", ($(this).val() == $("#h_"+id).text()) ? "red" : "inherit");
				}
			})
		});
	</script>
</head>
<body>
	<form method="get">
		<h1>Podstawowe</h1>
		<div>
			<h3>Formuła mszy</h3>
			<select name="a_formula" id="a_formula">
			<?php
			foreach(
				[
					'zwykła', 'zwykła wielkanocna', 'ślubna', 'pogrzebowa', 'majowe', 'czerwcowe',
					'Adwent', 'Boże Narodzenie', 'Wielki Post', 'Wielkanoc'
				] as $x){
				echo "<option value='$x'";
				if(isset($_GET["a_formula"]) && $_GET["a_formula"] === $x) echo " selected";
				echo ">$x</option>";
			}
			?>
			</select>
			<h3>Identyfikator mszy</h3>
			<input type="text" name="a_identyfikator" id="a_identyfikator" required <?php if(isset($_GET["a_identyfikator"])) echo "value='$_GET[a_identyfikator]'"; ?>>

			<h3>Kolor części stałych</h3>
			<div class='a_container'>
			<?php
			foreach([
				'zielony' => 'green',
				'biały' => 'white',
				'purpurowy' => 'purple',
				'czerwony (Piasecki)' => 'red',
				'niebieski (Pawlak)' => 'blue',
				'złoty (Machura)' => 'gold'
				] as $label => $code){
				echo "<input type='radio' name='a_czescistale' id='a_czescistale_$code' value='$code'";
					if($label == "zielony" || (isset($_GET["a_czescistale"]) && $_GET["a_czescistale"] === $code)) echo " checked";
				echo ">";
				echo "<label for='a_czescistale_$code'>$label</label>";
				}
			?>
			</div>
		</div>

		<h1>Pieśni</h1>
		<?php
		$conn = new mysqli("localhost", "root", "", "szszsz");
		if($conn->connect_error) echo "Nie można połączyć się z bazą: ".$conn->connect_error;
		$conn->set_charset("utf8");

		/*Zdobądź pieśni*/
		$q = "SELECT *
					FROM pieśni
					ORDER BY klasa, tytuł";
		$r = $conn->query($q) or die($conn->error);

		$naco_decode = ["Wejście", "Przygotowanie darów", "Komunia", "Uwielbienie", "Zakończenie"];

		while($a = $r->fetch_array()){
			$tmp = explode("/", $a['naco']);
			$naco = "";
			for($i = 0; $i < count($naco_decode); $i++){
				$naco .= ($tmp[$i]) ? $naco_decode[$i]."," : "";
			}
			$songs[] = ["tytuł" => $a["tytuł"], "naco" => $naco];
		}
		$r->free_result();

		function a_piesn($etykieta, $kod, $przeklejane = false, $nietypowe = false){
			global $songs;
			echo "<div class='a_cell'>";
			echo "<p><b>$etykieta</b></p>";
			if($przeklejane){
				echo "<textarea id='a_$kod' name='a_$kod'>";
				if(isset($_GET["a_$kod"])) echo $_GET["a_$kod"];
				echo "</textarea>";
			}else{
				echo "<select class='songchoose' id='a_$kod' name='a_$kod'>";
					echo "<option value='' />";
				foreach($songs as $x){
					$etykieta = preg_replace("/(.*)\s\d/", "$1", $etykieta);
					if(preg_match("/$etykieta/", $x['naco']) || $nietypowe){
						echo "<option value='$x[tytuł]'";
						if(isset($_GET["a_$kod"]) && $_GET["a_$kod"] === $x["tytuł"]) echo " selected";
						echo ">$x[tytuł]</option>";
					}
				}
				echo "</select>";
			}
			echo "</div>";
		}
		?>

		<div class="framed">
			<h2>Co było ostatnio?</h2>
			<?php $history = json_decode(file_get_contents("songhistory.json"), true); ?>
			<table id="songhistory">
				<tr>
					<th>Wejście</th>
					<th>Przygotowanie darów</th>
					<th colspan=3>Komunia</th>
					<th>Uwielbienie</th>
					<th>Zakończenie</th>
				</tr>
				<tr>
					<?php
						function history($id){
							global $history;
							echo "<td id='h_$id'>".$history["a_".$id]."</td>";
						}
						history("piesn_wejscie");
						history("piesn_dary");
						history("piesn_komunia1");
						history("piesn_komunia2");
						history("piesn_komunia3");
						history("piesn_uwielbienie");
						history("piesn_zakonczenie");
					?>
				</tr>
			</table>
			<div class="a_container">
				<!-- <input type="submit" name="sub" value="Wyświetl ostatni" onclick="formshow();return true;">
				<input type="submit" name="sub" value="Procesuj ostatni" onclick="formproc();return true;"> -->
				<input type="button" value="Skopiuj" onclick="copyhistory()" />
			</div>
		</div>
		
		<h3><a href="editor.php">Edycja pieśni</a></h3>

		<div class="framed">
			<h2>Esencja</h2>
			<div class='a_container'>
				<?php
				a_piesn("Wejście", "piesn_wejscie");
				a_piesn("Przygotowanie darów", "piesn_dary");
				?>
			</div>
			<div class="a_container">
				<?php
				a_piesn("Komunia 1", "piesn_komunia1");
				a_piesn("Komunia 2", "piesn_komunia2");
				a_piesn("Komunia 3", "piesn_komunia3");
				?>
			</div>
			<div class="a_container">
				<?php
				a_piesn("Uwielbienie", "piesn_uwielbienie");
				a_piesn("Zakończenie", "piesn_zakonczenie");
				?>
			</div>
			<h2>Psalmy</h2>
			<div class="a_container">
				<?php
				a_piesn("Psalm", "psalm", true);
				a_piesn("Aklamacja", "aklamacja", true);
				?>
			</div>
			<h2>Dodatkowe</h2>
			<div class='a_container'>
				<?php
				a_piesn("Przed mszą", "pre", false, true);
				a_piesn("Przed gloria", "piesn_przedgloria", false, true);
				?>
			</div>

			<input type="checkbox" id="savehistory" name="savehistory" checked></input>
			<label for="savehistory">Zapisz do historii</label>
		</div>
			
		<div>
			<script>
			const history = <?php include("songhistory.json"); ?>;

			function formshow() {
				document.forms[0].action = '_OUT/out.php';
			}
			function formproc() {
				document.forms[0].action = 'process.php';
			}
			function copyhistory() {
				for(name in history) {
					switch(name){
						case "savehistory":
						case "sub":
							continue;
						case "a_psalm":
						case "a_aklamacja":
							document.querySelector("#" + name).innerHTML = history[name];
							break;
						case "a_czescistale":
							document.querySelector("#" + name + "_" + history[name]).checked = true;
							break;
						default:
							document.querySelector("#" + name).value = history[name];
							break;
					}
				}
				document.querySelector("#savehistory").checked = false;
			}

			</script>
			<div class="a_container">
				<input type="submit" name="sub" value="Wyświetl" onclick="formshow();return true;">
				<input type="submit" name="sub" value="Procesuj" onclick="formproc();return true;">
			</div>
		</div>
	</form>
</body>
</html>