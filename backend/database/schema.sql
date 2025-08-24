-- Create database
CREATE DATABASE IF NOT EXISTS jake_portfolio;
USE jake_portfolio;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    proficiency INT NOT NULL CHECK (proficiency >= 0 AND proficiency <= 100),
    icon VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    image_url VARCHAR(500),
    live_url VARCHAR(500),
    github_url VARCHAR(500),
    category VARCHAR(100) NOT NULL,
    technologies JSON,
    featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Experience table
CREATE TABLE IF NOT EXISTS experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(200) NOT NULL,
    position VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    is_current BOOLEAN DEFAULT FALSE,
    location VARCHAR(200),
    company_url VARCHAR(500),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Education table
CREATE TABLE IF NOT EXISTS education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution VARCHAR(200) NOT NULL,
    degree VARCHAR(200) NOT NULL,
    field_of_study VARCHAR(200),
    start_date DATE NOT NULL,
    end_date DATE,
    is_current BOOLEAN DEFAULT FALSE,
    grade VARCHAR(50),
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    replied BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, email, password_hash) VALUES 
('admin', 'admin@jakeportfolio.com', 'admin123');

-- Insert sample skills
INSERT INTO skills (name, category, proficiency, display_order) VALUES
('React/Next.js', 'Frontend Development', 95, 1),
('TypeScript', 'Frontend Development', 90, 2),
('JavaScript (ES6+)', 'Frontend Development', 95, 3),
('HTML5 & CSS3', 'Frontend Development', 98, 4),
('Tailwind CSS', 'Frontend Development', 85, 5),
('Node.js', 'Backend Development', 88, 6),
('PHP', 'Backend Development', 85, 7),
('Python', 'Backend Development', 80, 8),
('Express.js', 'Backend Development', 85, 9),
('RESTful APIs', 'Backend Development', 90, 10),
('MySQL', 'Database & Cloud', 90, 11),
('MongoDB', 'Database & Cloud', 85, 12),
('PostgreSQL', 'Database & Cloud', 80, 13),
('AWS', 'Database & Cloud', 75, 14),
('Docker', 'Database & Cloud', 70, 15),
('Git/GitHub', 'Tools & Others', 95, 16),
('Figma', 'Tools & Others', 85, 17),
('Webpack/Vite', 'Tools & Others', 80, 18),
('Jest/Testing', 'Tools & Others', 75, 19),
('Agile/Scrum', 'Tools & Others', 90, 20);

-- Insert sample projects
INSERT INTO projects (title, description, short_description, category, technologies, featured, display_order) VALUES
('E-Commerce Dashboard', 'Modern admin dashboard for e-commerce management with real-time analytics, inventory tracking, and order management. Built with React and Node.js backend.', 'Modern admin dashboard for e-commerce management with real-time analytics.', 'Web App', '["React", "TypeScript", "Node.js", "MongoDB"]', TRUE, 1),
('Mobile Banking App', 'Secure mobile banking application with biometric authentication, transaction history, and budget tracking features. Developed using React Native.', 'Secure mobile banking application with biometric authentication.', 'Mobile App', '["React Native", "Firebase", "Redux", "Stripe"]', TRUE, 2),
('SaaS Landing Page', 'High-converting landing page for a SaaS product with interactive animations and optimized conversion funnels. Built with Next.js and Tailwind CSS.', 'High-converting landing page for a SaaS product with interactive animations.', 'Website', '["Next.js", "Tailwind CSS", "Framer Motion", "Vercel"]', TRUE, 3);

-- Insert sample experience
INSERT INTO experience (company, position, description, start_date, end_date, is_current, location) VALUES
('Tech Innovations Inc.', 'Senior Full Stack Developer', 'Led development of multiple web applications using React, Node.js, and cloud technologies. Mentored junior developers and implemented best practices.', '2022-01-01', NULL, TRUE, 'San Francisco, CA'),
('Digital Solutions LLC', 'Full Stack Developer', 'Developed and maintained web applications using PHP, MySQL, and JavaScript. Collaborated with design team to implement responsive user interfaces.', '2020-03-01', '2021-12-31', FALSE, 'Remote'),
('StartupXYZ', 'Frontend Developer', 'Built responsive web applications using React and modern CSS frameworks. Worked closely with UX/UI designers to implement pixel-perfect designs.', '2019-06-01', '2020-02-28', FALSE, 'New York, NY');

-- Insert sample education
INSERT INTO education (institution, degree, field_of_study, start_date, end_date, grade) VALUES
('University of California, Berkeley', 'Bachelor of Science', 'Computer Science', '2015-09-01', '2019-05-31', '3.8 GPA'),
('FreeCodeCamp', 'Full Stack Web Development Certification', 'Web Development', '2018-01-01', '2018-12-31', 'Completed');

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_title', 'Jake Developer - Full Stack Developer', 'text', 'Main site title'),
('site_description', 'Professional portfolio of Jake Developer - Full Stack Developer specializing in modern web applications', 'textarea', 'Site meta description'),
('hero_title', 'Jake Developer', 'text', 'Hero section main title'),
('hero_subtitle', 'Full Stack Developer & UI/UX Designer', 'text', 'Hero section subtitle'),
('hero_description', 'I create modern, responsive web applications with cutting-edge technologies. Passionate about clean code, innovative design, and exceptional user experiences.', 'textarea', 'Hero section description'),
('contact_email', 'jake.developer@email.com', 'text', 'Contact email address'),
('contact_location', 'San Francisco, CA', 'text', 'Contact location'),
('github_url', 'https://github.com/jakedev', 'text', 'GitHub profile URL'),
('linkedin_url', 'https://linkedin.com/in/jakedev', 'text', 'LinkedIn profile URL'),
('twitter_url', 'https://twitter.com/jakedev', 'text', 'Twitter profile URL');