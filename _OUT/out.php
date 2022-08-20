<?php
/* zapisz poprzednie */
if(isset($_GET['savehistory'])){
	file_put_contents('../songhistory.json', json_encode($_GET, JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script> -->
		<script><?php require("../offlinecore/react.development.js"); ?></script>
		<script><?php require("../offlinecore/react-dom.development.js"); ?></script>
		<script><?php require("../offlinecore/babel.min.js"); ?></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta charset="UTF-8">
		<style><?php require("../style.css"); ?></style>
	</head>
<body class='kontener'>
	<div id="main"></div>
	<div class="backstage-links">
		<a href="/projektOrganista/process.php?<?php echo parse_url($_SERVER["REQUEST_URI"])["query"]; ?>">
			<h3>Procesuj ponownie</h3>
		</a>
		<a href="/projektOrganista/?<?php echo parse_url($_SERVER["REQUEST_URI"])["query"]; ?>">
			<h3>Modyfikuj</h3>
		</a>
	</div>
</body>

<?php require("../modules.php"); ?>
</html>