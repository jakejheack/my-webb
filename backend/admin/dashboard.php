<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get statistics
    $stats = [];
    
    // Count projects
    $stmt = $db->query("SELECT COUNT(*) FROM projects WHERE is_active = 1");
    $stats['projects'] = $stmt->fetchColumn();
    
    // Count skills
    $stmt = $db->query("SELECT COUNT(*) FROM skills WHERE is_active = 1");
    $stats['skills'] = $stmt->fetchColumn();
    
    // Count unread messages
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $stats['unread_messages'] = $stmt->fetchColumn();
    
    // Count total messages
    $stmt = $db->query("SELECT COUNT(*) FROM contact_messages");
    $stats['total_messages'] = $stmt->fetchColumn();
    
    // Get recent messages
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Jake Portfolio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F0F23 0%, #1A1A2E 100%);
            min-height: 100vh;
            color: #ffffff;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 217, 255, 0.3);
            padding: 20px 0;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-link {
            color: #A0A0A0;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: #00D9FF;
            background: rgba(0, 217, 255, 0.1);
        }
        
        .logout-btn {
            background: linear-gradient(45deg, #FF6B6B, #FF8E8E);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 217, 255, 0.2);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #A0A0A0;
            font-size: 1.1rem;
        }
        
        .section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #00D9FF;
        }
        
        .message-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .message-name {
            font-weight: 600;
            color: #00D9FF;
        }
        
        .message-date {
            color: #A0A0A0;
            font-size: 0.9rem;
        }
        
        .message-subject {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .message-preview {
            color: #A0A0A0;
            font-size: 0.9rem;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
            display: block;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 217, 255, 0.3);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">Jake Portfolio Admin</div>
            <nav class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="projects.php" class="nav-link">Projects</a>
                <a href="skills.php" class="nav-link">Skills</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 class="dashboard-title">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['projects']; ?></div>
                <div class="stat-label">Active Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['skills']; ?></div>
                <div class="stat-label">Skills Listed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_messages']; ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Quick Actions</h2>
            <div class="quick-actions">
                <a href="projects.php?action=add" class="action-btn">Add New Project</a>
                <a href="skills.php?action=add" class="action-btn">Add New Skill</a>
                <a href="messages.php" class="action-btn">View Messages</a>
                <a href="settings.php" class="action-btn">Site Settings</a>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Recent Messages</h2>
            <?php if (empty($recent_messages)): ?>
                <p style="color: #A0A0A0;">No messages yet.</p>
            <?php else: ?>
                <?php foreach ($recent_messages as $message): ?>
                    <div class="message-item">
                        <div class="message-header">
                            <span class="message-name"><?php echo htmlspecialchars($message['name']); ?></span>
                            <span class="message-date"><?php echo date('M j, Y', strtotime($message['created_at'])); ?></span>
                        </div>
                        <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                        <div class="message-preview"><?php echo htmlspecialchars(substr($message['message'], 0, 100)) . '...'; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>