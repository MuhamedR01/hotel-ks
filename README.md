# Hotel KS - E-commerce Platform

A modern e-commerce platform built with PHP backend and React frontend for hotel merchandise and services.

## ⚠️ Important Security Notice

**This repository does NOT contain sensitive credentials or configuration.**

You MUST configure your own:
- Database credentials
- Admin account credentials  
- API keys and secrets
- Domain configuration

Never commit files containing real credentials to version control.

## 🚀 Quick Start

See [SETUP.md](SETUP.md) for complete installation and configuration instructions.

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL/MariaDB** - Database
- **RESTful API** - Communication layer

### Frontend  
- **React** - UI framework
- **JavaScript** - Programming language
- **Vite** - Build tool
- **Tailwind CSS** - Styling (if applicable)

## 📁 Project Structure

```
hotel-ks/
├── backend/           # PHP backend API
│   ├── config.example.php
│   ├── init.php
│   ├── db.php
│   └── *.php         # API endpoints
├── frontend/          # React frontend
│   ├── src/
│   ├── public/
│   └── package.json
├── dashboard/         # Admin dashboard
│   └── *.php
├── database/          # Database schema
│   └── schema.sql
└── SETUP.md          # Setup instructions
```

## 🔒 Security Features

- Password hashing with bcrypt
- Prepared statements (SQL injection prevention)
- CSRF token protection
- Session management
- CORS configuration
- Input validation and sanitization

## 📝 Configuration Files

The following files need to be created from templates:

1. `backend/config.php` (copy from `config.example.php`)
2. `frontend/.env` (create based on `.env.example`)

**These files are in .gitignore and will never be committed.**

## 🔐 Security Best Practices

Before deploying to production:

- [ ] Change all default credentials
- [ ] Use strong, unique passwords
- [ ] Enable HTTPS
- [ ] Configure proper file permissions
- [ ] Set secure CORS policies
- [ ] Regular security updates
- [ ] Enable error logging (not display)
- [ ] Backup database regularly

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

[Add your license here]

## 👤 Author

**MuhamedR01**

## 🐛 Issues

Found a bug? Please open an issue: [GitHub Issues](https://github.com/MuhamedR01/hotel-ks/issues)

---

**Note**: This is a clean repository ready for public release. All sensitive information has been removed.
