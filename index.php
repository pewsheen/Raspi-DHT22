<?php

	// Current data
	$file = fopen('docs/temp.txt', 'r');

	$data = fscanf($file, "%s %s %f %f\n");
	list($current_date, $current_time, $current_temp, $current_hud) = $data;
	$current_date = $current_date." ".$current_time;

	fclose($file);
?>

<!DOCTYPE html>
<html class="uk-height-1-1">
	<head>
		<title> 阿寶去冰斗工作室 Meter</title>
		<meta charset='utf-8'> 
		<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
		<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script src="js/uikit.min.js"></script>

		<link rel="stylesheet" href="css/normalize.css" />
		<link rel="stylesheet" href="css/uikit.min.css" />

		<!-- ============ AJAX ============ -->
		<script>

			var jsonTemp;
			var jsonHud;

			function sleep(milliseconds) {
				var start = new Date().getTime();
				
				for (var i = 0; i < 1e7; i++) {
					if ((new Date().getTime() - start) > milliseconds){
						break;
					}
				}
			}

			function getCurrentTemp() {

				var xmlhttp;
				var temp;
				var date;
				var hud;

				// Spin icon
				var icon = document.getElementById("refresh");
				icon.className = icon.className + " uk-icon-spin";

				if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
 					xmlhttp = new XMLHttpRequest();
 					historyhttp = new XMLHttpRequest();
				}


				// Current
				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState==4 && xmlhttp.status==200) {

						// Current data
						temp = xmlhttp.responseXML.documentElement.getElementsByTagName("TEMP");
						hud = xmlhttp.responseXML.documentElement.getElementsByTagName("HUD");
						date = xmlhttp.responseXML.documentElement.getElementsByTagName("DATE");

						temp = temp[0].firstChild.nodeValue;
						hud = hud[0].firstChild.nodeValue;
						date = date[0].firstChild.nodeValue;

						jsonTemp = $.ajax({
							url: "getTempData.php",
							dataType:"json",
							async: false
						}).responseText;

						jsonHud = $.ajax({
							url: "getHudData.php",
							dataType:"json",
							async: false
						}).responseText;

						sleep(1000);

						document.getElementById("temp").innerHTML=temp+" *C";
						document.getElementById("date").innerHTML=date;
						document.getElementById("hud").innerHTML=hud+" %";

						drawChart();

						icon.className = "uk-icon-refresh uk-icon-large";
					}
				}

				xmlhttp.open("GET","xml/temp.xml",true);
				xmlhttp.send();
			}
		</script>


	    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
	    <script type="text/javascript">

			jsonTemp = $.ajax({
				url: "getTempData.php",
				dataType:"json",
				async: false
			}).responseText;

			jsonHud = $.ajax({
				url: "getHudData.php",
				dataType:"json",
				async: false
			}).responseText;

	      google.load("visualization", "1", {packages:["corechart"]});
	      google.setOnLoadCallback(drawChart);

	      function drawChart() {

	      	// Draw Temperature
	        var data_temp = new google.visualization.DataTable(jsonTemp);

	        var options_temp = {
	        	pointSize: 5,
	        	legend: 'none'
	        };

	        var chart_temp = new google.visualization.LineChart(document.getElementById('chart_temp'));
	        chart_temp.draw(data_temp, options_temp);

	        // Draw Humidity
	        var data_hud = new google.visualization.DataTable(jsonHud);

	        var options_hud = {
	        	pointSize: 5,
	        	legend: 'none',
	          	colors: ['#e0440e']
	        };

	        var chart_hud = new google.visualization.LineChart(document.getElementById('chart_hud'));
	        chart_hud.draw(data_hud, options_hud);
	        
	      }
	    </script>


	</head>
	<body class="uk-height-1-1">

		<div class="uk-container uk-width-medium-2-3 uk-container-center uk-text-center" style="padding-top:30px;padding-bottom:30px;">
			<div class="uk-grid">
				<div class="uk-width-medium-1-1">
					<div class="uk-panel uk-panel-box">
						<h3 class="uk-panel-title"><i class="uk-icon-location-arrow"></i> Location</h3>
						<div class="uk-align-center"><h1>Taipei home</h1></div>
					</div>
				</div>
			</div>
			<div class="uk-grid">
				<div class="uk-width-medium-1-2">
					<div class="uk-panel uk-panel-box">
						<h3 class="uk-panel-title"><i class="uk-icon-sun"></i> Temperature</h3>
						<div class="uk-align-center"><h1 id="temp">
	<?php
		echo $current_temp." *C";
	?>
						</h1></div>
					</div>
				</div>
				<div class="uk-width-medium-1-2">
					<div class="uk-panel uk-panel-box">
						<h3 class="uk-panel-title"><i class="uk-icon-tint"></i> Humidity</h3>
						<div class="uk-align-center"><h1 id="hud">
	<?php
		echo $current_hud." %";
	?>
						</h1></div>
					</div>
				</div>
			</div>
			<div class="uk-grid">
				<div class="uk-width-medium-1-2">
					<div class="uk-panel uk-panel-box" style="height:90px;">
						<h3 class="uk-panel-title"><i class="uk-icon-refresh"></i> Last update</h3>
						<div class="uk-align-center"><h2 id="date">
		<?php
			echo $current_date;
		?>
						</h2></div>
					</div>
				</div>
				<div class="uk-width-medium-1-2">
					<div class="uk-panel uk-panel-box" style="height:90px;">
						<a style="font-size:35px;" id="refresh" class="uk-icon-refresh uk-icon-large" onclick="getCurrentTemp()"></a>
					</div>
				</div>
			</div>
			<div class="uk-grid">
				<div class="uk-width-medium-1-1">
					<div class="uk-panel uk-panel-box">
						<h3 class="uk-panel-title"><i class="uk-icon-bar-chart"></i> Temperature<small> (*C)</small> history </br></h3>
						<div id="chart_temp" ></div>
					</div>
				</div>
			</div>
			<div class="uk-grid">
				<div class="uk-width-medium-1-1">
					<div class="uk-panel uk-panel-box">
						<h3 class="uk-panel-title"><i class="uk-icon-bar-chart"></i> Humidity<small> (%)</small> history </br></h3>
						<div id="chart_hud" ></div>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>
