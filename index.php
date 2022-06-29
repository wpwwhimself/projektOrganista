<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta charset="UTF-8">
	<link rel=stylesheet type='text/css' href='style.css?'>
</head>
<body>
	<form method="get">
		<h1>Podstawowe</h1>
		<div>
			<h3>Formuła mszy</h3>
			<select name="a_formula">
			<?php
			foreach(['zwykła', 'zwykła wielkanocna', 'Adwent', 'Boże Narodzenie', 'Wielki Post', 'Wielkanoc'] as $x){
				echo "<option value='$x'>$x</option>";
			}
			?>
			</select>
			<h3>Identyfikator mszy</h3>
			<input type="text" name="a_identyfikator">

			<h3>Kolor części stałych</h3>
			<div class='a_container'>
			<?php
			foreach([
				'zielony' => 'green',
				'biały' => 'white',
				'purpurowy' => 'purple',
				'czerwony (Piasecki)' => 'red',
				'niebieski (Pawlak)' => 'blue'
				] as $label => $code){
				echo "<input type='radio' name='a_czescistale' id='a_czescistale_$code' value='$code'";
					if($label == "zielony") echo " checked";
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
		$q = "SELECT tytuł 
					FROM pieśni
					ORDER BY klasa, tytuł";
		$r = $conn->query($q) or die($conn->error);
		while($a = $r->fetch_array()){
				$titles[] = $a['tytuł'];
		}
		$r->free_result();

		function a_piesn($etykieta, $kod, $przeklejane = false){
			global $titles;
			echo "<div class='a_cell'>";
			echo "<p><b>$etykieta</b></p>";
			if($przeklejane){
				echo "<textarea name='a_$kod'></textarea>";
			}else{
				echo "<select name='a_$kod'>";
					echo "<option value='' />";
				foreach($titles as $value){
					echo "<option value='$value'>$value</option>";
				}
				echo "</select>";
			}
			echo "</div>";
		}
		?>
		<div class="framed">
			<h2>Esencja</h2>
			<div class='a_container'>
				<?php
				a_piesn("Wejście", "piesn_wejscie");
				a_piesn("Przygotowanie darów", "piesn_dary");
				a_piesn("Komunia 1", "piesn_komunia1");
				a_piesn("Komunia 2", "piesn_komunia2");
				a_piesn("Komunia 3", "piesn_komunia3");
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
				a_piesn("Przed mszą", "pre");
				a_piesn("Przed gloria", "piesn_przedgloria");
				?>
			</div>

			<input type="checkbox" id="savehistory" name="savehistory"></input>
			<label for="savehistory">Zapisz do historii</label>
		</div>
			
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
					<td><?php echo $history['a_piesn_wejscie']; ?></td>
					<td><?php echo $history['a_piesn_dary']; ?></td>
					<td><?php echo $history['a_piesn_komunia1']; ?></td>
					<td><?php echo $history['a_piesn_komunia2']; ?></td>
					<td><?php echo $history['a_piesn_komunia3']; ?></td>
					<td><?php echo $history['a_piesn_uwielbienie']; ?></td>
					<td><?php echo $history['a_piesn_zakonczenie']; ?></td>
				</tr>
			</table>
		</div>

		<div>
			<script>
				function formshow() {
				document.forms[0].action = 'out.php';
			}
			function formproc() {
				document.forms[0].action = 'process.php';
			}
			</script>
			<input type="submit" value="Wyświetl" onclick="formshow();return true;">
			<input type="submit" value="Procesuj" onclick="formproc();return true;">
		</div>
	</form>
</body>
</html>