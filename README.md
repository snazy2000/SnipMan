# SnippetMan

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-blue.svg)](https://php.net)

A modern, collaborative code snippet management platform built with Laravel 12. Organize, share, and collaborate on code snippets with powerful features for individual developers and teams.

## ✨ Features

- **🔐 Secure Authentication** - User registration, login, email verification, and password management
- **👥 Team Collaboration** - Create teams, invite members, and manage permissions (Owner/Editor/Viewer)
- **📝 Rich Snippet Editor** - Monaco Editor with syntax highlighting for multiple programming languages
- **📁 Folder Organization** - Hierarchical folder structure for personal and team snippets
- **🔗 Public Sharing** - Share snippets publicly with unique URLs and view tracking
- **🤖 AI-Powered Features** - Automatic snippet descriptions using Ollama or OpenRouter
- **📊 Version Control** - Automatic versioning when content changes
- **🏷️ Tagging System** - Custom tags for better organization
- **🛠️ Admin Panel** - Comprehensive user and team management
- **🎨 Modern UI** - Responsive design with Tailwind CSS and Alpine.js

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 20+
- npm or yarn

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/snippetman.git
   cd snippetman
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional: seed with sample data
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

### Docker Setup

SnippetMan includes a complete Docker setup with separate containers for the web application and queue workers.

#### Prerequisites
- Docker and Docker Compose
- PostgreSQL database (external or via Docker)

#### Quick Start with Docker

1. **Clone and configure**
   ```bash
   git clone https://github.com/yourusername/snippetman.git
   cd snippetman
   cp .env.example .env
   ```

2. **Configure environment**
   Edit `.env` with your database settings:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://localhost:8080

   DB_CONNECTION=pgsql
   DB_HOST=your-postgres-host
   DB_PORT=5432
   DB_DATABASE=snippetman
   DB_USERNAME=your-username
   DB_PASSWORD=your-password

   QUEUE_CONNECTION=database
   ```

3. **Build and start services**
   ```bash
   # Build and start all services
   docker-compose up -d

   # Or build first, then start
   docker-compose build
   docker-compose up -d
   ```

4. **Run database migrations**
   ```bash
   docker-compose exec web php artisan key:generate
   docker-compose exec web php artisan migrate
   docker-compose exec web php artisan db:seed  # Optional
   ```

5. **Access the application**
   Open [http://localhost:8080](http://localhost:8080) in your browser

#### Docker Services

- **Web Service** (`snippetman-web`): Nginx + PHP-FPM, serves the application on port 8080
- **Queue Service** (`snippetman-queue`): Background job processing for AI features

#### Docker Commands

```bash
# View logs
docker-compose logs -f web
docker-compose logs -f queue

# Execute commands in containers
docker-compose exec web php artisan tinker
docker-compose exec web php artisan queue:work

# Stop services
docker-compose down

# Rebuild after code changes
docker-compose build --no-cache
docker-compose up -d
```

#### Pre-built Docker Images

Pre-built images are available on GitHub Container Registry:

```bash
# Pull and run pre-built images
docker pull ghcr.io/yourusername/snippetman-web:latest
docker pull ghcr.io/yourusername/snippetman-queue:latest

# Or use in docker-compose.yml
services:
  web:
    image: ghcr.io/yourusername/snippetman-web:latest
  queue:
    image: ghcr.io/yourusername/snippetman-queue:latest
```

#### Development with Docker

For development with hot reload:

```bash
# Mount source code for live changes
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Install dependencies in container
docker-compose exec web composer install
docker-compose exec web npm install
docker-compose exec web npm run dev
```

#### Production Deployment

For production:

1. **Environment variables**: Set all required environment variables
2. **Database**: Ensure PostgreSQL is available and accessible
3. **SSL/TLS**: Configure SSL certificates if needed
4. **Backups**: Set up database backups
5. **Monitoring**: Configure logging and monitoring

#### Troubleshooting

**Common issues:**

- **Port conflicts**: Change port mapping in `docker-compose.yml`
- **Database connection**: Verify PostgreSQL credentials and network access
- **Permissions**: Ensure proper file permissions for storage directories
- **Assets not loading**: Run `npm run build` and check public/build directory

**Logs:**
```bash
# Check application logs
docker-compose logs web

# Check queue worker logs
docker-compose logs queue

# Follow logs in real-time
docker-compose logs -f
```

## 🧪 Testing

Run the comprehensive test suite:
```bash
# Run all tests
php artisan test

# Run with coverage (requires PCOV)
php artisan test --coverage

# Run specific test groups
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Code Quality
```bash
# Code style checking
./vendor/bin/pint --test

# Static analysis
./vendor/bin/phpstan analyse
```

## ⚙️ Configuration

### Admin Setup
Create the first super admin user:
```bash
php artisan make:superadmin --name="Admin User" --email="admin@example.com" --password="securepassword"
```

## 📖 Usage

### Getting Started
1. Register an Admin account as above
2. Create your first snippet with syntax highlighting
3. Organize snippets with folders and tags
4. Share snippets publicly or collaborate with team members

### Team Collaboration
1. Create a team as the owner
2. Invite members via email invitations
3. Set appropriate roles (Editor, Viewer)
4. Share team snippets and folders

## 🏗️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.4)
- **Database**: PostgreSQL (configurable for MySQL/MSSQL/Sqlite)
- **Frontend**: Alpine.js + Tailwind CSS
- **Build Tool**: Vite
- **Testing**: Pest PHP
- **Code Quality**: Laravel Pint, PHPStan
- **Authentication**: Laravel Breeze
- **Job Processing**: Laravel Queues


### Development Setup
```bash
composer install
npm install
npm run dev  # For development with hot reload
```

### Code Style
```bash
# Fix code style issues
./vendor/bin/pint

# Check for style issues
./vendor/bin/pint --test
```
## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework
- [Monaco Editor](https://microsoft.github.io/monaco-editor/) - Code editing experience
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework
- [Pest](https://pestphp.com) - PHP testing framework
