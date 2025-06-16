<?php
include 'db_config.php';
include 'phpqrcode/qrlib.php';
session_start();

$admin_user = "admin";
$admin_pass = "admin123";

if (isset($_POST['login'])) {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Invalid credentials.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

if (isset($_GET['approve']) && isset($_GET['id']) && $_SESSION['admin']) {
    $id = intval($_GET['id']);
    $conn->query("UPDATE students SET approved = 1 WHERE id = $id");
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Student Portfolio System</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: "SF Pro Display", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #50d5b7 0%, #067d68 100%);
            min-height: 100vh;
            line-height: 1.6;
            color: #333;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Login Form Styles */
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            max-width: 450px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .login-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #1e3c72;
            z-index: 2;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
            background: rgba(255, 255, 255, 1);
        }
        
        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.4);
        }
        
        .error-message {
            background: #fee;
            color: #c53030;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #c53030;
        }
        
        /* Dashboard Styles */
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .dashboard-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dashboard-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .dashboard-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .welcome-text {
            color: #666;
            font-size: 1.1rem;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .stat-icon.pending { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
        .stat-icon.approved { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        
        .section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .portfolio-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .portfolio-card.pending {
            border-left-color: #f39c12;
            background: linear-gradient(135deg, #fff5e6 0%, #fef7ed 100%);
        }
        
        .portfolio-card.approved {
            border-left-color: #27ae60;
            background: linear-gradient(135deg, #f0fdf4 0%, #f7fee7 100%);
        }
        
        .portfolio-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .portfolio-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .portfolio-info h4 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .portfolio-username {
            color: #666;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .portfolio-bio {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .portfolio-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .approve-btn {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .approve-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .view-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .qr-code {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-icon {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-badge.approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #00b894;
        }
        
        @media (max-width: 768px) {
            .admin-container { padding: 10px; }
            .dashboard-header { padding: 20px; }
            .dashboard-title h1 { font-size: 1.5rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .portfolio-header { flex-direction: column; align-items: flex-start; }
            .portfolio-actions { width: 100%; justify-content: flex-start; }
            .login-container { padding: 30px; }
            .login-title { font-size: 1.8rem; }
        }
        
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 8s linear infinite;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 6s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 1s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 3s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 5s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 7s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 2.5s; }
        
        @keyframes float {
            0% { transform: translateY(100vh) scale(0); }
            10% { transform: translateY(90vh) scale(1); }
            90% { transform: translateY(-10vh) scale(1); }
            100% { transform: translateY(-10vh) scale(0); }
        }
    </style>
</head>
<body>
    <div class="floating-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <?php if (!isset($_SESSION['admin'])): ?>
        <div class="login-wrapper">
            <div class="login-container">
                <div class="login-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1 class="login-title">Admin Login</h1>
                <p class="login-subtitle">Access the portfolio management dashboard</p>
                
                <form method="POST">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Admin Username" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="admin-container">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <div class="dashboard-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div>
                        <h1>Admin Dashboard</h1>
                        <p class="welcome-text">Welcome back, Administrator</p>
                    </div>
                </div>
                <a href="?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>

            <?php
            $pending_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE approved = 0")->fetch_assoc()['count'];
            $approved_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE approved = 1")->fetch_assoc()['count'];
            ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Pending Approval</div>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon approved">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="stat-number"><?php echo $approved_count; ?></div>
                            <div class="stat-label">Approved Portfolios</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <h2 class="section-title">Pending Portfolios</h2>
                </div>

                <?php
                $pending = $conn->query("SELECT * FROM students WHERE approved = 0 ORDER BY id DESC");
                if ($pending->num_rows == 0) {
                    echo '<div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <h3>No Pending Portfolios</h3>
                            <p>All portfolios have been reviewed and approved.</p>
                          </div>';
                } else {
                    while ($s = $pending->fetch_assoc()) {
                        echo '<div class="portfolio-card pending">
                                <div class="portfolio-header">
                                    <div class="portfolio-info">
                                        <h4>' . htmlspecialchars($s['name']) . '</h4>
                                        <div class="portfolio-username">@' . htmlspecialchars($s['username']) . '</div>
                                    </div>
                                    <div class="status-badge pending">Pending Review</div>
                                </div>
                                <div class="portfolio-bio">' . htmlspecialchars(substr($s['bio'], 0, 150)) . '...</div>
                                <div class="portfolio-actions">
                                    <a class="approve-btn" href="admin.php?approve=1&id=' . $s['id'] . '">
                                        <i class="fas fa-check"></i>
                                        Approve Portfolio
                                    </a>
                                </div>
                              </div>';
                    }
                }
                ?>
            </div>

            <div class="section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h2 class="section-title">Approved Portfolios</h2>
                </div>

                <?php
                $approved = $conn->query("SELECT * FROM students WHERE approved = 1 ORDER BY id DESC");
                if ($approved->num_rows == 0) {
                    echo '<div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                            <h3>No Approved Portfolios</h3>
                            <p>No portfolios have been approved yet.</p>
                          </div>';
                } else {
                    while ($s = $approved->fetch_assoc()) {
                        $url = "http://localhost/student_portfolio/student.php?username=" . urlencode($s['username']);
                        $qr_file = "assets/qr_" . $s['username'] . ".png";
                        if (!file_exists($qr_file)) {
                            QRcode::png($url, $qr_file, QR_ECLEVEL_L, 4);
                        }

                        echo '<div class="portfolio-card approved">
                                <div class="portfolio-header">
                                    <div class="portfolio-info">
                                        <h4>' . htmlspecialchars($s['name']) . '</h4>
                                        <div class="portfolio-username">@' . htmlspecialchars($s['username']) . '</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div class="status-badge approved">Live</div>
                                        <img src="' . htmlspecialchars($qr_file) . '" alt="QR Code" class="qr-code">
                                    </div>
                                </div>
                                <div class="portfolio-bio">' . htmlspecialchars(substr($s['bio'], 0, 150)) . '...</div>
                                <div class="portfolio-actions">
                                    <a class="view-btn" href="' . htmlspecialchars($url) . '" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                        View Portfolio
                                    </a>
                                </div>
                              </div>';
                    }
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Add loading state to approve buttons
        document.querySelectorAll('.approve-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving...';
                this.style.pointerEvents = 'none';
            });
        });

        // Add click to copy functionality for QR codes
        document.querySelectorAll('.qr-code').forEach(qr => {
            qr.addEventListener('click', function() {
                // Create a temporary notification
                const notification = document.createElement('div');
                notification.textContent = 'QR Code clicked!';
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #27ae60;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 10px;
                    z-index: 1000;
                    animation: slideIn 0.3s ease;
                `;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            });
        });

        // Auto refresh every 30 seconds to check for new submissions
        <?php if (isset($_SESSION['admin'])): ?>
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>