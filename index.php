<?php
require_once 'backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get site settings
    $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Get skills grouped by category
    $stmt = $db->query("SELECT * FROM skills WHERE is_active = 1 ORDER BY category, display_order");
    $skills = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $skills[$row['category']][] = $row;
    }
    
    // Get featured projects
    $stmt = $db->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY display_order LIMIT 6");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Error loading data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_title'] ?? 'Jake Developer - Full Stack Developer'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? 'Professional portfolio of Jake Developer'); ?>">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0F0F23;
            color: #ffffff;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1A1A2E;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            border-radius: 4px;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Section styles */
        .section {
            padding: 100px 0;
            position: relative;
        }
        
        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.75rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .section-subtitle {
            text-align: center;
            color: #A0A0A0;
            font-size: 1.2rem;
            margin-bottom: 60px;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #0F0F23 0%, #1A1A2E 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 20%, rgba(0, 217, 255, 0.2) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(124, 58, 237, 0.2) 0%, transparent 50%);
            z-index: 1;
        }
        
        .floating-element {
            position: absolute;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, rgba(0, 217, 255, 0.3), rgba(124, 58, 237, 0.3));
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 217, 255, 0.4);
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .floating-element:nth-child(2) { top: 20%; right: 15%; animation-delay: 2s; }
        .floating-element:nth-child(3) { bottom: 15%; left: 20%; animation-delay: 4s; }
        .floating-element:nth-child(4) { bottom: 25%; right: 10%; animation-delay: 1s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .hero-text h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        
        .hero-text .subtitle {
            color: #00D9FF;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .hero-text .role {
            font-size: 1.75rem;
            color: #A0A0A0;
            font-weight: 400;
            margin-bottom: 30px;
        }
        
        .hero-text .description {
            font-size: 1.2rem;
            color: #A0A0A0;
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 500px;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 32px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
            animation: glow 2s ease-in-out infinite;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 217, 255, 0.3);
        }
        
        .btn-outline {
            border: 1px solid #00D9FF;
            color: #00D9FF;
            background: transparent;
        }
        
        .btn-outline:hover {
            background: #00D9FF;
            color: #0F0F23;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(0, 217, 255, 0.3); }
            50% { box-shadow: 0 0 40px rgba(0, 217, 255, 0.6); }
        }
        
        .hero-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .hero-circle {
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, rgba(0, 217, 255, 0.2), rgba(124, 58, 237, 0.2));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(20px);
            border: 2px solid rgba(0, 217, 255, 0.4);
            position: relative;
            animation: float 8s ease-in-out infinite;
        }
        
        .hero-circle i {
            font-size: 4rem;
            color: #00D9FF;
        }
        
        /* About Section */
        .about {
            background: #1A1A2E;
        }
        
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .about-image img {
            width: 300px;
            height: 400px;
            object-fit: cover;
            border-radius: 20px;
            border: 3px solid rgba(0, 217, 255, 0.4);
            box-shadow: 0 20px 40px rgba(0, 217, 255, 0.3);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 20px;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #00D9FF, #7C3AED);
        }
        
        .about-text h3 {
            color: #00D9FF;
            font-size: 1.75rem;
            margin-bottom: 20px;
        }
        
        .about-text p {
            color: #ffffff;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .about-features {
            margin-top: 30px;
        }
        
        .about-features h4 {
            color: #7C3AED;
            margin-bottom: 15px;
        }
        
        .about-features ul {
            list-style: none;
        }
        
        .about-features li {
            color: #A0A0A0;
            margin-bottom: 8px;
            position: relative;
            padding-left: 20px;
        }
        
        .about-features li::before {
            content: '▶';
            color: #00D9FF;
            position: absolute;
            left: 0;
        }
        
        /* Skills Section */
        .skills {
            background: linear-gradient(135deg, #0F0F23 0%, #1A1A2E 100%);
        }
        
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .skill-category {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 16px;
            padding: 30px;
            transition: all 0.3s ease;
        }
        
        .skill-category:hover {
            transform: translateY(-5px);
            border-color: rgba(0, 217, 255, 0.6);
            box-shadow: 0 20px 40px rgba(0, 217, 255, 0.2);
        }
        
        .skill-category h3 {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .skill-item {
            margin-bottom: 20px;
        }
        
        .skill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .skill-name {
            color: #ffffff;
            font-weight: 500;
        }
        
        .skill-percentage {
            color: #00D9FF;
            font-weight: 600;
        }
        
        .skill-bar {
            height: 8px;
            background: #343A40;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .skill-progress {
            height: 100%;
            background: linear-gradient(90deg, #00D9FF, #7C3AED);
            border-radius: 4px;
            transition: width 2s ease;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Projects Section */
        .projects {
            background: #1A1A2E;
        }
        
        .project-filters {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 20px;
            border-radius: 25px;
            border: 1px solid rgba(0, 217, 255, 0.4);
            background: transparent;
            color: #A0A0A0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: linear-gradient(45deg, #00D9FF, #7C3AED);
            color: white;
            border: none;
        }
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .project-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.2);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .project-card:hover {
            transform: translateY(-10px);
            border-color: rgba(0, 217, 255, 0.6);
            box-shadow: 0 30px 60px rgba(0, 217, 255, 0.3);
        }
        
        .project-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .project-card:hover .project-image {
            transform: scale(1.05);
        }
        
        .project-content {
            padding: 30px;
        }
        
        .project-title {
            color: #00D9FF;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .project-description {
            color: #A0A0A0;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .project-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .tech-tag {
            background: rgba(0, 217, 255, 0.1);
            color: #00D9FF;
            border: 1px solid rgba(0, 217, 255, 0.3);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        
        .project-links {
            display: flex;
            gap: 15px;
        }
        
        .project-link {
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .project-link.demo {
            border: 1px solid #00D9FF;
            color: #00D9FF;
        }
        
        .project-link.demo:hover {
            background: #00D9FF;
            color: #0F0F23;
        }
        
        .project-link.code {
            border: 1px solid #7C3AED;
            color: #7C3AED;
        }
        
        .project-link.code:hover {
            background: #7C3AED;
            color: white;
        }
        
        /* Contact Section */
        .contact {
            background: linear-gradient(135deg, #0F0F23 0%, #1A1A2E 100%);
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
        }
        
        .contact-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 20px;
            padding: 40px;
        }
        
        .contact-form h3 {
            color: #00D9FF;
            font-size: 1.75rem;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #A0A0A0;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
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
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00D9FF;
            box-shadow: 0 0 0 3px rgba(0, 217, 255, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .contact-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 217, 255, 0.3);
            border-radius: 20px;
            padding: 30px;
        }
        
        .contact-card h3 {
            color: #00D9FF;
            margin-bottom: 20px;
        }
        
        .contact-item {
            margin-bottom: 20px;
        }
        
        .contact-item h4 {
            color: #ffffff;
            margin-bottom: 5px;
        }
        
        .contact-item p {
            color: #A0A0A0;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .social-link {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 217, 255, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00D9FF;
            font-size: 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background: #00D9FF;
            color: #0F0F23;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 217, 255, 0.4);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content,
            .about-content,
            .contact-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .hero-circle {
                width: 300px;
                height: 300px;
            }
            
            .about-image {
                text-align: center;
            }
            
            .hero-buttons {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .section {
                padding: 60px 0;
            }
            
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .btn {
                padding: 10px 24px;
                font-size: 1rem;
            }
        }
        
        /* Loading animation */
        .loading {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .loading.loaded {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Success message */
        .success-message {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            color: #00FF88;
            text-align: center;
        }
        
        .error-message {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            color: #FF6B6B;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="subtitle">Hello, I'm</div>
                    <h1><?php echo htmlspecialchars($settings['hero_title'] ?? 'Jake Developer'); ?></h1>
                    <div class="role"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? 'Full Stack Developer & UI/UX Designer'); ?></div>
                    <p class="description">
                        <?php echo htmlspecialchars($settings['hero_description'] ?? 'I create modern, responsive web applications with cutting-edge technologies. Passionate about clean code, innovative design, and exceptional user experiences.'); ?>
                    </p>
                    <div class="hero-buttons">
                        <a href="#projects" class="btn btn-primary">View My Work</a>
                        <a href="#contact" class="btn btn-outline">Contact Me</a>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="hero-circle">
                        <i class="fas fa-code"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about section" id="about">
        <div class="container">
            <h2 class="section-title">About Me</h2>
            <p class="section-subtitle">Passionate developer with a love for creating amazing digital experiences</p>
            
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1551033406-611cf9a28f67?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTAwNDR8MHwxfHNlYXJjaHwyfHxwcm9ncmFtbWVyJTIwZGV2ZWxvcGVyJTIwY29tcHV0ZXIlMjBjb2Rpbmd8ZW58MHwxfHx8MTc1NTYzMjU0OXww&ixlib=rb-4.1.0&q=85" 
                         alt="Professional programmer developer working on computer coding - Markus Spiske on Unsplash"
                         style="width: 300px; height: 400px;">
                </div>
                
                <div class="about-text">
                    <div class="glass-card">
                        <h3>My Journey</h3>
                        <p>
                            I'm a passionate full-stack developer with over 5 years of experience creating 
                            innovative web applications. My journey began with a curiosity about how websites 
                            work, and it has evolved into a deep love for crafting digital experiences that 
                            make a difference.
                        </p>
                        <p>
                            I specialize in modern JavaScript frameworks, cloud technologies, and user-centered 
                            design. When I'm not coding, you'll find me exploring new technologies, contributing 
                            to open-source projects, or sharing knowledge with the developer community.
                        </p>
                        
                        <div class="about-features">
                            <h4>What I Bring:</h4>
                            <ul>
                                <li>Clean, maintainable code architecture</li>
                                <li>User-focused design thinking</li>
                                <li>Agile development methodologies</li>
                                <li>Continuous learning mindset</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section class="skills section" id="skills">
        <div class="container">
            <h2 class="section-title">Skills & Technologies</h2>
            <p class="section-subtitle">Technologies I work with to bring ideas to life</p>
            
            <div class="skills-grid">
                <?php foreach ($skills as $category => $categorySkills): ?>
                <div class="skill-category">
                    <h3><?php echo htmlspecialchars($category); ?></h3>
                    <?php foreach ($categorySkills as $skill): ?>
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                            <span class="skill-percentage"><?php echo $skill['proficiency']; ?>%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" data-width="<?php echo $skill['proficiency']; ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 60px;">
                <h3 style="color: #A0A0A0; margin-bottom: 10px;">Always Learning</h3>
                <p style="color: #A0A0A0;">Currently exploring: AI/ML, Web3, and Advanced Cloud Architecture</p>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="projects section" id="projects">
        <div class="container">
            <h2 class="section-title">Featured Projects</h2>
            <p class="section-subtitle">A showcase of my recent work and creative solutions</p>
            
            <div class="project-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="Web App">Web App</button>
                <button class="filter-btn" data-filter="Mobile App">Mobile App</button>
                <button class="filter-btn" data-filter="Website">Website</button>
            </div>
            
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project-card" data-category="<?php echo htmlspecialchars($project['category']); ?>">
                    <img src="<?php echo $project['category'] === 'Web App' ? 'https://images.unsplash.com/photo-1486927181919-3ac1fc3a8082?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTAwNDR8MHwxfHNlYXJjaHw1fHxkYXNoYm9hcmQlMjB3ZWIlMjBhcHBsaWNhdGlvbiUyMGludGVyZmFjZXxlbnwwfDB8fGJsdWV8MTc1NTYzMjU0OXww&ixlib=rb-4.1.0&q=85' : ($project['category'] === 'Mobile App' ? 'https://images.unsplash.com/photo-1551721434-8b94ddff0e6d?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTAwNDR8MHwxfHNlYXJjaHwyfHxtb2JpbGUlMjBhcHAlMjBpbnRlcmZhY2UlMjBkZXNpZ258ZW58MHwxfHxwdXJwbGV8MTc1NTU3NjYyN3ww&ixlib=rb-4.1.0&q=85' : 'https://images.unsplash.com/photo-1629363447922-1f421b470828?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTAwNDR8MHwxfHNlYXJjaHw2fHxlY29tbWVyY2UlMjB3ZWJzaXRlJTIwc2hvcHBpbmd8ZW58MHwwfHxncmVlbnwxNzU1NjMyNTQ5fDA&ixlib=rb-4.1.0&q=85'); ?>" 
                         alt="<?php echo htmlspecialchars($project['title']); ?> - Project Screenshot" 
                         class="project-image"
                         style="width: 100%; height: 250px;">
                    
                    <div class="project-content">
                        <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                        
                        <div class="project-tech">
                            <?php 
                            $technologies = json_decode($project['technologies'], true);
                            if ($technologies) {
                                foreach ($technologies as $tech) {
                                    echo '<span class="tech-tag">' . htmlspecialchars($tech) . '</span>';
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="project-links">
                            <?php if ($project['live_url']): ?>
                            <a href="<?php echo htmlspecialchars($project['live_url']); ?>" class="project-link demo" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Live Demo
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($project['github_url']): ?>
                            <a href="<?php echo htmlspecialchars($project['github_url']); ?>" class="project-link code" target="_blank">
                                <i class="fab fa-github"></i> Code
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact section" id="contact">
        <div class="container">
            <h2 class="section-title">Let's Work Together</h2>
            <p class="section-subtitle">Have a project in mind? Let's discuss how we can bring your ideas to life</p>
            
            <div class="contact-content">
                <div class="contact-form">
                    <h3>Send Message</h3>
                    
                    <div id="message-container"></div>
                    
                    <form id="contactForm">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>
                
                <div class="contact-info">
                    <div class="contact-card">
                        <h3>Get In Touch</h3>
                        
                        <div class="contact-item">
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($settings['contact_email'] ?? 'jake.developer@email.com'); ?></p>
                        </div>
                        
                        <div class="contact-item">
                            <h4>Location</h4>
                            <p><?php echo htmlspecialchars($settings['contact_location'] ?? 'San Francisco, CA'); ?></p>
                        </div>
                        
                        <div class="contact-item">
                            <h4>Response Time</h4>
                            <p>Usually within 24 hours</p>
                        </div>
                    </div>
                    
                    <div class="contact-card">
                        <h3>Connect With Me</h3>
                        
                        <div class="social-links">
                            <a href="mailto:<?php echo htmlspecialchars($settings['contact_email'] ?? 'jake.developer@email.com'); ?>" class="social-link">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars($settings['linkedin_url'] ?? '#'); ?>" class="social-link" target="_blank">
                                <i class="fab fa-linkedin"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars($settings['github_url'] ?? '#'); ?>" class="social-link" target="_blank">
                                <i class="fab fa-github"></i>
                            </a>
                        </div>
                        
                        <p style="text-align: center; margin-top: 20px; color: #A0A0A0;">
                            Follow me on social media for updates and insights
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animate skill bars on scroll
        function animateSkillBars() {
            const skillBars = document.querySelectorAll('.skill-progress');
            skillBars.forEach(bar => {
                const rect = bar.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    const width = bar.getAttribute('data-width');
                    bar.style.width = width + '%';
                }
            });
        }

        // Project filtering
        const filterButtons = document.querySelectorAll('.filter-btn');
        const projectCards = document.querySelectorAll('.project-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const filter = button.getAttribute('data-filter');

                projectCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 100);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Contact form submission
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const messageContainer = document.getElementById('message-container');
            
            try {
                const response = await fetch('backend/api/contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageContainer.innerHTML = '<div class="success-message">' + result.message + '</div>';
                    this.reset();
                } else {
                    messageContainer.innerHTML = '<div class="error-message">' + result.error + '</div>';
                }
            } catch (error) {
                messageContainer.innerHTML = '<div class="error-message">Error sending message. Please try again.</div>';
            }
        });

        // Scroll animations
        function handleScrollAnimations() {
            const elements = document.querySelectorAll('.loading');
            elements.forEach(element => {
                const rect = element.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    element.classList.add('loaded');
                }
            });
            
            animateSkillBars();
        }

        // Event listeners
        window.addEventListener('scroll', handleScrollAnimations);
        window.addEventListener('load', () => {
            handleScrollAnimations();
            // Initialize skill bars
            setTimeout(animateSkillBars, 500);
        });

        // Add loading class to elements for animation
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.add('loading');
            });
        });
    </script>
</body>
</html>