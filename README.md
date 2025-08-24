# Jake Portfolio - Modern Developer Portfolio Website

A stunning, modern portfolio website built with React frontend and PHP backend, featuring Aurora UI design with glassmorphism effects.

## 🚀 Features

### Frontend (React)
- **Modern Design**: Aurora UI with glassmorphism effects and neon accents
- **Responsive**: Mobile-first design that works on all devices
- **Interactive**: Smooth animations and hover effects
- **Accessible**: WCAG compliant with proper focus management
- **Performance**: Optimized for fast loading and smooth interactions

### Backend (PHP + MySQL)
- **Admin Panel**: Complete content management system
- **Contact Form**: Secure form processing with spam protection
- **Database**: MySQL database with proper schema design
- **Authentication**: Secure admin login with password hashing
- **API**: RESTful API endpoints for data management

### Key Sections
- **Hero Section**: Stunning animated introduction with floating elements
- **About Section**: Personal story with glassmorphism card design
- **Skills Section**: Interactive skill bars with technology categories
- **Projects Portfolio**: Filterable project showcase with live demos
- **Contact Section**: Working contact form with validation

## 🛠 Tech Stack

### Frontend
- React 19 with TypeScript
- Material-UI v7 for components
- Emotion for styled components
- Modern CSS with animations

### Backend
- PHP 8+ for server-side logic
- MySQL for database
- PDO for database connections
- Session-based authentication

## 📁 Project Structure

```
jakeportfolio/
├── src/                          # React frontend
│   ├── components/
│   │   ├── HeroSection.tsx
│   │   ├── AboutSection.tsx
│   │   ├── SkillsSection.tsx
│   │   ├── ProjectsSection.tsx
│   │   └── ContactSection.tsx
│   ├── theme.ts                  # MUI theme configuration
│   └── App.portfolio.tsx         # Main app component
├── backend/                      # PHP backend
│   ├── config/
│   │   └── database.php         # Database configuration
│   ├── api/
│   │   └── contact.php          # Contact form API
│   ├── admin/
│   │   ├── login.php            # Admin login
│   │   ├── dashboard.php        # Admin dashboard
│   │   └── logout.php           # Logout handler
│   └── database/
│       └── schema.sql           # Database schema
├── index.css                    # Global styles
└── README.md
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Node.js (for frontend development)

### Database Setup
1. Create a MySQL database named `jake_portfolio`
2. Import the schema from `backend/database/schema.sql`
3. Update database credentials in `backend/config/database.php`

### Backend Setup
1. Place the `backend` folder in your web server directory
2. Configure your web server to serve PHP files
3. Ensure proper permissions for file uploads (if needed)

### Frontend Setup
1. Install dependencies: `npm install`
2. Start development server: `npm run dev`
3. Build for production: `npm run build`

### Admin Access
- **URL**: `/backend/admin/login.php`
- **Default credentials**:
  - Username: `admin`
  - Password: `admin123`

## 🎨 Design Features

### Color Palette
- **Primary**: #00D9FF (Cyan)
- **Secondary**: #7C3AED (Purple)
- **Accent**: #FF6B6B (Red)
- **Background**: #0F0F23 (Dark Blue)
- **Surface**: #1A1A2E (Dark Purple)

### Typography
- **Headings**: Space Grotesk (Modern, technical)
- **Body**: Inter (Clean, readable)

### Visual Effects
- Glassmorphism cards with backdrop blur
- Gradient text and buttons
- Floating animation elements
- Smooth hover transitions
- Responsive grid layouts

## 📱 Responsive Design

The portfolio is fully responsive with breakpoints for:
- Mobile: 320px - 768px
- Tablet: 768px - 1024px
- Desktop: 1024px+

## 🔒 Security Features

- Password hashing for admin accounts
- SQL injection prevention with PDO
- CSRF protection for forms
- Rate limiting for contact form
- Input validation and sanitization

## 🚀 Performance Optimizations

- Optimized images with proper sizing
- CSS animations using transform/opacity
- Efficient React component structure
- Minimal bundle size with tree shaking
- Lazy loading for images

## 📊 Admin Panel Features

- **Dashboard**: Overview with statistics
- **Projects**: Add, edit, delete projects
- **Skills**: Manage skill categories and levels
- **Messages**: View and manage contact form submissions
- **Settings**: Configure site-wide settings

## 🌟 Customization

### Adding New Sections
1. Create new React component in `src/components/`
2. Add to main App component
3. Update database schema if needed
4. Add admin panel management

### Styling Changes
- Update theme in `src/theme.ts`
- Modify global styles in `index.css`
- Customize component styles using MUI's `sx` prop

### Content Management
- Use admin panel for easy content updates
- Database-driven content for easy maintenance
- File upload support for images

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📞 Support

For support or questions, please contact:
- Email: jake.developer@email.com
- GitHub: [Your GitHub Profile]

---

Built with ❤️ using modern web technologies for an exceptional user experience.# my-portfolio
# my-web
