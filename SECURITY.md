# Security Policy

## Supported Versions

Currently supported versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please email [your-email] or open a private security advisory.

**Please do not open public issues for security vulnerabilities.**

### What to Include

- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

## Security Best Practices for Users

### Configuration
- Never commit `backend/config.php` with real credentials
- Use environment-specific configuration
- Rotate secrets regularly

### Passwords
- Use strong, unique passwords (min 12 characters)
- Enable password hashing (already implemented)
- Change default admin credentials immediately

### Database
- Use dedicated database user with minimal privileges
- Never use root user for application
- Enable SSL for database connections in production

### Web Server
- Enable HTTPS (Let's Encrypt recommended)
- Set proper file permissions (644 for files, 755 for directories)
- Disable directory listing
- Keep PHP and web server updated

### Application
- Keep dependencies updated
- Monitor logs for suspicious activity
- Implement rate limiting on login endpoints
- Use CSRF tokens (already implemented)
- Validate all user input (already implemented)

## Known Security Features

- ✅ Password hashing with bcrypt
- ✅ Prepared statements (SQL injection prevention)
- ✅ CSRF token protection  
- ✅ Session management
- ✅ Input validation
- ✅ CORS configuration

## Disclosure Policy

We follow responsible disclosure:
1. Report received and acknowledged (24-48 hours)
2. Vulnerability confirmed and assessed
3. Fix developed and tested
4. Security update released
5. Public disclosure (after fix is available)
