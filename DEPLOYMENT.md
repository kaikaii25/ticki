# Cloud Deployment Guide

## 🚀 Quick Start

### 1. Environment Setup

Create a `.env` file in the root directory:

```bash
# Database
DB_HOST=your-db-host
DB_USER=your-db-user
DB_PASS=your-secure-password
DB_NAME=nissan_tickets

# File Uploads
UPLOAD_PATH=uploads/
UPLOAD_DRIVER=local  # or 's3'

# AWS S3 (if using S3 for uploads)
S3_BUCKET=your-bucket-name
S3_REGION=us-east-1
S3_KEY=your-aws-access-key
S3_SECRET=your-aws-secret-key

# Session Storage
SESSION_DRIVER=files  # or 'redis' or 'memcached'
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Production Settings
APP_ENV=production
APP_DEBUG=false
```

### 2. Docker Deployment

```bash
# Build and start all services
docker-compose up -d

# Check health status
curl http://localhost/health.php

# View logs
docker-compose logs -f app
```

### 3. Manual Deployment

#### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Apache/Nginx
- Composer

#### Steps
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 logs/

# Create database
mysql -u root -p < nissan_tickets.sql

# Configure web server
# (See nginx.conf example below)
```

## 🔧 Configuration

### Nginx Configuration

Create `nginx.conf`:

```nginx
events {
    worker_connections 1024;
}

http {
    upstream app {
        server app:80;
    }

    server {
        listen 80;
        server_name your-domain.com;
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl http2;
        server_name your-domain.com;

        ssl_certificate /etc/nginx/ssl/cert.pem;
        ssl_certificate_key /etc/nginx/ssl/key.pem;

        location / {
            proxy_pass http://app;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }

        location /uploads/ {
            alias /var/www/html/uploads/;
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }
}
```

### Apache Configuration

Add to your virtual host:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

## 🔒 Security Checklist

- [ ] HTTPS enabled
- [ ] Strong database passwords
- [ ] Environment variables configured
- [ ] File uploads secured
- [ ] Error reporting disabled in production
- [ ] Security headers configured
- [ ] Regular backups scheduled
- [ ] SSL certificates valid
- [ ] Database connections encrypted
- [ ] Session security configured

## 📊 Monitoring

### Health Check Endpoint
```bash
curl http://your-domain.com/health.php
```

Expected response:
```json
{
    "status": "healthy",
    "timestamp": "2024-01-15 10:30:00",
    "version": "1.0.0",
    "checks": {
        "database": "healthy",
        "filesystem": "healthy",
        "sessions": "healthy",
        "memory": {
            "usage": 1048576,
            "limit": "256M",
            "status": "healthy"
        }
    }
}
```

### Log Monitoring
- Application logs: `/var/log/php_errors.log`
- Web server logs: Apache/Nginx access/error logs
- Database logs: MySQL slow query log

## 🔄 Backup Strategy

### Database Backup
```bash
# Daily backup
mysqldump -u root -p nissan_tickets > backup_$(date +%Y%m%d).sql

# Automated backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > /backups/db_$DATE.sql
gzip /backups/db_$DATE.sql
find /backups -name "db_*.sql.gz" -mtime +7 -delete
```

### File Backup
```bash
# Backup uploads directory
tar -czf uploads_$(date +%Y%m%d).tar.gz uploads/
```

## 🚀 Scaling

### Horizontal Scaling
1. Use load balancer (HAProxy, Nginx)
2. Configure shared session storage (Redis)
3. Use shared file storage (S3, NFS)
4. Database read replicas

### Vertical Scaling
1. Increase PHP memory limit
2. Optimize MySQL configuration
3. Enable OPcache
4. Use CDN for static assets

## 🛠️ Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check environment variables
   - Verify database credentials
   - Ensure database is running

2. **File Upload Issues**
   - Check upload directory permissions
   - Verify PHP upload settings
   - Check disk space

3. **Session Issues**
   - Verify session storage configuration
   - Check Redis/Memcached connectivity
   - Review session timeout settings

### Debug Mode
For troubleshooting, temporarily enable debug mode:
```bash
APP_ENV=development
APP_DEBUG=true
```

## 📈 Performance Optimization

1. **Enable OPcache**
2. **Use Redis for sessions**
3. **Implement CDN**
4. **Database indexing**
5. **Image optimization**
6. **Gzip compression**

## 🔐 SSL/TLS Setup

### Let's Encrypt
```bash
certbot --nginx -d your-domain.com
```

### Manual SSL
1. Generate CSR
2. Install certificate
3. Configure web server
4. Test SSL configuration

## 📞 Support

For deployment issues:
1. Check health endpoint
2. Review application logs
3. Verify environment configuration
4. Test database connectivity
5. Check file permissions 