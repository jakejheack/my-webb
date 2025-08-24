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
                    $technologies = json_encode(array_filter(explode(',', $_POST['technologies'])));
                    
                    $stmt = $db->prepare("INSERT INTO projects (title, description, short_description, category, technologies, live_url, github_url, featured, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['short_description'],
                        $_POST['category'],
                        $technologies,
                        $_POST['live_url'],
                        $_POST['github_url'],
                        isset($_POST['featured']) ? 1 : 0,
                        $_POST['display_order']
                    ]);
                    
                    $message = 'Project added successfully!';
                } catch (Exception $e) {
                    $error = 'Error adding project: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                try {
                    $technologies = json_encode(array_filter(explode(',', $_POST['technologies'])));
                    
                    $stmt = $db->prepare("UPDATE projects SET title = ?, description = ?, short_description = ?, category = ?, technologies = ?, live_url = ?, github_url = ?, featured = ?, display_order = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['short_description'],
                        $_POST['category'],
                        $technologies,
                        $_POST['live_url'],
                        $_POST['github_url'],
                        isset($_POST['featured']) ? 1 : 0,
                        $_POST['display_order'],
                        $_POST['id']
                    ]);
                    
                    $message = 'Project updated successfully!';
                } catch (Exception $e) {
                    $error = 'Error updating project: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("UPDATE projects SET is_active = 0 WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Project deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting project: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all projects
$stmt = $db->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY display_order, created_at DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get project for editing if ID is provided
$editProject = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND is_active = 1");
    $stmt->execute([$_GET['edit']]);
    $editProject = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Jake Portfolio Admin</title>
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
            min-height: 100px;
        }
        
        .form-checkbox {
            margin-right: 8px;
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
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .table th {
            background: rgba(0, 217, 255, 0.1);
            color: #00D9FF;
            font-weight: 600;
        }
        
        .table td {
            color: #A0A0A0;
        }
        
        .table tr:hover {
            background: rgba(255, 255, 255, 0.05);
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
        
        .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .tech-tag {
            background: rgba(0, 217, 255, 0.1);
            color: #00D9FF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .featured-badge {
            background: linear-gradient(45deg, #00FF88, #00CC6A);
            color: #000;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 8px;
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
                <a href="projects.php" class="nav-link active">Projects</a>
                <a href="skills.php" class="nav-link">Skills</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Manage Projects</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Add/Edit Project Form -->
        <div class="card">
            <h2><?php echo $editProject ? 'Edit Project' : 'Add New Project'; ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editProject ? 'edit' : 'add'; ?>">
                <?php if ($editProject): ?>
                    <input type="hidden" name="id" value="<?php echo $editProject['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label">Project Title</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               value="<?php echo htmlspecialchars($editProject['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="Web App" <?php echo ($editProject['category'] ?? '') === 'Web App' ? 'selected' : ''; ?>>Web App</option>
                            <option value="Mobile App" <?php echo ($editProject['category'] ?? '') === 'Mobile App' ? 'selected' : ''; ?>>Mobile App</option>
                            <option value="Website" <?php echo ($editProject['category'] ?? '') === 'Website' ? 'selected' : ''; ?>>Website</option>
                            <option value="API" <?php echo ($editProject['category'] ?? '') === 'API' ? 'selected' : ''; ?>>API</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="short_description" class="form-label">Short Description</label>
                    <input type="text" id="short_description" name="short_description" class="form-input" 
                           value="<?php echo htmlspecialchars($editProject['short_description'] ?? ''); ?>" 
                           placeholder="Brief one-line description">
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Full Description</label>
                    <textarea id="description" name="description" class="form-textarea" required><?php echo htmlspecialchars($editProject['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="technologies" class="form-label">Technologies (comma-separated)</label>
                    <input type="text" id="technologies" name="technologies" class="form-input" 
                           value="<?php echo $editProject ? htmlspecialchars(implode(', ', json_decode($editProject['technologies'], true) ?: [])) : ''; ?>" 
                           placeholder="React, Node.js, MongoDB, etc.">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="live_url" class="form-label">Live URL</label>
                        <input type="url" id="live_url" name="live_url" class="form-input" 
                               value="<?php echo htmlspecialchars($editProject['live_url'] ?? ''); ?>" 
                               placeholder="https://example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="github_url" class="form-label">GitHub URL</label>
                        <input type="url" id="github_url" name="github_url" class="form-input" 
                               value="<?php echo htmlspecialchars($editProject['github_url'] ?? ''); ?>" 
                               placeholder="https://github.com/username/repo">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number" id="display_order" name="display_order" class="form-input" 
                               value="<?php echo htmlspecialchars($editProject['display_order'] ?? '0'); ?>" 
                               min="0">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="featured" class="form-checkbox" 
                                   <?php echo ($editProject['featured'] ?? false) ? 'checked' : ''; ?>>
                            Featured Project
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $editProject ? 'Update Project' : 'Add Project'; ?>
                </button>
                
                <?php if ($editProject): ?>
                    <a href="projects.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Projects List -->
        <div class="card">
            <h2>All Projects</h2>
            
            <?php if (empty($projects)): ?>
                <p style="color: #A0A0A0;">No projects found. Add your first project above.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Technologies</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <strong style="color: #ffffff;"><?php echo htmlspecialchars($project['title']); ?></strong>
                                <br>
                                <small><?php echo htmlspecialchars(substr($project['short_description'], 0, 50)) . '...'; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($project['category']); ?></td>
                            <td>
                                <div class="tech-tags">
                                    <?php 
                                    $technologies = json_decode($project['technologies'], true);
                                    if ($technologies) {
                                        foreach (array_slice($technologies, 0, 3) as $tech) {
                                            echo '<span class="tech-tag">' . htmlspecialchars($tech) . '</span>';
                                        }
                                        if (count($technologies) > 3) {
                                            echo '<span class="tech-tag">+' . (count($technologies) - 3) . '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($project['featured']): ?>
                                    <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $project['display_order']; ?></td>
                            <td>
                                <a href="projects.php?edit=<?php echo $project['id']; ?>" class="btn btn-secondary" style="font-size: 0.9rem; padding: 6px 12px;">Edit</a>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="font-size: 0.9rem; padding: 6px 12px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>