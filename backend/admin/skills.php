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
            case 'add':
                try {
                    $stmt = $db->prepare("INSERT INTO skills (name, category, proficiency, display_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['category'],
                        $_POST['proficiency'],
                        $_POST['display_order']
                    ]);
                    
                    $message = 'Skill added successfully!';
                } catch (Exception $e) {
                    $error = 'Error adding skill: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                try {
                    $stmt = $db->prepare("UPDATE skills SET name = ?, category = ?, proficiency = ?, display_order = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['category'],
                        $_POST['proficiency'],
                        $_POST['display_order'],
                        $_POST['id']
                    ]);
                    
                    $message = 'Skill updated successfully!';
                } catch (Exception $e) {
                    $error = 'Error updating skill: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("UPDATE skills SET is_active = 0 WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Skill deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting skill: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all skills grouped by category
$stmt = $db->query("SELECT * FROM skills WHERE is_active = 1 ORDER BY category, display_order, name");
$allSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

$skillsByCategory = [];
foreach ($allSkills as $skill) {
    $skillsByCategory[$skill['category']][] = $skill;
}

// Get skill for editing if ID is provided
$editSkill = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM skills WHERE id = ? AND is_active = 1");
    $stmt->execute([$_GET['edit']]);
    $editSkill = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get unique categories
$categories = array_keys($skillsByCategory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - Jake Portfolio Admin</title>
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
        .form-select:focus {
            outline: none;
            border-color: #00D9FF;
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .category-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
        }
        
        .category-title {
            color: #00D9FF;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .skill-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .skill-item:last-child {
            border-bottom: none;
        }
        
        .skill-name {
            color: #ffffff;
            font-weight: 500;
        }
        
        .skill-proficiency {
            color: #00D9FF;
            font-weight: 600;
        }
        
        .skill-actions {
            display: flex;
            gap: 5px;
        }
        
        .skill-actions .btn {
            padding: 4px 8px;
            font-size: 0.8rem;
            margin: 0;
        }
        
        .proficiency-bar {
            width: 100px;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
            margin: 5px 0;
        }
        
        .proficiency-fill {
            height: 100%;
            background: linear-gradient(90deg, #00D9FF, #7C3AED);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .skills-grid {
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
                <a href="skills.php" class="nav-link active">Skills</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Manage Skills</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Skill Form -->
        <div class="card">
            <h2><?php echo $editSkill ? 'Edit Skill' : 'Add New Skill'; ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editSkill ? 'edit' : 'add'; ?>">
                <?php if ($editSkill): ?>
                    <input type="hidden" name="id" value="<?php echo $editSkill['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Skill Name</label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?php echo htmlspecialchars($editSkill['name'] ?? ''); ?>" 
                               placeholder="e.g., React, Node.js, Python" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="Frontend Development" <?php echo ($editSkill['category'] ?? '') === 'Frontend Development' ? 'selected' : ''; ?>>Frontend Development</option>
                            <option value="Backend Development" <?php echo ($editSkill['category'] ?? '') === 'Backend Development' ? 'selected' : ''; ?>>Backend Development</option>
                            <option value="Database & Cloud" <?php echo ($editSkill['category'] ?? '') === 'Database & Cloud' ? 'selected' : ''; ?>>Database & Cloud</option>
                            <option value="Tools & Others" <?php echo ($editSkill['category'] ?? '') === 'Tools & Others' ? 'selected' : ''; ?>>Tools & Others</option>
                            <option value="Design" <?php echo ($editSkill['category'] ?? '') === 'Design' ? 'selected' : ''; ?>>Design</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="proficiency" class="form-label">Proficiency Level (0-100)</label>
                        <input type="number" id="proficiency" name="proficiency" class="form-input" 
                               value="<?php echo htmlspecialchars($editSkill['proficiency'] ?? ''); ?>" 
                               min="0" max="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number" id="display_order" name="display_order" class="form-input" 
                               value="<?php echo htmlspecialchars($editSkill['display_order'] ?? '0'); ?>" 
                               min="0">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $editSkill ? 'Update Skill' : 'Add Skill'; ?>
                </button>
                
                <?php if ($editSkill): ?>
                    <a href="skills.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Skills by Category -->
        <div class="card">
            <h2>Skills by Category</h2>
            
            <?php if (empty($skillsByCategory)): ?>
                <p style="color: #A0A0A0;">No skills found. Add your first skill above.</p>
            <?php else: ?>
                <div class="skills-grid">
                    <?php foreach ($skillsByCategory as $category => $skills): ?>
                    <div class="category-card">
                        <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                        
                        <?php foreach ($skills as $skill): ?>
                        <div class="skill-item">
                            <div>
                                <div class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></div>
                                <div class="proficiency-bar">
                                    <div class="proficiency-fill" style="width: <?php echo $skill['proficiency']; ?>%"></div>
                                </div>
                                <small style="color: #A0A0A0;">Order: <?php echo $skill['display_order']; ?></small>
                            </div>
                            
                            <div>
                                <div class="skill-proficiency"><?php echo $skill['proficiency']; ?>%</div>
                                <div class="skill-actions">
                                    <a href="skills.php?edit=<?php echo $skill['id']; ?>" class="btn btn-secondary">Edit</a>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this skill?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>