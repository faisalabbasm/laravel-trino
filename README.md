# Laravel-Trino Data Governance Platform

A comprehensive data governance solution that integrates Laravel with Trino (distributed SQL query engine) to provide a unified layer for querying multiple data sources including MySQL, MongoDB, and more.

## 🎯 Project Overview

This project provides:
- **Laravel Application**: Backend API for data governance operations
- **Trino Integration**: Distributed SQL query engine as a central data layer
- **Multi-Database Support**: Query different databases using a unified SQL interface
- **Drift Detection**: Advanced schema drift detection capabilities
- **Extensible Architecture**: Easy to add Python modules for data quality, profiling, etc.

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│                     Laravel Application                  │
│                    (API & Services)                      │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ├── HTTP API (REST)
                  │
          ┌───────▼────────┐
          │     Trino      │ ← Central Query Layer
          │  (Port 8080)   │
          └───────┬────────┘
                  │
      ┌───────────┼───────────┬──────────────┐
      │           │           │              │
┌─────▼────┐ ┌───▼────┐ ┌────▼─────┐ ┌─────▼─────┐
│  MySQL   │ │ MongoDB│ │SQL Server│ │   Other   │
└──────────┘ └────────┘ └──────────┘ └───────────┘
```

## 📋 Prerequisites

- Docker & Docker Compose
- PHP 8.1+ (if running locally)
- Composer (if running locally)

## 🚀 Quick Start

### 1. Clone and Setup

```bash
git clone https://github.com/faisalabbasm/laravel-trino.git
cd laravel-trino
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

Generate application key:
```bash
php artisan key:generate
```

### 4. Start Docker Services

Start MySQL, MongoDB, and Trino:
```bash
docker-compose up -d mysql mongodb trino
```

Wait about 30-60 seconds for Trino to fully start and initialize.

### 5. Verify Services are Running

Check Trino logs:
```bash
docker-compose logs trino
```

Access Trino Web UI:
```
http://localhost:8080
```

### 6. Start Laravel Application

Option A: Using Docker
```bash
docker-compose up -d laravel
```

Option B: Locally (recommended for development)
```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

## 🧪 Testing the Integration

### Test Trino Connection

```bash
curl http://localhost:8000/trino/test
```

Expected response:
```json
{
  "connection": {
    "success": true,
    "message": "Successfully connected to Trino"
  },
  "catalogs": {...},
  "schemas": {...},
  "tables": {...}
}
```

### Test MongoDB Setup

Run the MongoDB test script:
```bash
./test_mongodb.sh
```

### Query Users via Trino

```bash
curl http://localhost:8000/trino/users
```

### Query Products via Trino

```bash
curl http://localhost:8000/trino/products
```

### Execute Custom Query

```bash
curl -X POST http://localhost:8000/trino/query \
  -H "Content-Type: application/json" \
  -d '{
    "query": "SELECT * FROM mysql.test_db.users WHERE department = '\''Engineering'\''"
  }'
```

### Query MongoDB via Trino

```bash
# Get MongoDB users
curl -X POST http://localhost:8000/trino/query \
  -H "Content-Type: application/json" \
  -d '{
    "query": "SELECT name, email, age FROM users",
    "catalog": "mongodb",
    "schema": "test_db"
  }'

# Get MongoDB products  
curl -X POST http://localhost:8000/trino/query \
  -H "Content-Type: application/json" \
  -d '{
    "query": "SELECT name, category, price FROM products WHERE category = '\''Electronics'\''",
    "catalog": "mongodb",
    "schema": "test_db"
  }'
```

## 🔍 Drift Detection Features

This project includes advanced schema drift detection capabilities:

### Test Drift Detection

```bash
# Run drift detection demo
./test_drift_demo.sh

# Run comprehensive drift tests
./TEST_DRIFT_DETECTION.sh
```

### Drift Detection API

```bash
# Check for schema drift
curl http://localhost:8000/drift-detection/check

# Get drift history
curl http://localhost:8000/drift-detection/history

# Generate drift report
curl http://localhost:8000/drift-detection/report
```

## 📁 Project Structure

```
laravel-trino/
├── app/
│   ├── Http/Controllers/
│   │   ├── TrinoController.php          # API endpoints for Trino
│   │   └── DriftDetectionController.php # Drift detection endpoints
│   ├── Services/
│   │   ├── TrinoService.php             # Core Trino integration service
│   │   └── DriftDetectionService.php    # Schema drift detection service
│   └── Providers/
│       └── TrinoServiceProvider.php    # Service provider
├── config/
│   ├── app.php                         # Laravel app configuration
│   └── trino.php                       # Trino configuration
├── routes/
│   ├── web.php                         # Web routes
│   └── api.php                         # API routes
├── docker/
│   ├── trino/
│   │   ├── catalog/
│   │   │   ├── mysql.properties         # MySQL connector config
│   │   │   └── mongodb.properties       # MongoDB connector config
│   │   └── config.properties            # Trino server config
│   ├── mysql/
│   │   └── init.sql                     # Sample database schema
│   └── mongodb/
│       └── init-mongo.js                # Sample MongoDB data
├── resources/views/
│   ├── dashboard.blade.php              # Main dashboard
│   ├── query.blade.php                  # Query interface
│   └── drift-detection.blade.php        # Drift detection UI
├── docker-compose.yml                   # Docker services configuration
└── README.md                            # This file
```

## 🔌 API Endpoints

### Core Trino Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | API information and available endpoints |
| GET | `/trino/test` | Test Trino connection and list catalogs, schemas, and tables |
| POST | `/trino/query` | Execute a custom SQL query via Trino |
| GET | `/trino/users` | Get users from MySQL via Trino |
| GET | `/trino/products` | Get products from MySQL via Trino |

### Drift Detection Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/drift-detection/check` | Check for schema drift |
| GET | `/drift-detection/history` | Get drift detection history |
| GET | `/drift-detection/report` | Generate drift detection report |
| POST | `/drift-detection/scan` | Perform comprehensive drift scan |

### Example API Usage

**Execute Custom Query:**
```bash
curl -X POST http://localhost:8000/trino/query \
  -H "Content-Type: application/json" \
  -d '{
    "query": "SELECT * FROM mysql.test_db.users",
    "catalog": "mysql",
    "schema": "test_db"
  }'
```

**Check for Drift:**
```bash
curl http://localhost:8000/drift-detection/check
```

## 🐍 Python Integration Example

To integrate Python modules with Trino, install the Trino Python client:

```bash
pip install trino
```

Example Python code to query via Trino:

```python
from trino.dbapi import connect

# Connect to Trino
conn = connect(
    host='localhost',
    port=8080,
    user='trino',
    catalog='mysql',
    schema='test_db',
)

# Execute query
cursor = conn.cursor()
cursor.execute('SELECT * FROM users WHERE department = ?', ('Engineering',))

# Fetch results
rows = cursor.fetchall()
for row in rows:
    print(row)

cursor.close()
conn.close()
```

## 🔧 Configuration

### Trino Configuration

Edit `.env` to configure Trino connection:

```env
TRINO_HOST=trino
TRINO_PORT=8080
TRINO_CATALOG=mysql
TRINO_SCHEMA=test_db
TRINO_USER=trino
TRINO_TIMEOUT=30
TRINO_DEBUG=false
```

### Adding New Data Sources

To add a new data source (e.g., PostgreSQL, SQL Server):

1. Create a new catalog file in `docker/trino/catalog/`:

**Example: PostgreSQL (`postgresql.properties`)**
```properties
connector.name=postgresql
connection-url=jdbc:postgresql://postgres:5432/mydb
connection-user=postgres
connection-password=password
```

2. Add the service to `docker-compose.yml`

3. Restart Trino:
```bash
docker-compose restart trino
```

## 🛠️ Laravel Trino Service Usage

You can use the `TrinoService` in any Laravel class:

```php
use App\Services\TrinoService;

class DataQualityService
{
    protected $trino;
    
    public function __construct(TrinoService $trino)
    {
        $this->trino = $trino;
    }
    
    public function validateData()
    {
        // Query across multiple sources
        $mysqlData = $this->trino->query(
            'SELECT * FROM mysql.test_db.users'
        );
        
        $mongoData = $this->trino->query(
            'SELECT * FROM mongodb.mydb.users'
        );
        
        // Perform validation...
    }
}
```

### Available Methods

```php
// Execute a query
$trino->query($sql, $catalog, $schema);

// Test connection
$trino->testConnection();

// List catalogs
$trino->listCatalogs();

// List schemas
$trino->listSchemas($catalog);

// List tables
$trino->listTables($schema, $catalog);

// Describe table structure
$trino->describeTable($table, $schema, $catalog);

// Query specific data source
$trino->queryDataSource('mysql', $query, $schema);
```

## 📊 Sample Data

### MySQL Sample Data

The MySQL database is pre-populated with sample data:

**Users Table:**
- 5 sample users with departments and salaries

**Products Table:**
- 5 sample products with categories and prices

### MongoDB Sample Data

The MongoDB database includes:

**Users Collection:**
- 3 sample users with nested address objects and tags
- Fields: user_id, name, email, age, status, created_at, address, tags

**Products Collection:**
- 4 sample products with specifications and inventory
- Fields: product_id, name, category, price, in_stock, quantity, specs, tags

**Orders Collection:**
- 3 sample orders with embedded items
- Fields: order_id, user_id, order_date, status, total_amount, items, shipping_address

## 🔍 Troubleshooting

### Trino not starting

Check logs:
```bash
docker-compose logs trino
```

Wait longer (Trino takes time to start):
```bash
# Wait 60 seconds, then test
sleep 60 && curl http://localhost:8080/v1/info
```

### Connection refused

Ensure all services are running:
```bash
docker-compose ps
```

### MySQL connection issues

Check MySQL is accessible:
```bash
docker-compose exec mysql mysql -utest_user -ptest_password test_db -e "SELECT 1"
```

### MongoDB connection issues

Check MongoDB is accessible:
```bash
docker exec -it laravel_mongodb mongosh -u admin -p admin_password --authenticationDatabase admin --eval "db.adminCommand('ping')"
```

### Clear and restart

```bash
docker-compose down -v
docker-compose up -d
```

## 🎯 Next Steps

1. **Add More Connectors**: Configure PostgreSQL, SQL Server, or other data sources
2. **Cross-Database Queries**: Query MongoDB and MySQL together via Trino
3. **Python Modules**: Create Python services for data quality and profiling
4. **Authentication**: Add API authentication (Laravel Sanctum/Passport)
5. **Query Builder**: Create a visual query builder UI
6. **Scheduling**: Add scheduled data quality checks using Laravel Task Scheduler
7. **Monitoring**: Integrate with monitoring tools for query performance
8. **Caching**: Add Redis caching for frequently accessed queries

## 📖 Additional Documentation

- **[QUICKSTART.md](QUICKSTART.md)** - 5-minute quick start guide
- **[DRIFT_DETECTION_QUICKSTART.md](DRIFT_DETECTION_QUICKSTART.md)** - Drift detection quick start
- **[MONGODB_SETUP.md](MONGODB_SETUP.md)** - Complete MongoDB setup and query guide
- **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - Testing and validation guide
- **[UI_USAGE_GUIDE.md](UI_USAGE_GUIDE.md)** - Web interface usage guide

## 📚 Resources

- [Trino Documentation](https://trino.io/docs/current/)
- [Laravel Documentation](https://laravel.com/docs)
- [Trino Connectors](https://trino.io/docs/current/connector.html)

## 📝 License

MIT License

---

Built with ❤️ for Data Governance