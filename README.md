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

## Cloud Deployment

1. Set up your environment variables (see .env.example).
2. Use a secure MySQL user and password.
3. Set UPLOAD_PATH to a persistent or cloud storage location.
4. Use HTTPS in production.
5. Disable display_errors and enable error logging in php.ini for production.
6. For scaling, use a shared session store (Redis, Memcached) if needed. 