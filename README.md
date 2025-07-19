# Nissan Ticketing System

A simple and easy-to-use ticketing system for Nissan, built with PHP and MySQL. This system allows users to create, manage, and track support tickets efficiently.

## Features

- User authentication (login/register)
- Role-based access control (admin/user)
- Ticket creation and management
- Ticket status tracking (open, in progress, resolved, closed)
- Priority levels (low, medium, high)
- Ticket comments
- Ticket assignment
- Search and filter tickets
- Responsive design using Bootstrap 5

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (for local development)

## Installation

1. Clone or download this repository to your web server's document root (e.g., `htdocs` for XAMPP)
2. Create a new MySQL database named `nissan_tickets`
3. Import the database schema (it will be created automatically when you first access the system)
4. Configure your web server to point to the project directory
5. Access the system through your web browser

## Default Configuration

The system uses the following default database configuration:
- Host: localhost
- Username: root
- Password: (empty)
- Database: nissan_tickets

You can modify these settings in `config/database.php` if needed.

## Usage

1. Register a new account or login with existing credentials
2. Create new tickets with title, description, and priority
3. View and manage tickets in the dashboard
4. Add comments and update ticket status
5. Search and filter tickets as needed

## Admin Features

Administrators have additional capabilities:
- View all tickets
- Assign tickets to users
- Manage user accounts
- Access advanced statistics

## Security Features

- Password hashing
- SQL injection prevention
- XSS protection
- Input sanitization
- Session management

## Future Development

The system is designed to be easily extensible. Some planned features include:
- Email notifications
- File attachments
- Advanced reporting
- API integration
- Custom ticket categories
- SLA tracking

## Contributing

Feel free to submit issues and enhancement requests!

## License

This project is licensed under the MIT License - see the LICENSE file for details. 

## 🚀 Cloud Deployment

The system is **production-ready** for cloud deployment with the following features:

### ✅ Cloud-Ready Features
- **Environment-based configuration** with `.env` support
- **Docker & Docker Compose** ready
- **Health check endpoint** (`/health.php`)
- **Security headers** and HTTPS enforcement
- **S3 file upload support**
- **Redis session storage** support
- **Production error handling**
- **Comprehensive logging**

### 🚀 Quick Deployment

#### Option 1: Docker (Recommended)
```bash
# Clone and setup
git clone <repository>
cd ticki
cp env.example .env
# Edit .env with your settings

# Deploy
docker-compose up -d

# Check health
curl http://localhost/health.php
```

#### Option 2: Manual Deployment
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Configure environment
cp env.example .env
# Edit .env with production settings

# Set permissions
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 logs/

# Deploy to web server
```

### 📋 Deployment Checklist
- [ ] Environment variables configured
- [ ] Database credentials secured
- [ ] HTTPS/SSL enabled
- [ ] File uploads secured
- [ ] Error reporting disabled
- [ ] Regular backups scheduled
- [ ] Monitoring configured

### 📖 Detailed Guide
See [DEPLOYMENT.md](DEPLOYMENT.md) for comprehensive deployment instructions.

### 🔧 Supported Platforms
- **AWS** (EC2, RDS, S3)
- **Google Cloud** (Compute Engine, Cloud SQL)
- **Azure** (App Service, SQL Database)
- **DigitalOcean** (Droplets, Managed Databases)
- **Heroku** (with ClearDB addon)
- **VPS/Shared Hosting** (with manual setup) 

## Cloud Session Storage (Redis/Memcached)

For scalable sessions in the cloud, configure PHP to use Redis or Memcached as the session handler. Example for Docker or cloud hosting:

### Redis (recommended)

1. Add to your Dockerfile or install Redis on your server.
2. In your php.ini or Docker environment, set:

```
session.save_handler = redis
session.save_path = "tcp://your-redis-host:6379"
```

3. For Docker Compose, you can add a Redis service and link it to your PHP container.

### Memcached

1. Install Memcached and the PHP memcached extension.
2. In your php.ini or Docker environment, set:

```
session.save_handler = memcached
session.save_path = "your-memcached-host:11211"
```

**Note:**
- Make sure your cloud provider allows network access between your app and the session store.
- For AWS, you can use ElastiCache (Redis or Memcached) as the backend. 

## Background Jobs & Queue

This system includes a simple file-based queue for background jobs (e.g., sending emails, generating reports).

### Usage
- To queue a job: `queueJob('send_email', ['to' => $to, 'subject' => $subject, 'body' => $body]);`
- Jobs are stored as JSON files in `logs/queue/`.

### Processing Jobs
- Create a CLI script or cron job to read and process jobs from the `logs/queue` directory.
- For each job, perform the required action (e.g., send the email) and then delete the job file.

### Production Recommendation
- For scalable production use, switch to a real queue system (Redis, RabbitMQ, AWS SQS, etc.).
- Use a worker process or service to process jobs asynchronously. 

## Accessibility & UX

This system aims to be user-friendly and accessible. Here are some best practices and recommendations:

- Use semantic HTML elements (e.g., <button>, <nav>, <main>, <form>, <label>, <input>).
- Ensure all form fields have associated <label> elements.
- Use ARIA attributes where appropriate (e.g., aria-label, aria-live for notifications).
- Maintain sufficient color contrast for text and UI elements (use tools like WebAIM Contrast Checker).
- Ensure all interactive elements are keyboard accessible (tab order, focus states).
- Test with screen readers (NVDA, VoiceOver, JAWS).
- Use responsive design for mobile and tablet users.
- Add skip-to-content links for easier navigation.
- Use alt text for all images and icons.

### Tools for Testing
- [axe DevTools](https://www.deque.com/axe/devtools/)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)
- [WAVE](https://wave.webaim.org/)

### Quick Wins
- Review your forms and navigation for keyboard and screen reader accessibility.
- Check color contrast and font sizes.
- Test on mobile devices for touch usability. 

## Backup & Disaster Recovery

To ensure business continuity, regularly back up your database and uploaded files.

### Database Backup
- Use `mysqldump` or your cloud provider's backup tools to export the database regularly.
- Example: `mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup.sql`
- Automate backups with cron jobs or cloud backup services.

### File Backup
- Back up the `uploads/` directory (or your cloud storage bucket) regularly.
- Use tools like `rsync`, cloud storage snapshots, or managed backup solutions.

### Restore Procedures
- To restore the database: `mysql -u $DB_USER -p$DB_PASS $DB_NAME < backup.sql`
- To restore files: copy the backup files back to the `uploads/` directory or cloud bucket.

### Recommendations
- Store backups in a secure, offsite location.
- Test your restore process periodically.
- For cloud deployments, use managed backup and snapshot features. 