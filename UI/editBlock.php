<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', 'C:/xampp/htdocs/new_ui/debug.log');
    
    try {
        include('session.php');
    } catch (Exception $e) {
        error_log('Session include error: ' . $e->getMessage());
        die('<html><body><h2>Error: Failed to load session - ' . htmlspecialchars($e->getMessage()) . '</h2></body></html>');
    }

    try {
        require_once('file_functions.php');
        require_once('blockchain.php');
        require_once('blockchain_functions.php');
    } catch (Exception $e) {
        error_log('Require error: ' . $e->getMessage());
        die('<html><body><h2>Error: Failed to load required files - ' . htmlspecialchars($e->getMessage()) . '</h2></body></html>');
    }

    $simbaBlockchain = null;
    try {
        loadBlockchain("blockchain.ini", $simbaBlockchain);
    } catch (Exception $e) {
        error_log('Blockchain load error: ' . $e->getMessage());
        die('<html><body><h2>Error: Failed to load blockchain - ' . htmlspecialchars($e->getMessage()) . '</h2></body></html>');
    }

    $connection_status = 0;
    try {
        if (!function_exists('get_ini_value')) {
            throw new Exception('get_ini_value() function not defined');
        }
        $ini_data = parse_ini_file("blockchain.ini", true);
        if (!isset($ini_data['keywords']) || !isset($ini_data['keywords']['connection_status'])) {
            throw new Exception('keywords section or connection_status missing in blockchain.ini');
        }
        $connection_status = get_ini_value("keywords", "connection_status");
        if ($connection_status != 1) {
            // Show warning in HTML instead of alert
        }
    } catch (Exception $e) {
        error_log('Connection status error: ' . $e->getMessage());
        $connection_status = 0;
    }

    // Load block data for display
    $selected_index = isset($_GET['index']) ? $_GET['index'] : '';
    $block_data = [];
    if ($selected_index !== '' && isset($ini_data["block-$selected_index"])) {
        $block_data = $ini_data["block-$selected_index"];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blockchain Block</title>
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
        }
        
        .headings {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
            text-align: center;
        }
        
        .headings.red {
            color: var(--error-color);
        }
        
        select {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background: #f9fafb;
            margin-bottom: 1rem;
            width: 100%;
            max-width: 300px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        select:hover {
            border-color: var(--primary-color);
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
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .warning {
            color: var(--error-color);
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .block-section {
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--text-color);
        }
        
        td input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }
        
        td input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        
        td input[readonly] {
            background: #f0f0f0;
            cursor: not-allowed;
        }
        
        .cta {
            text-align: center;
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
        }
    </style>
</head>
<body>
    <div class="header-navbar">
        <div class="header-title">
            <h1>Edit Blockchain Block</h1>
        </div>
        <span class="navbar-toggle material-symbols-rounded">menu</span>
        <ul class="top-menu">
            <li><a href="app.php"><span class="material-symbols-rounded">home</span>Home</a></li>
            <li><a class="active" href="editBlock.php"><span class="material-symbols-rounded">edit</span>Tamper DB</a></li>
            <li class="fr"><a class="side-menu" href="diffBlockchain.php"><img src="images/diff.png">Diff DB</a></li>
            <li class="fr"><a class="side-menu" href="viewDB.php" target="_blank"><img src="images/database.png">View DB</a></li>
            <li><a href="logout.php"><span class="material-symbols-rounded">logout</span>Log Out</a></li>
        </ul>
    </div>
    <audio id="buzzer"><source src="beep.ogg" type="audio/ogg"></audio>
    <div class="form-module form-module-large">
        <div class="content">
            <h2 class="headings">Edit Details</h2>
            <div style="clear: both;"></div>
            <form method="GET" action="editBlock.php">
                <label for="index">Index</label>
                <select name="index" id="index" onchange="this.form.submit()">
                    <option value="" disabled <?php echo $selected_index === '' ? 'selected' : ''; ?>>Select Index</option>
                    <?php 
                        try {
                            $last_block_index = get_ini_value_in("blockchain.ini", "indexes", "last_block_index");
                            if ($last_block_index && is_numeric($last_block_index)) {
                                for($i = 1; $i <= $last_block_index; $i++) {
                                    $selected = $selected_index == $i ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No blocks found</option>";
                            }
                        } catch (Exception $e) {
                            error_log('Block index error: ' . $e->getMessage());
                            echo "<option value='' disabled>Error: " . htmlspecialchars($e->getMessage()) . "</option>";
                        }
                    ?>
                </select>
            </form>
            <?php if (!empty($block_data)) { ?>
                <form action="tamperBlockchain.php" method="GET">
                    <input type="hidden" name="index" value="<?php echo htmlspecialchars($selected_index); ?>">
                    <div class="block-section">
                        <table>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                            <?php foreach ($block_data as $key => $value) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($key); ?></td>
                                    <td>
                                        <?php if ($key === 'data') { ?>
                                            <input type="text" name="data" id="data" value="<?php echo htmlspecialchars($value); ?>">
                                        <?php } else { ?>
                                            <input type="text" value="<?php echo htmlspecialchars($value); ?>" readonly>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <div class="cta">
                        <input type="submit" name="submit" value="Change">
                    </div>
                </form>
            <?php } ?>
            <div style="clear: both;"></div>
            <br/>
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