<?php
    include('session.php');

// Check if download is requested
if (isset($_GET['download']) && $_GET['download'] === 'blockchain.ini') {
    $file = 'blockchain.ini';
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        die('File not found.');
    }
}

// Read blockchain.ini content
$file_content = file_exists('blockchain.ini') 
    ? file_get_contents('blockchain.ini') 
    : "File blockchain.ini not found.";

$lines = explode("\n", trim($file_content));

// Group lines by block
$blocks = [];
$current_block = '';

foreach ($lines as $line) {
    if (preg_match('/^\[.*\]$/', $line)) {
        $current_block = $line;
        $blocks[$current_block] = [];
    } else {
        if (trim($line) !== '') {
            $blocks[$current_block][] = $line;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blockchain Data</title>
    <link rel="stylesheet" href="css/reset.min.css">
    <link rel="stylesheet" href="css/style.php?theme=green">
    <link rel="stylesheet" href="css/modular.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <script src="js/ajax-functions.js"></script>
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
            --highlight-color: #fef08a;
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
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: space-between;
        }
        
        .headings {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
            width: 100%;
            text-align: center;
        }
        
        .download-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .download-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .block-section {
            width: 100%;
        }
        
        .block-section pre {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-bottom: 1rem;
            position: relative;
            transition: transform 0.3s ease;
        }
        
        .block-section pre:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }
        
        .block-section pre::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), transparent);
            border-radius: 0.75rem;
            z-index: -1;
        }
        
        .block-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .cta {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .cta .button-small {
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .cta .button-small:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .cta a {
            color: var(--text-light);
            text-decoration: none;
            margin-left: 0.5rem;
        }
        
        .cta a:hover {
            color: var(--primary-color);
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            .cta {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header-navbar">
        <div class="header-title">
            <h1>View Blockchain Data</h1>
        </div>
        <span class="navbar-toggle material-symbols-rounded">menu</span>
        <ul class="top-menu">
            <li><a href="app.php"><span class="material-symbols-rounded">home</span>Home</a></li>
            <li><a class="fr" href="editBlock.php"><span class="material-symbols-rounded">edit</span>Tamper DB</a></li>
            <li><a class="side-menu" href="diffBlockchain.php"><img src="images/diff.png">Diff DB</a></li>
            <li><a class="active" class="side-menu" href="viewDB.php" ><img src="images/database.png">View DB</a></li>
            <li><a href="logout.php"><span class="material-symbols-rounded">logout</span>Log Out</a></li>
        </ul>
    </div>
    <audio id="buzzer"><source src="beep.ogg" type="audio/ogg"></audio>
    <div class="form-module form-module-large">
        <div class="content">
            <h2 class="headings">Blockchain Data (blockchain.ini)</h2>
            <a href="?download=blockchain.ini" class="download-btn">Download blockchain.ini</a>
            <div class="block-section">
                <?php foreach ($blocks as $block => $lines) { ?>
                    <h3><?php echo htmlspecialchars($block); ?></h3>
                    <pre><?php echo htmlspecialchars(implode("\n", $lines)); ?></pre>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="js/canvasjs.min.js"></script>
    <script>
        document.querySelector('.navbar-toggle').addEventListener('click', () => {
            document.querySelector('.top-menu').classList.toggle('active');
        });
    </script>
</body>
</html>