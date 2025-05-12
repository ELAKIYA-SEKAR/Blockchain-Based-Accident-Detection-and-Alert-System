<?php
    date_default_timezone_set('Asia/Kolkata');
	
	include('file_functions.php');
	
	session_start();
	
	if(!isset($_SESSION['normal_user']))
	{
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Session Expired</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary-color: #4f46e5;
			--text-color: #111827;
			--text-light: #6b7280;
			--background-color: #f4f6ff;
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
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 100vh;
			margin: 0;
			position: relative;
			overflow-x: hidden;
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
		
		.message-container {
			max-width: 550px;
			background: rgba(255, 255, 255, 0.95);
			padding: 2.5rem;
			border-radius: 1.5rem;
			box-shadow: var(--card-shadow);
			text-align: center;
			backdrop-filter: blur(10px);
			border: 1px solid rgba(255, 255, 255, 0.1);
			animation: fadeInUp 0.8s ease-out;
		}
		
		.message-title {
			font-size: 1.75rem;
			font-weight: 600;
			margin-bottom: 1rem;
			color: var(--primary-color);
		}
		
		.message-text {
			font-size: 1.1rem;
			margin-bottom: 1.5rem;
			color: var(--text-light);
		}
		
		.redirect-timer {
			font-size: 0.9rem;
			color: var(--text-light);
			margin-top: 1rem;
			background: rgba(79, 70, 229, 0.1);
			padding: 0.5rem 1rem;
			border-radius: 0.5rem;
			display: inline-block;
		}
		
		@keyframes fadeInUp {
			from { opacity: 0; transform: translateY(20px); }
			to { opacity: 1; transform: translateY(0); }
		}
		
		@media (max-width: 480px) {
			.message-container {
				padding: 2rem;
				margin: 1rem;
			}
			.message-title {
				font-size: 1.5rem;
			}
		}
	</style>
</head>
<body>
	<div class="message-container">
		<h2 class="message-title">Session Expired</h2>
		<p class="message-text">Please log in to continue using the IoT Accident Recorder.</p>
		<div class="redirect-timer">Redirecting to login page...</div>
	</div>
	<script>
		setTimeout(function() { window.location = "index.php"; }, 2000);
	</script>
</body>
</html>
<?php
		exit;
	}
?>