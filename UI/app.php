<?php
	include('session.php');
	
	require_once('file_functions.php');
	require_once('blockchain.php');
	require_once('blockchain_functions.php');

	loadBlockchain("blockchain.ini", $simbaBlockchain);
	
	$connection_status = get_ini_value("keywords", "connection_status");
	if($connection_status != 1)
	{		
		echo '<!DOCTYPE html>';
		echo '<html>';
		echo '<script language="javascript">';
		echo 'alert("Connection to Device - Failed")';
		echo '</script>';
		echo '</html>';
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>IoT Accident Recorder Using Blockchain</title>	
	<link rel="stylesheet" href="css/reset.min.css">
	<link rel="stylesheet" href="css/style.php?theme=green">
	<link rel="stylesheet" href="css/modular.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
	<script src="js/jquery.min.js"></script>
	<script src="js/ajax-functions.js"></script>
	<script type="text/javascript"> 
	function delay(ms){
	   var start = new Date().getTime();
	   var end = start;
	   while(end < start + ms) {
		 end = new Date().getTime();
	  }
	}
	$(document).ready(function() 
	{
		function functionToLoadFile() 
		{
			jQuery.get('getVariables.php?keywords>refresh=1', function(data) 
			{
				if (data == "1") 
				{
					document.getElementById('buzzer').play();
					delay(1000);
					$(location).attr('href', 'app.php');
					jQuery.get('setVariables.php?keywords>refresh=0');
				}
				setTimeout(functionToLoadFile, 5000);
			});
		}
		setTimeout(functionToLoadFile, 10);
	});
	</script>
	<style>
		:root {
			--primary-color: #4f46e5;
			--primary-hover: #4338ca;
			--secondary-color: #7c3aed;
			--background-color: #f4f6ff;
			--error-color: #dc2626;
			--success-color: #22c55e;
			--text-color: #111827;
			--text-light: #6b7280;
			--border-color: #e5e7eb;
			--card-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
		}
		
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
		
		body {
			font-family: 'Inter', sans-serif;
			background: linear-gradient(135deg, var(--background-color), #e0e7ff);
			color: var(--text-color);
			line-height: 1.6;
			min-height: 100vh;
			padding: 2rem;
			position: relative;
			overflow-x: hidden;
			animation: gradientShift 15s ease infinite;
			background-size: 200% 200%;
		}
		
		@keyframes gradientShift {
			0% { background-position: 0% 50%; }
			50% { background-position: 100% 50%; }
			100% { background-position: 0% 50%; }
		}
		
		body::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.2), transparent 50%);
			z-index: -1;
		}
		
		.header-navbar {
			position: sticky;
			top: 0;
			background: rgba(255, 255, 255, 0.95);
			border-radius: 0.75rem;
			box-shadow: var(--card-shadow);
			padding: 1rem 2rem;
			margin-bottom: 2rem;
			backdrop-filter: blur(10px);
			z-index: 1000;
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 1rem;
			animation: fadeInDown 0.8s ease-out;
		}
		
		.header-title h1 {
			font-size: 1.75rem;
			font-weight: 700;
			background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
		}
		
		.top-menu {
			list-style: none;
			display: flex;
			gap: 1rem;
			padding: 0;
			align-items: center;
			flex-wrap: wrap;
		}
		
		.top-menu li {
			display: inline-block;
		}
		
		.top-menu a {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.75rem 1.5rem;
			border-radius: 0.5rem;
			color: var(--text-color);
			text-decoration: none;
			font-weight: 500;
			transition: all 0.3s ease;
		}
		
		.top-menu a:hover, .top-menu a.active {
			background: var(--primary-color);
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
		}
		
		.top-menu .fr {
			margin-left: auto;
		}
		
		.top-menu img {
			width: 20px;
			height: 20px;
		}
		
		.navbar-toggle {
			display: none;
			cursor: pointer;
			font-size: 1.5rem;
			color: var(--text-color);
		}
		
		.cta {
			float: right;
			padding: 0.5rem 1rem;
			background: rgba(255, 255, 255, 0.95);
			border-radius: 0.75rem;
			box-shadow: var(--card-shadow);
			font-size: 0.9rem;
			color: var(--text-color);
			animation: fadeIn 0.5s ease-out;
		}
		
		.cta .button-small {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
		}
		
		.cta a {
			color: var(--primary-color);
			text-decoration: none;
			font-weight: 500;
		}
		
		.cta a:hover {
			color: var(--primary-hover);
		}
		
		.form-module {
			background: rgba(255, 255, 255, 0.95);
			border-radius: 1.5rem;
			box-shadow: var(--card-shadow);
			width: 100%;
			max-width: 900px;
			margin: 0 auto;
			padding: 2.5rem;
			border: 1px solid rgba(255, 255, 255, 0.1);
			backdrop-filter: blur(10px);
			animation: fadeInUp 0.8s ease-out;
		}
		
		.content {
			padding: 1.5rem;
		}
		
		.headings {
			font-size: 1.5rem;
			font-weight: 600;
			margin-bottom: 1rem;
			color: var(--text-color);
		}
		
		.headings.red {
			color: var(--error-color);
		}
		
		.headings a {
			color: var(--primary-color);
			text-decoration: none;
			font-weight: 500;
		}
		
		.headings a:hover {
			color: var(--primary-hover);
		}
		
		.fr {
			float: right;
		}
		
		table {
			width: 100%;
			border-collapse: collapse;
			background: white;
			border-radius: 0.75rem;
			overflow: hidden;
			box-shadow: var(--card-shadow);
		}
		
		th, td {
			padding: 1rem;
			text-align: left;
			border-bottom: 1px solid var(--border-color);
		}
		
		th {
			background: var(--primary-color);
			color: white;
			font-weight: 600;
		}
		
		tr:last-child td {
			border-bottom: none;
		}
		
		.red {
			color: var(--error-color);
			font-weight: 500;
		}
		
		input[type="file"] {
			padding: 0.75rem;
			border: 1px solid var(--border-color);
			border-radius: 0.5rem;
			background: #f9fafb;
			margin-bottom: 1rem;
			width: 100%;
		}
		
		input[type="submit"] {
			padding: 0.75rem 1.5rem;
			background: var(--primary-color);
			color: white;
			border: none;
			border-radius: 0.5rem;
			font-size: 1rem;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.3s ease;
		}
		
		input[type="submit"]:hover {
			background: var(--primary-hover);
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
		}
		
		@keyframes fadeInDown {
			from { opacity: 0; transform: translateY(-20px); }
			to { opacity: 1; transform: translateY(0); }
		}
		
		@keyframes fadeInUp {
			from { opacity: 0; transform: translateY(20px); }
			to { opacity: 1; transform: translateY(0); }
		}
		
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		
		@media (max-width: 768px) {
			.form-module {
				padding: 1.5rem;
			}
			.header-title h1 {
				font-size: 1.5rem;
			}
			.navbar-toggle {
				display: block;
			}
			.top-menu {
				display: none;
				flex-direction: column;
				width: 100%;
				background: rgba(255, 255, 255, 0.95);
				border-radius: 0.5rem;
				padding: 1rem;
				position: absolute;
				top: 100%;
				left: 0;
				z-index: 999;
			}
			.top-menu.active {
				display: flex;
			}
			.top-menu .fr {
				margin-left: 0;
			}
			.fr {
				float: none;
			}
			.header-navbar {
				flex-direction: column;
				align-items: flex-start;
			}
		}
	</style>
</head>
<body>
	<div class="header-navbar">
		<div class="header-title">
			<h1>IoT Accident Recorder Using Blockchain</h1>
		</div>
		<span class="navbar-toggle material-symbols-rounded">menu</span>
		<ul class="top-menu">
			<li><a class="active" href="app.php"><span class="material-symbols-rounded">home</span>Home</a></li>
			<li><a href="editBlock.php"><span class="material-symbols-rounded">edit</span>Tamper DB</a></li>
			<li class="fr"><a class="side-menu" href="diffBlockchain.php"><img src="images/diff.png">Diff DB</a></li>
			<li class="fr"><a class="side-menu" href="viewDB.php" target="_blank"><img src="images/database.png">View DB</a></li>
			<li><a href="logout.php"><span class="material-symbols-rounded">logout</span>Log Out</a></li>
		</ul>
	</div>
	<audio id="buzzer"><source src="beep.ogg" type="audio/ogg"></audio>
	<div class="form-module form-module-large">
		<?php if(!checkBlockchainIntegrity($simbaBlockchain)): ?>
			<div class="content">
				<h2 class="headings" style="color: red;">Database corrupted</h2>
				<h2 class="headings">Please load an uncorrupted database</h2>
				<div style="clear: both;"> </div>
				<br/>
				<form action="uploadBlockchain.php" method="post" enctype="multipart/form-data">
					<input type="file" name="blockchainDatabase" id="blockchainDatabase">
					<input type="submit" value="Upload Database" name="submit" value="Upload">
				</form>
				<div style="clear: both;"> </div>
				<br/>
			</div>
		<?php else: ?>
			<div class="content">
				<div style="clear: both;"> </div>
				<br/>
				<h2 class="headings fr">Total Events: <?php echo get_ini_value_in("blockchain.ini", "indexes", "last_block_index"); ?></h2>
				<h2 class="headings">Accident History <a href="clearAccidents.php">Clear Accidents</a></h2>
				<div style="clear: both;"> </div>
				<table>
					<tr>
						<th>S. No.</th>
						<th>Speed</th>
						<th>Location</th>
						<th>Alcohol</th>
						<th>Sleep Status</th>
						<th>Date</th>
						<th>Time</th>
					</tr>
					<?php
						$last_block_index = get_ini_value_in("blockchain.ini", "indexes", "last_block_index");
						for($block_id = "1"; $block_id <= $last_block_index; $block_id++)
						{
							$block_data = explode("*",get_ini_value_in("blockchain.ini", "block-{$block_id}", "data"));
							if($block_data[0] > 80)
							{
								$speed_css = "red";
							}
							else
							{
								$speed_css = "";
							}
							if($block_data[2] == "Drunk")
							{
								$alcohol_status_css = "red";
							}
							else
							{
								$alcohol_status_css = "";
							}
							if($block_data[3] == "Sleeping")
							{
								$sleep_status_css = "red";
							}
							else
							{
								$sleep_status_css = "";
							}
							echo "<tr>";
							echo "<td>" . $block_id . "</td>"; 
							echo "<td class=$speed_css>" . $block_data[0] . "</td>"; 
							echo "<td>" . $block_data[1] . "</td>";
							echo "<td class=$alcohol_status_css>" . $block_data[2] . "</td>";
							echo "<td class=$sleep_status_css>" . $block_data[3] . "</td>";
							echo "<td>" . $block_data[4] . "</td>";
							echo "<td>" . $block_data[5] . "</td>";
							echo "</tr>";
						}
					?>
				</table>
				<div style="clear: both;"> </div>
				<br/>
			</div>
		<?php endif; ?>
	</div>
	<script>
		document.querySelector('.navbar-toggle').addEventListener('click', () => {
			document.querySelector('.top-menu').classList.toggle('active');
		});
	</script>
</body>
</html>