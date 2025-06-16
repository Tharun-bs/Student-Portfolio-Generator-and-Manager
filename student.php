<?php
include 'db_config.php';
include 'phpqrcode/qrlib.php';
session_start();

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $skills = explode(',', $_POST['skills']);
    $project_title = $_POST['project_title'];
    $project_description = $_POST['project_description'];
    $github_link = $_POST['github_link'];

    $image = $_FILES['image']['name'];
    $target = "assets/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $stmt = $conn->prepare("INSERT INTO students (username, name, email, bio, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $name, $email, $bio, $target);
    $stmt->execute();
    $student_id = $stmt->insert_id;

    foreach ($skills as $skill) {
        $skill = trim($skill);
        if ($skill != "") {
            $conn->query("INSERT INTO skills (student_id, skill_name) VALUES ($student_id, '$skill')");
        }
    }

    $stmt = $conn->prepare("INSERT INTO projects (student_id, title, description, github_link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $project_title, $project_description, $github_link);
    $stmt->execute();

    header("Location: student.php?username=" . urlencode($username));
    exit();
}

// Display Portfolio
if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Portfolio Not Found</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: url("https://static.vecteezy.com/system/resources/previews/007/078/777/non_2x/seamless-pattern-with-education-icons-doodle-with-education-and-school-icons-on-black-background-vintage-education-pattern-free-vector.jpg");
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .error-container {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    border-radius: 20px;
                    padding: 40px;
                    text-align: center;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                }
                .error-icon { font-size: 64px; color: #ff6b6b; margin-bottom: 20px; }
                h2 { color: #2c3e50; margin-bottom: 20px; font-weight: 600; }
                .back-btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 12px 30px;
                    border-radius: 25px;
                    text-decoration: none;
                    display: inline-block;
                    transition: all 0.3s ease;
                    font-weight: 500;
                }
                .back-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2>Portfolio Not Found</h2>
                <p>The requested portfolio does not exist or has been removed.</p>
                <br>
                <a href="student.php" class="back-btn"><i class="fas fa-arrow-left"></i> Go Back</a>
            </div>
        </body>
        </html>';
        exit();
    }

    if (!$student['approved']) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Portfolio Pending Approval</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: url("https://thumbs.dreamstime.com/b/work-office-desk-top-view-lot-different-stationery-elements-seamless-vector-wallpaper-business-job-theme-image-diversity-200292769.jpg");
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .pending-container {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    border-radius: 20px;
                    padding: 40px;
                    text-align: center;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                }
                .pending-icon { font-size: 64px; color: #f39c12; margin-bottom: 20px; animation: pulse 2s infinite; }
                @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
                h2 { color: #2c3e50; margin-bottom: 20px; font-weight: 600; }
                .back-btn {
                    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
                    color: white;
                    padding: 12px 30px;
                    border-radius: 25px;
                    text-decoration: none;
                    display: inline-block;
                    transition: all 0.3s ease;
                    font-weight: 500;
                }
                .back-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
                }
            </style>
        </head>
        <body>
            <div class="pending-container">
                <div class="pending-icon"><i class="fas fa-clock"></i></div>
                <h2>Portfolio Pending Approval</h2>
                <p>Your portfolio has been submitted and is awaiting admin approval.</p>
                <br>
                <a href="student.php" class="back-btn"><i class="fas fa-arrow-left"></i> Go Back</a>
            </div>
        </body>
        </html>';
        exit();
    }

    $url = "http://localhost/student_portfolio/student.php?username=" . urlencode($username);
    $qr_file = "assets/qr_$username.png";
    if (!file_exists($qr_file)) {
        QRcode::png($url, $qr_file, QR_ECLEVEL_L, 4);
    }

    // Display Portfolio with Enhanced UI
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>' . htmlspecialchars($student['name']) . ' - Portfolio</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                font-family: "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: url("https://img.freepik.com/premium-vector/stem-vector-concept-blue-seamless-pattern-background_104589-3725.jpg");
                min-height: 100vh;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .portfolio-header {
                text-align: center;
                margin-bottom: 40px;
                padding: 40px 0;
            }
            .profile-image {
                width: 200px;
                height: 200px;
                border-radius: 50%;
                object-fit: cover;
                border: 6px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
                margin-bottom: 20px;
                transition: transform 0.3s ease;
            }
            .profile-image:hover { transform: scale(1.05); }
            .name-title {
                font-size: 3rem;
                font-weight: 700;
                color: white;
                margin-bottom: 10px;
                text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            }
            .email {
                font-size: 1.2rem;
                color: rgba(255, 255, 255, 0.9);
                margin-bottom: 20px;
            }
            .content-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 40px;
            }
            .card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 20px;
                padding: 30px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            }
            .card-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .card-icon {
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.2rem;
            }
            .bio-text {
                font-size: 1.1rem;
                color: #555;
                line-height: 1.7;
            }
            .skills-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 12px;
            }
            .skill-tag {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 10px 16px;
                border-radius: 25px;
                text-align: center;
                font-weight: 500;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .skill-tag:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            }
            .project-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 20px;
                padding: 30px;
                margin-bottom: 25px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .project-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            }
            .project-title {
                font-size: 1.4rem;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .project-description {
                color: #555;
                margin-bottom: 20px;
                line-height: 1.6;
            }
            .github-link {
                background: linear-gradient(135deg, #24292e 0%, #1a1e22 100%);
                color: white;
                padding: 12px 24px;
                border-radius: 25px;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .github-link:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                color: white;
            }
            .share-section {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: 20px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
                margin-bottom: 30px;
            }
            .share-title {
                font-size: 1.5rem;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .url-input {
                width: 100%;
                padding: 15px;
                border: 2px solid #e0e0e0;
                border-radius: 15px;
                font-size: 1rem;
                margin-bottom: 20px;
                background: rgba(255, 255, 255, 0.8);
                transition: all 0.3s ease;
            }
            .url-input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .qr-code {
                max-width: 200px;
                border-radius: 15px;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                margin: 20px auto;
                display: block;
            }
            .back-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 30px;
                border-radius: 25px;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                font-weight: 500;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .back-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
                color: white;
            }
            @media (max-width: 768px) {
                .content-grid { grid-template-columns: 1fr; }
                .name-title { font-size: 2rem; }
                .profile-image { width: 150px; height: 150px; }
                .skills-grid { grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="portfolio-header">
                <img src="' . htmlspecialchars($student['image_path']) . '" alt="Profile Picture" class="profile-image">
                <h1 class="name-title">' . htmlspecialchars($student['name']) . '</h1>
                <div class="email"><i class="fas fa-envelope"></i> ' . htmlspecialchars($student['email']) . '</div>
            </div>
            
            <div class="content-grid">
                <div class="card">
                    <h3 class="card-title">
                        <div class="card-icon"><i class="fas fa-user"></i></div>
                        About Me
                    </h3>
                    <p class="bio-text">' . nl2br(htmlspecialchars($student['bio'])) . '</p>
                </div>
                
                <div class="card">
                    <h3 class="card-title">
                        <div class="card-icon"><i class="fas fa-code"></i></div>
                        Skills
                    </h3>
                    <div class="skills-grid">';
    
    $sid = $student['id'];
    $skills = $conn->query("SELECT skill_name FROM skills WHERE student_id = $sid");
    while ($s = $skills->fetch_assoc()) {
        echo '<div class="skill-tag">' . htmlspecialchars($s['skill_name']) . '</div>';
    }
    
    echo '          </div>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <div class="card-icon"><i class="fas fa-rocket"></i></div>
                    Projects
                </h3>';
    
    $projects = $conn->query("SELECT * FROM projects WHERE student_id = $sid");
    while ($p = $projects->fetch_assoc()) {
        echo '<div class="project-card">
                <h4 class="project-title">
                    <i class="fas fa-folder-open"></i>
                    ' . htmlspecialchars($p['title']) . '
                </h4>
                <p class="project-description">' . nl2br(htmlspecialchars($p['description'])) . '</p>
                <a href="' . htmlspecialchars($p['github_link']) . '" target="_blank" class="github-link">
                    <i class="fab fa-github"></i>
                    View on GitHub
                </a>
              </div>';
    }
    
    echo '      </div>
            
            <div class="share-section">
                <h3 class="share-title">
                    <i class="fas fa-share-alt"></i>
                    Share Portfolio
                </h3>
                <input type="text" value="' . htmlspecialchars($url) . '" readonly class="url-input" onclick="this.select();">
                <img src="' . htmlspecialchars($qr_file) . '" alt="QR Code" class="qr-code">
                <p style="color: #666; margin-top: 10px;">Scan QR code to share instantly</p>
            </div>
            
            <div style="text-align: center;">
                <a href="student.php" class="back-btn">
                    <i class="fas fa-plus"></i>
                    Submit Another Portfolio
                </a>
            </div>
        </div>
    </body>
    </html>';
    exit();
}
?>

<!-- Enhanced HTML Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Student Portfolio - Create Your Profile</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: url("https://st2.depositphotos.com/4948655/11775/v/950/depositphotos_117759442-stock-illustration-seamless-doodles-education-pattern.jpg");
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 43px;
            color: #667eea;
            z-index: 2;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
        }
        
        .form-group textarea {
            min-height: 100px;
            font-family: inherit;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 15px;
            border: 2px dashed #667eea;
            border-radius: 15px;
            background: rgba(102, 126, 234, 0.05);
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input-button:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: #5a67d8;
        }
        
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .submit-btn:hover::before {
            left: 100%;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .hint {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .container { padding: 30px 20px; }
            h1 { font-size: 2rem; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .form-group input, .form-group textarea { padding: 12px 12px 12px 40px; }
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { top: 60%; right: 10%; animation-delay: 2s; }
        .shape:nth-child(3) { bottom: 20%; left: 20%; animation-delay: 4s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape">
            <i class="fas fa-code" style="font-size: 3rem; color: white;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-laptop-code" style="font-size: 3rem; color: white;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-rocket" style="font-size: 3rem; color: white;"></i>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h1>Create Your Portfolio</h1>
            <p class="subtitle">Showcase your skills and projects to the world</p>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <i class="fas fa-at"></i>
                    <input type="text" id="username" name="username" placeholder="Choose a unique username" required>
                    <div class="hint">This will be part of your portfolio URL</div>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="bio">About You</label>
                <i class="fas fa-pen"></i>
                <textarea id="bio" name="bio" placeholder="Tell us about yourself, your interests, and what drives you..." required></textarea>
                <div class="hint">Write a compelling bio that showcases your personality</div>
            </div>
            
            <div class="form-group">
                <label for="skills">Your Skills</label>
                <i class="fas fa-code"></i>
                <input type="text" id="skills" name="skills" placeholder="JavaScript, Python, React, Node.js, MongoDB..." required>
                <div class="hint">Separate skills with commas</div>
            </div>
            
            <div class="form-group">
                <label for="project_title">Featured Project Title</label>
                <i class="fas fa-rocket"></i>
                <input type="text" id="project_title" name="project_title" placeholder="My Awesome Project" required>
            </div>
            
            <div class="form-group">
                <label for="project_description">Project Description</label>
                <i class="fas fa-clipboard"></i>
                <textarea id="project_description" name="project_description" placeholder="Describe your project, the technologies used, challenges faced, and what you learned..." required></textarea>
                <div class="hint">Make it detailed and engaging</div>
            </div>
            
            <div class="form-group">
                <label for="github_link">GitHub Repository</label>
                <i class="fab fa-github"></i>
                <input type="url" id="github_link" name="github_link" placeholder="https://github.com/username/project" required>
            </div>
            
            <div class="form-group">
                <label for="image">Profile Picture</label>
                <div class="file-input-wrapper">
                    <input type="file" id="image" name="image" accept="image/*" required class="file-input">
                    <div class="file-input-button">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Choose Profile Picture</span>
                    </div>
                </div>
                <div class="hint">Upload a professional photo (JPG, PNG, max 5MB)</div>
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Create My Portfolio
            </button>
        </form>
    </div>
    
    <script>
        // File input enhancement
        document.getElementById('image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const button = document.querySelector('.file-input-button span');
            if (fileName) {
                button.textContent = fileName;
                document.querySelector('.file-input-button').style.background = 'rgba(102, 126, 234, 0.15)';
            }
        });
        
        // Form validation enhancement
        const inputs = document.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#10b981';
                } else {
                    this.style.borderColor = '#ef4444';
                }
            });
            
            input.addEventListener('input', function() {
                this.style.borderColor = '#e0e0e0';
            });
        });
        
        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.querySelector('.submit-btn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Portfolio...';
            btn.disabled = true;
        });
        
        // Skills input enhancement
        document.getElementById('skills').addEventListener('input', function(e) {
            const skills = e.target.value.split(',').map(s => s.trim()).filter(s => s);
            const hint = this.nextElementSibling;
            hint.textContent = `${skills.length} skills added`;
        });
    </script>
</body>
</html>