<?php

$workspace_path = '/home/pi/wifi-sentinel';
$aerodump_file_name = 'dump';

// Get latest dump
$files1 = scandir($workspace_path);
$matches = array_values(array_filter($files1, function($var) use ($aerodump_file_name) { return preg_match("/\b$aerodump_file_name\b/i", $var); }));
$latest_file_name = $matches[count($matches)-1];

$aerodump_path = $workspace_path . '/' . $latest_file_name;
$address_book_path = $workspace_path .'/address_book.csv';


// The nested array to hold all the arrays
$aerodump = [];

// Open the file for reading
if (($h = fopen("{$aerodump_path}", "r")) !== FALSE) {
	// Each line in the file is converted into an individual array that we call $data
	// The items of the array are comma separated
	while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
		// Each individual array is being pushed into the nested array
		$aerodump[] = $data;
	}

	// Close the file
	fclose($h);
}


// Load address book
$address_book = [];

// Open the file for reading
if (($h = fopen("{$address_book_path}", "r")) !== FALSE) {
	// Each line in the file is converted into an individual array that we call $data
	// The items of the array are comma separated
	while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
		// Each individual array is being pushed into the nested array
		$address_book[] = $data;
	}

	// Close the file
	fclose($h);
}

// Keep only devices from aerodump
$arr = array_map(function ($element) {
	return $element[0];
}, $aerodump);
$key = array_search('Station MAC', $arr); // $key = 2;

$devices = array_slice($aerodump, $key + 1);
unset($devices[count($devices) - 1]);

// Add in hostnames
foreach ($devices as &$device) {
	$mac = $device[0];
	foreach ($address_book as &$entry) {
		$address_mac = $entry[1];
		$hostname = $entry[3];

		if ($mac == $address_mac) {
			$device[7] = $hostname;
			break;
		} else {
			$device[7] = 'Unknown';
		}
	}
}

// Sort by date
function sortFunction( $d1, $d2 ) {
	if($d1[2]==$d2[2]) return 0;
    return strtotime($d1[2]) < strtotime($d2[2]) ? 1 : -1;
}
usort($devices, "sortFunction");

// Display the code in a readable format
// echo "<pre>";
// var_dump($devices);
// echo "</pre>";

?>


<!DOCTYPE HTML>
<!--
	Stellar by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

<head>
	<title>Wifi Sentinel</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
	<link rel="stylesheet" href="assets/css/main.css" />
	<noscript>
		<link rel="stylesheet" href="assets/css/noscript.css" />
	</noscript>
	<link rel="shortcut icon" href="/images/security.svg" type="image/x-icon"/>

</head>

<body class="is-preload">

	<!-- Wrapper -->
	<div id="wrapper">

		<!-- Header -->
		<header id="header" class="alt">
			<span class="logo"><img src="images/security.svg" alt="" width="100" /></span>
			<h1>Wifi Sentinel</h1>
			<p>See who's been in your home<br /></p>
		</header>

		<!-- Nav -->
		<nav id="nav">
			<ul>
				<li><a href="#intro" class="active">Overview</a></li>
			</ul>
		</nav>

		<!-- Main -->
		<div id="main">

			<!-- Introduction -->
			<section id="intro" class="main">

				<div class="spotlight">
					<div class="content">
						<header class="major">
							<h2>Devices</h2>
						</header>

						<div class="table-wrapper">
							<table>
								<thead>
									<tr>
									<th>Safe</th>

										<th>Name</th>
										<th>MAC</th>
										<th>First Seen</th>
										<th>Last Seen</th>
										<th>Distance To Router</th>
									</tr>
								</thead>
								<tbody>


									<?php
									foreach ($devices as $d) {
										$known =   $d[7] != 'Unknown';

										$first_seen = new DateTime($d[1]);
										$last_seen = new DateTime($d[2]);

										echo "<tr>";
										if($known) {
											echo '<td style="vertical-align: middle;  "><i class="fas fa-check-circle" style="font-size: 2em; color:#42f551; "></i></td>';
										} else {
											echo '<td style="vertical-align: middle;  "><i class="fas fa-exclamation-triangle" style="font-size: 2em;  color:#ff6700; "></i></i></td>';

										}
										echo '<td style=" display: table-cell;vertical-align: middle;"><p style="display: block; margin: 0px; " >' . $d[7] . '</p></td>';
										echo '<td style=" display: table-cell;vertical-align: middle;"><p style="display: block; margin: 0px; " >' . $d[0] . '</p></td>';
										echo '<td style=" display: table-cell;vertical-align: middle;"><p style="display: block; margin: 0px; " >' . $first_seen->format('g:ia') . ' | '. $first_seen->format('jS M Y') . '</p></td>';
										echo '<td style=" display: table-cell;vertical-align: middle;"><p style="display: block; margin: 0px; " >' . $last_seen->format('g:ia') . ' | '. $last_seen->format('jS M Y') . '</p></td>';
										echo '<td style=" display: table-cell;vertical-align: middle;"><p style="display: block; margin: 0px; " >' . $d[3] . '</p></td>';
										echo '</tr>';
									}
							


									?>

								</tbody>
							</table>
							
							<a href="javascript:location.reload(true)" class="button primary fit">Refresh</a>

						</div>
					</div>
				</div>
			</section>

		</div>


	</div>
	<footer><br /><br /><br /></footer>

									
	<!-- Scripts -->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/jquery.scrollex.min.js"></script>
	<script src="assets/js/jquery.scrolly.min.js"></script>
	<script src="assets/js/browser.min.js"></script>
	<script src="assets/js/breakpoints.min.js"></script>
	<script src="assets/js/util.js"></script>
	<script src="assets/js/main.js"></script>

</body>

</html>