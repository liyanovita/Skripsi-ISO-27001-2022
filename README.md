# OpenAudit-27001 🛡️

OpenAudit-27001 is a modern, AI-powered compliance dashboard tailored specifically for ISO 27001:2022 audits. Designed for agility and high-density information display, it provides a unified platform to assess, track, and improve organizational information security management systems (ISMS).

## 🚀 Features

### 1. AI Recommendation Engine
Leverages intelligent logic to analyze your assessment inputs and provide:
- **Evidence Validation Assistance:** Validates your audit notes to ensure they align with ISO control requirements.
- **Risk Severity Classification:** Categorizes identified gaps into Critical, High, Medium, or Low risks based on maturity ratings.
- **Suggested Implementation Priority:** Prioritizes corrective actions (Priority 1-4) to help focus on high-impact areas.

### 2. Advanced Reporting System
- **Historical Assessment Comparison:** Compare your latest assessment against previous sessions domain-by-domain (Policies, People, Physical, Technology).
- **Downloadable Improvement Roadmap:** Export a structured PDF or Excel roadmap prioritized by risk severity and target completion timelines (30/60/90/180 days).
- **Unified Intelligence Hub:** Visualize your compliance status using radar charts and interactive dashboards.

### 3. Open Collaboration & Community Hub
- **Shared Implementation Reference:** Access and clone ISO 27001 compliance templates shared by experts across different industries (e.g., Fintech, Healthcare, Cloud).
- **Community Star Rating:** Rate community templates out of 5 stars to help others discover the best resources.
- **Knowledge Base:** Built-in ISO 27001 references to guide your audit process.

### 4. RESTful API (NEW!)
- **46+ API Endpoints:** Complete REST API for all system functionality
- **Token Authentication:** Secure authentication using Laravel Sanctum
- **OpenAPI Documentation:** Interactive Swagger UI documentation
- **Mobile & Integration Ready:** Build mobile apps or integrate with third-party tools
- **Webhook Support:** N8N workflow integration for automation
- **Rate Limiting:** Built-in protection against abuse

### 5. Enterprise-Grade Features
- **Comprehensive Logging:** 8 log channels with automatic rotation
- **Performance Monitoring:** Track response times and system health
- **Rate Limiting:** Protect against brute force and DDoS attacks
- **Caching Strategy:** Intelligent caching for 25-40% faster responses
- **Database Optimization:** 30+ indexes for optimal query performance
- **Audit Trail:** Complete activity logging for compliance

### 6. Seamless User Experience
- **Lightweight User Profile:** Track your audit history and statistics seamlessly.
- **Ultra-Compact High-Density UI:** Designed for rapid data entry and maximum visibility using Tailwind CSS.
- **Hotwire Turbo Integration:** SPA-like performance without the heavy Javascript overhead.
- **Telegram Notifications:** Real-time CAPA reminders via Telegram

## 🛠️ Technology Stack

### Backend
- **Framework:** Laravel 11
- **Database:** SQLite (default) / MySQL
- **Authentication:** Laravel Sanctum (API tokens)
- **API Documentation:** L5 Swagger (OpenAPI 3.0)
- **Reports:** Barryvdh DomPDF, Maatwebsite Excel

### Frontend
- **CSS Framework:** Tailwind CSS
- **JavaScript:** Alpine.js, Chart.js
- **SPA-like:** Hotwire Turbo
- **Icons:** Heroicons

### Architecture
- **Design Pattern:** Service Layer, Repository Pattern
- **Code Organization:** Domain-Driven Structure
- **Caching:** Redis/File-based caching
- **Logging:** Multi-channel logging system
- **Rate Limiting:** Custom rate limiter service

## 📦 Installation

### Quick Start

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/audit-iso27001.git
cd audit-iso27001
```

2. **Install dependencies**
```bash
composer install
npm install
npm run build
```

3. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Setup & Seeding**
```bash
php artisan migrate:fresh --seed
```

5. **Run the application**
```bash
php artisan serve
```

6. **Access the application**
- Web Interface: `http://localhost:8000`
- API Documentation: `http://localhost:8000/api/documentation` (after setup)
- API Health Check: `http://localhost:8000/api/health`

### API Setup (Optional)

To enable API functionality:

```bash
# Install Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\ServiceProvider"

# Generate API documentation
php artisan l5-swagger:generate

# Test API
curl http://localhost:8000/api/health
```

### N8N Integration (Optional)

For automated CAPA reminders via Telegram:

1. Import workflow: `n8n-capa-reminder-workflow.json`
2. Configure Telegram bot credentials
3. Update webhook URL in workflow
4. Activate workflow

See `N8N_CAPA_REMINDER_GUIDE.md` for detailed instructions.

## 📚 Documentation

Comprehensive documentation is available:

- **API Documentation:** `API_DOCUMENTATION.md`
- **API Quick Start:** `API_QUICK_START.md`
- **Logging Guide:** `LOGGING_GUIDE.md`
- **Rate Limiting Guide:** `RATE_LIMITING_GUIDE.md`
- **Database Optimization:** `DATABASE_OPTIMIZATION_GUIDE.md`
- **N8N Integration:** `N8N_CAPA_REMINDER_GUIDE.md`
- **Implementation Summary:** `FINAL_IMPLEMENTATION_SUMMARY.md`

## 🔧 Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME="ISO 27001 Audit System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=audit_iso27001
DB_USERNAME=root
DB_PASSWORD=

# Telegram Notifications
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

# Rate Limiting
RATE_LIMIT_API=60
RATE_LIMIT_AUTH=5
RATE_LIMIT_WEBHOOK=100

# Caching
CACHE_DRIVER=redis
CACHE_TTL=900
```

## 🚀 Performance

The system has been optimized for production use:

- **Response Time:** 25-40% faster with caching
- **Database Queries:** 30-40% reduction with eager loading
- **Code Quality:** 60-85% smaller controllers
- **Security:** Comprehensive rate limiting and validation
- **Monitoring:** Full audit trail and performance tracking

## 🔒 Security Features

- **Authentication:** Token-based API authentication
- **Authorization:** Role-based access control
- **Rate Limiting:** Protection against brute force and DDoS
- **Input Validation:** Comprehensive validation on all inputs
- **Audit Logging:** Complete activity tracking
- **Security Events:** Automatic logging of security-related events

## 📊 API Endpoints

The system provides 46+ API endpoints:

- **Authentication:** Login, register, logout, profile
- **Assessment Sessions:** CRUD operations, clone, finalize
- **Assessment Results:** Update, AI insights, status
- **Community Templates:** Share, rate, clone, use
- **Intelligence:** Dashboard, analytics, reports
- **Compliance:** SoA workspace, exports
- **Knowledge Base:** Resources management
- **Webhooks:** N8N integration, notifications

See `API_DOCUMENTATION.md` for complete endpoint reference.

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ApiHealthTest

# Run with coverage
php artisan test --coverage
```

## 🛠️ Management Commands

Useful artisan commands:

```bash
# Logging
php artisan logs:monitor          # Monitor logs in real-time
php artisan logs:statistics       # View log statistics
php artisan logs:cleanup          # Clean old logs

# Rate Limiting
php artisan rate-limit:monitor    # Monitor rate limits
php artisan rate-limit:reset      # Reset rate limits

# Database
php artisan db:optimize           # Optimize database
php artisan db:analyze-queries    # Analyze slow queries

# API Documentation
php artisan l5-swagger:generate   # Generate API docs
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 🙏 Acknowledgments

- ISO 27001:2022 Standard
- Laravel Framework
- Open Source Community

## 📞 Support

For support and questions:
- Check the documentation files
- Review API documentation at `/api/documentation`
- Check system health at `/api/health`
- Review logs in `storage/logs/`

---

**Version:** 1.0.0  
**Last Updated:** 2026-05-26  
**Status:** Production Ready ✅
