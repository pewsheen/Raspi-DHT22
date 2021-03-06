<?php

	// Current data
	// Read history data
	$file = fopen('docs/history.txt', 'r');

	$table = array();

	$table['cols'] = array(
			array('label' => 'date', 'type' => 'string'),
			array('label' => 'humidity', 'type' => 'number')
		);

	for ($i = 0; $i < 10; $i++){
		$data = fscanf($file, "%s %s %f %f\n");
		list($cell_date, $cell_time, $cell_temp, $cell_hud) = $data;
		$cell_date = $cell_date." ".$cell_time;

		if ($i >= 5) {
			$table['rows'][] = array(
				'c' => array(
						array('v' => $cell_date),
						array('v' => $cell_hud)
				)
			);
		}
	}

	echo json_encode($table);

	fclose($file);

?>