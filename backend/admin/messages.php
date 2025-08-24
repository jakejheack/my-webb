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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                try {
                    $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Message marked as read!';
                } catch (Exception $e) {
                    $error = 'Error updating message: ' . $e->getMessage();
                }
                break;
                
            case 'mark_replied':
                try {
                    $stmt = $db->prepare("UPDATE contact_messages SET replied = 1 WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Message marked as replied!';
                } catch (Exception $e) {
                    $error = 'Error updating message: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = 'Message deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting message: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filters
$whereClause = "WHERE 1=1";
$params = [];

if ($filter === 'unread') {
    $whereClause .= " AND is_read = 0";
} elseif ($filter === 'read') {
    $whereClause .= " AND is_read = 1";
} elseif ($filter === 'replied') {
    $whereClause .= " AND replied = 1";
}

if ($search) {
    $whereClause .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

// Get messages with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT * FROM contact_messages $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countStmt = $db->prepare("SELECT COUNT(*) FROM contact_messages $whereClause");
$countStmt->execute($params);
$totalMessages = $countStmt->fetchColumn();
$totalPages = ceil($totalMessages / $perPage);

// Get statistics
$stats = [];
$stats['total'] = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$stats['unread'] = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
$stats['replied'] = $db->query("SELECT COUNT(*) FROM contact_messages WHERE replied = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Jake Portfolio Admin</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #A0A0A0;
            font-size: 0.9rem;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid rgba(0, 217, 255, 0.4);
            background: transparent;
            color: #A0A0A0;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
            border: none;
        }
        
        .search-box {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.4);
            border-radius: 20px;
            color: #ffffff;
            font-family: inherit;
            width: 250px;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #00D9FF;
        }
        
        .message-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .message-item:hover {
            border-color: rgba(0, 217, 255, 0.3);
        }
        
        .message-item.unread {
            border-left: 4px solid #00D9FF;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .message-sender {
            flex: 1;
        }
        
        .sender-name {
            font-weight: 600;
            color: #00D9FF;
            font-size: 1.1rem;
        }
        
        .sender-email {
            color: #A0A0A0;
            font-size: 0.9rem;
        }
        
        .message-meta {
            text-align: right;
            color: #A0A0A0;
            font-size: 0.9rem;
        }
        
        .message-subject {
            font-weight: 600;
            color: #ffffff;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .message-content {
            color: #A0A0A0;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #A0A0A0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #00FF88, #00CC6A);
            color: #000;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #FF6B6B, #FF8E8E);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .status-badges {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-unread {
            background: rgba(0, 217, 255, 0.2);
            color: #00D9FF;
        }
        
        .badge-replied {
            background: rgba(0, 255, 136, 0.2);
            color: #00FF88;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 6px;
            color: #A0A0A0;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover,
        .pagination a.active {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
            border: none;
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
        
        @media (max-width: 768px) {
            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .message-meta {
                text-align: left;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
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
                <a href="messages.php" class="nav-link active">Messages</a>
                <a href="settings.php" class="nav-link">Settings</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Contact Messages</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['replied']; ?></div>
                <div class="stat-label">Replied Messages</div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="card">
            <div class="filters">
                <a href="messages.php?filter=all&search=<?php echo urlencode($search); ?>" 
                   class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="messages.php?filter=unread&search=<?php echo urlencode($search); ?>" 
                   class="filter-btn <?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread</a>
                <a href="messages.php?filter=read&search=<?php echo urlencode($search); ?>" 
                   class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
                <a href="messages.php?filter=replied&search=<?php echo urlencode($search); ?>" 
                   class="filter-btn <?php echo $filter === 'replied' ? 'active' : ''; ?>">Replied</a>
                
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <input type="text" name="search" class="search-box" 
                           placeholder="Search messages..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        
        <!-- Messages List -->
        <div class="card">
            <?php if (empty($messages)): ?>
                <p style="color: #A0A0A0; text-align: center; padding: 40px;">No messages found.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <div class="message-item <?php echo !$msg['is_read'] ? 'unread' : ''; ?>">
                    <div class="message-header">
                        <div class="message-sender">
                            <div class="sender-name"><?php echo htmlspecialchars($msg['name']); ?></div>
                            <div class="sender-email"><?php echo htmlspecialchars($msg['email']); ?></div>
                        </div>
                        <div class="message-meta">
                            <div><?php echo date('M j, Y', strtotime($msg['created_at'])); ?></div>
                            <div><?php echo date('g:i A', strtotime($msg['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="status-badges">
                        <?php if (!$msg['is_read']): ?>
                            <span class="badge badge-unread">Unread</span>
                        <?php endif; ?>
                        <?php if ($msg['replied']): ?>
                            <span class="badge badge-replied">Replied</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                    <div class="message-content"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                    
                    <div class="message-actions">
                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" 
                           class="btn btn-primary">Reply</a>
                        
                        <?php if (!$msg['is_read']): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-secondary">Mark as Read</button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if (!$msg['replied']): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_replied">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-success">Mark as Replied</button>
                        </form>
                        <?php endif; ?>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="messages.php?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="messages.php?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="messages.php?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filter); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>