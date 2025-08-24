<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                try {
                    $db->beginTransaction();
                    
                    foreach ($_POST['settings'] as $key => $value) {
                        $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                        $stmt->execute([$value, $key]);
                    }
                    
                    $db->commit();
                    $message = 'Settings updated successfully!';
                } catch (Exception $e) {
                    $db->rollback();
                    $error = 'Error updating settings: ' . $e->getMessage();
                }
                break;
                
            case 'add_setting':
                try {
                    $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['setting_key'],
                        $_POST['setting_value'],
                        $_POST['setting_type'],
                        $_POST['description']
                    ]);
                    
                    $message = 'Setting added successfully!';
                } catch (Exception $e) {
                    $error = 'Error adding setting: ' . $e->getMessage();
                }
                break;
                
            case 'delete_setting':
                try {
                    $stmt = $db->prepare("DELETE FROM site_settings WHERE setting_key = ?");
                    $stmt->execute([$_POST['setting_key']]);
                    $message = 'Setting deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting setting: ' . $e->getMessage();
                }
                break;
                
            case 'change_password':
                try {
                    $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$_POST['new_password'], $_SESSION['admin_id']]);
                    $message = 'Password changed successfully!';
                } catch (Exception $e) {
                    $error = 'Error changing password: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all settings
$stmt = $db->query("SELECT * FROM site_settings ORDER BY setting_key");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group settings by category
$settingsGroups = [
    'Site Information' => [],
    'Hero Section' => [],
    'Contact Information' => [],
    'Social Media' => [],
    'Other' => []
];

foreach ($settings as $setting) {
    $key = $setting['setting_key'];
    
    if (strpos($key, 'site_') === 0) {
        $settingsGroups['Site Information'][] = $setting;
    } elseif (strpos($key, 'hero_') === 0) {
        $settingsGroups['Hero Section'][] = $setting;
    } elseif (strpos($key, 'contact_') === 0) {
        $settingsGroups['Contact Information'][] = $setting;
    } elseif (in_array($key, ['github_url', 'linkedin_url', 'twitter_url', 'facebook_url', 'instagram_url'])) {
        $settingsGroups['Social Media'][] = $setting;
    } else {
        $settingsGroups['Other'][] = $setting;
    }
}

// Get current admin user info
$stmt = $db->prepare("SELECT username, email FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Jake Portfolio Admin</title>
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
        
        .nav-link:hover,
        .nav-link.active {
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
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .card h2 {
            color: #00D9FF;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #A0A0A0;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.4);
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: #00D9FF;
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 217, 255, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #A0A0A0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #FF6B6B, #FF8E8E);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
        
        .success-message {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            color: #00FF88;
        }
        
        .error-message {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            color: #FF6B6B;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }
        
        .setting-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .setting-key {
            font-weight: 600;
            color: #00D9FF;
            margin-bottom: 5px;
        }
        
        .setting-description {
            font-size: 0.9rem;
            color: #A0A0A0;
            margin-bottom: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .tab {
            padding: 10px 20px;
            background: transparent;
            border: none;
            color: #A0A0A0;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: #00D9FF;
            border-bottom-color: #00D9FF;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
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
                <a href="settings.php" class="nav-link active">Settings</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Site Settings</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('site-settings')">Site Settings</button>
            <button class="tab" onclick="showTab('account-settings')">Account Settings</button>
            <button class="tab" onclick="showTab('add-setting')">Add New Setting</button>
        </div>
        
        <!-- Site Settings Tab -->
        <div id="site-settings" class="tab-content active">
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="settings-grid">
                    <?php foreach ($settingsGroups as $groupName => $groupSettings): ?>
                        <?php if (!empty($groupSettings)): ?>
                        <div class="card">
                            <h2><?php echo htmlspecialchars($groupName); ?></h2>
                            
                            <?php foreach ($groupSettings as $setting): ?>
                            <div class="setting-item">
                                <div class="setting-key"><?php echo htmlspecialchars($setting['setting_key']); ?></div>
                                <?php if ($setting['description']): ?>
                                    <div class="setting-description"><?php echo htmlspecialchars($setting['description']); ?></div>
                                <?php endif; ?>
                                
                                <?php if ($setting['setting_type'] === 'textarea'): ?>
                                    <textarea name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]" 
                                              class="form-textarea"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                <?php else: ?>
                                    <input type="text" 
                                           name="settings[<?php echo htmlspecialchars($setting['setting_key']); ?>]" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                <?php endif; ?>
                                
                                <button type="button" class="btn btn-danger" style="font-size: 0.8rem; padding: 4px 8px; margin-top: 10px;" 
                                        onclick="deleteSetting('<?php echo htmlspecialchars($setting['setting_key']); ?>')">Delete</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Update All Settings</button>
            </form>
        </div>
        
        <!-- Account Settings Tab -->
        <div id="account-settings" class="tab-content">
            <div class="card">
                <h2>Account Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($adminUser['username']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="<?php echo htmlspecialchars($adminUser['email']); ?>" readonly>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2>Change Password</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Add New Setting Tab -->
        <div id="add-setting" class="tab-content">
            <div class="card">
                <h2>Add New Setting</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add_setting">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="setting_key" class="form-label">Setting Key</label>
                            <input type="text" id="setting_key" name="setting_key" class="form-input" 
                                   placeholder="e.g., custom_setting" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="setting_type" class="form-label">Setting Type</label>
                            <select id="setting_type" name="setting_type" class="form-select" required>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="setting_value" class="form-label">Setting Value</label>
                        <input type="text" id="setting_value" name="setting_value" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-textarea" 
                                  placeholder="Brief description of this setting"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add Setting</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        function deleteSetting(settingKey) {
            if (confirm('Are you sure you want to delete this setting?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_setting">
                    <input type="hidden" name="setting_key" value="${settingKey}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>