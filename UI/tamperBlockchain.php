<?php
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', 'C:/xampp/htdocs/new_ui/debug.log');

    // Include session management
    try {
        include('session.php');
    } catch (Exception $e) {
        error_log('Session include error: ' . $e->getMessage());
        die('<html><body><h2>Error: Failed to load session - ' . htmlspecialchars($e->getMessage()) . '</h2></body></html>');
    }

    // Include required files
    try {
        require_once('file_functions.php');
        require_once('blockchain.php');
        require_once('blockchain_functions.php');
    } catch (Exception $e) {
        error_log('Require error: ' . $e->getMessage());
        die('<html><body><h2>Error: Failed to load required files - ' . htmlspecialchars($e->getMessage()) . '</h2></body></html>');
    }

    // Load blockchain
    $simbaBlockchain = null;
    try {
        loadBlockchain("blockchain.ini", $simbaBlockchain);
    } catch (Exception $e) {
        error_log('Blockchain load error: ' . $e->getMessage());
        die('<html><body><h2>Error: Failed to load blockchain - ' . htmlspecialchars($e->getMessage()) . '</h2></body></html>');
    }

    // Validate GET parameters
    if (!isset($_GET['index']) || !isset($_GET['data']) || !isset($_GET['submit'])) {
        header("Location: editBlock.php?error=Missing parameters");
        exit;
    }

    $index = $_GET['index'];
    $data = $_GET['data'];

    // Validate index
    $ini_data = parse_ini_file("blockchain.ini", true);
    $last_block_index = $ini_data['indexes']['last_block_index'] ?? 0;
    if (!is_numeric($index) || $index < 0 || $index > $last_block_index || !isset($ini_data["block-$index"])) {
        header("Location: editBlock.php?error=Invalid index");
        exit;
    }

    // Compare new data with existing data
    $current_data = $ini_data["block-$index"]['data'] ?? '';
    if ($current_data === $data) {
        header("Location: editBlock.php?index=$index&message=No changes made");
        exit;
    }

    // Attempt to tamper with the blockchain
    try {
        if (tamperBlockchain($simbaBlockchain, $index, $data)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tamper Blockchain</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary-color: #4f46e5;
			--error-color: #dc2626;
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
		<h2 class="message-title">Data Tampered Successfully</h2>
		<p class="message-text">The blockchain data has been modified.</p>
		<div class="redirect-timer">Redirecting to Edit Block page...</div>
	</div>
	<script>
		setTimeout(function() { window.location = "editBlock.php"; }, 2000);
	</script>
</body>
</html>
<?php
        } else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tamper Blockchain</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary-color: #4f46e5;
			--error-color: #dc2626;
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
			color: var(--error-color);
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
			background: rgba(220, 38, 38, 0.1);
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
		<h2 class="message-title">Tampering Failed</h2>
		<p class="message-text">The blockchain database could not be modified.</p>
		<div class="redirect-timer">Redirecting to Edit Block page...</div>
	</div>
	<script>
		setTimeout(function() { window.location = "editBlock.php?index=<?php echo htmlspecialchars($index); ?>"; }, 2000);
	</script>
</body>
</html>
<?php
        }
    } catch (Exception $e) {
        error_log('Tamper blockchain error: ' . $e->getMessage());
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tamper Blockchain</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--primary-color: #4f46e5;
			--error-color: #dc2626;
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
			color: var(--error-color);
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
			background: rgba(220, 38, 38, 0.1);
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
		<h2 class="message-title">Error</h2>
		<p class="message-text">An error occurred: <?php echo htmlspecialchars($e->getMessage()); ?></p>
		<div class="redirect-timer">Redirecting to Edit Block page...</div>
	</div>
	<script>
		setTimeout(function() { window.location = "editBlock.php"; }, 2000);
	</script>
</body>
</html>
<?php
    }
?>