"""
Python Trino Client Example
Demonstrates how Python modules can connect to Trino for data operations
"""

from trino.dbapi import connect
import json


class TrinoClient:
    """
    Python client for connecting to Trino and executing queries
    """
    
    def __init__(self, host='localhost', port=8080, user='trino', catalog='mysql', schema='test_db'):
        """
        Initialize Trino connection
        
        Args:
            host: Trino server host
            port: Trino server port
            user: Trino user
            catalog: Default catalog to use
            schema: Default schema to use
        """
        self.host = host
        self.port = port
        self.user = user
        self.catalog = catalog
        self.schema = schema
        self.connection = None
    
    def connect(self):
        """Establish connection to Trino"""
        try:
            self.connection = connect(
                host=self.host,
                port=self.port,
                user=self.user,
                catalog=self.catalog,
                schema=self.schema,
            )
            print(f"✓ Connected to Trino at {self.host}:{self.port}")
            return True
        except Exception as e:
            print(f"✗ Failed to connect to Trino: {e}")
            return False
    
    def execute_query(self, query):
        """
        Execute a SQL query and return results
        
        Args:
            query: SQL query string
            
        Returns:
            List of tuples containing query results
        """
        if not self.connection:
            self.connect()
        
        try:
            cursor = self.connection.cursor()
            cursor.execute(query)
            results = cursor.fetchall()
            columns = [desc[0] for desc in cursor.description]
            cursor.close()
            
            # Convert to list of dictionaries
            formatted_results = []
            for row in results:
                formatted_results.append(dict(zip(columns, row)))
            
            return formatted_results
        except Exception as e:
            print(f"✗ Query execution failed: {e}")
            return None
    
    def test_connection(self):
        """Test the Trino connection"""
        result = self.execute_query("SELECT 1 as test")
        if result:
            print("✓ Connection test successful")
            return True
        return False
    
    def list_catalogs(self):
        """List all available catalogs"""
        return self.execute_query("SHOW CATALOGS")
    
    def list_schemas(self, catalog=None):
        """List all schemas in a catalog"""
        cat = catalog or self.catalog
        return self.execute_query(f"SHOW SCHEMAS FROM {cat}")
    
    def list_tables(self, schema=None, catalog=None):
        """List all tables in a schema"""
        cat = catalog or self.catalog
        sch = schema or self.schema
        return self.execute_query(f"SHOW TABLES FROM {cat}.{sch}")
    
    def describe_table(self, table, schema=None, catalog=None):
        """Describe table structure"""
        cat = catalog or self.catalog
        sch = schema or self.schema
        return self.execute_query(f"DESCRIBE {cat}.{sch}.{table}")
    
    def close(self):
        """Close the connection"""
        if self.connection:
            self.connection.close()
            print("✓ Connection closed")


# Data Quality Module Example
class DataQualityChecker:
    """
    Example Data Quality module using Trino
    """
    
    def __init__(self, trino_client):
        self.client = trino_client
    
    def check_null_values(self, table, column, catalog=None, schema=None):
        """Check for null values in a column"""
        cat = catalog or self.client.catalog
        sch = schema or self.client.schema
        
        query = f"""
        SELECT 
            COUNT(*) as total_rows,
            SUM(CASE WHEN {column} IS NULL THEN 1 ELSE 0 END) as null_count,
            ROUND(SUM(CASE WHEN {column} IS NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as null_percentage
        FROM {cat}.{sch}.{table}
        """
        
        result = self.client.execute_query(query)
        return result[0] if result else None
    
    def check_duplicates(self, table, column, catalog=None, schema=None):
        """Check for duplicate values in a column"""
        cat = catalog or self.client.catalog
        sch = schema or self.client.schema
        
        query = f"""
        SELECT 
            {column},
            COUNT(*) as count
        FROM {cat}.{sch}.{table}
        GROUP BY {column}
        HAVING COUNT(*) > 1
        """
        
        return self.client.execute_query(query)
    
    def check_value_range(self, table, column, min_value, max_value, catalog=None, schema=None):
        """Check if values are within expected range"""
        cat = catalog or self.client.catalog
        sch = schema or self.client.schema
        
        query = f"""
        SELECT 
            COUNT(*) as total_rows,
            SUM(CASE WHEN {column} < {min_value} OR {column} > {max_value} THEN 1 ELSE 0 END) as out_of_range_count
        FROM {cat}.{sch}.{table}
        WHERE {column} IS NOT NULL
        """
        
        result = self.client.execute_query(query)
        return result[0] if result else None


# Data Profiling Module Example
class DataProfiler:
    """
    Example Data Profiling module using Trino
    """
    
    def __init__(self, trino_client):
        self.client = trino_client
    
    def profile_numeric_column(self, table, column, catalog=None, schema=None):
        """Profile a numeric column"""
        cat = catalog or self.client.catalog
        sch = schema or self.client.schema
        
        query = f"""
        SELECT 
            COUNT(*) as count,
            MIN({column}) as min_value,
            MAX({column}) as max_value,
            AVG({column}) as avg_value,
            STDDEV({column}) as std_dev,
            APPROX_PERCENTILE({column}, 0.5) as median
        FROM {cat}.{sch}.{table}
        WHERE {column} IS NOT NULL
        """
        
        result = self.client.execute_query(query)
        return result[0] if result else None
    
    def profile_text_column(self, table, column, catalog=None, schema=None):
        """Profile a text column"""
        cat = catalog or self.client.catalog
        sch = schema or self.client.schema
        
        query = f"""
        SELECT 
            COUNT(*) as count,
            COUNT(DISTINCT {column}) as distinct_count,
            MIN(LENGTH({column})) as min_length,
            MAX(LENGTH({column})) as max_length,
            AVG(LENGTH({column})) as avg_length
        FROM {cat}.{sch}.{table}
        WHERE {column} IS NOT NULL
        """
        
        result = self.client.execute_query(query)
        return result[0] if result else None
    
    def get_value_distribution(self, table, column, limit=10, catalog=None, schema=None):
        """Get value distribution for a column"""
        cat = catalog or self.client.catalog
        sch = schema or self.client.schema
        
        query = f"""
        SELECT 
            {column},
            COUNT(*) as frequency,
            ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as percentage
        FROM {cat}.{sch}.{table}
        GROUP BY {column}
        ORDER BY frequency DESC
        LIMIT {limit}
        """
        
        return self.client.execute_query(query)


def main():
    """
    Main function demonstrating usage
    """
    print("\n" + "="*60)
    print("Python Trino Client - Data Governance Example")
    print("="*60 + "\n")
    
    # Initialize Trino client
    client = TrinoClient(
        host='localhost',
        port=8080,
        user='trino',
        catalog='mysql',
        schema='test_db'
    )
    
    # Connect and test
    if not client.connect():
        print("Failed to connect. Make sure Trino is running.")
        return
    
    client.test_connection()
    
    print("\n" + "-"*60)
    print("1. Listing Available Resources")
    print("-"*60)
    
    # List catalogs
    catalogs = client.list_catalogs()
    print(f"\n📁 Available Catalogs:")
    for catalog in catalogs:
        print(f"   - {catalog['Catalog']}")
    
    # List tables
    tables = client.list_tables()
    print(f"\n📊 Tables in {client.catalog}.{client.schema}:")
    for table in tables:
        print(f"   - {table['Table']}")
    
    print("\n" + "-"*60)
    print("2. Querying Data")
    print("-"*60)
    
    # Query users
    users = client.execute_query("SELECT * FROM mysql.test_db.users LIMIT 3")
    print(f"\n👥 Sample Users:")
    for user in users:
        print(f"   - {user['name']} ({user['email']}) - {user['department']} - ${user['salary']}")
    
    # Query products
    products = client.execute_query("SELECT * FROM mysql.test_db.products LIMIT 3")
    print(f"\n📦 Sample Products:")
    for product in products:
        print(f"   - {product['name']} ({product['category']}) - ${product['price']} - Stock: {product['stock']}")
    
    print("\n" + "-"*60)
    print("3. Data Quality Checks")
    print("-"*60)
    
    # Initialize data quality checker
    dq_checker = DataQualityChecker(client)
    
    # Check for null values
    null_check = dq_checker.check_null_values('users', 'email')
    print(f"\n✓ Null Value Check (users.email):")
    print(f"   Total Rows: {null_check['total_rows']}")
    print(f"   Null Count: {null_check['null_count']}")
    print(f"   Null Percentage: {null_check['null_percentage']}%")
    
    # Check for duplicates
    duplicates = dq_checker.check_duplicates('users', 'email')
    print(f"\n✓ Duplicate Check (users.email):")
    if duplicates:
        print(f"   Found {len(duplicates)} duplicate(s)")
        for dup in duplicates:
            print(f"   - {dup['email']}: {dup['count']} occurrences")
    else:
        print(f"   No duplicates found")
    
    # Check value range
    range_check = dq_checker.check_value_range('users', 'salary', 50000, 100000)
    print(f"\n✓ Value Range Check (users.salary: 50000-100000):")
    print(f"   Total Rows: {range_check['total_rows']}")
    print(f"   Out of Range: {range_check['out_of_range_count']}")
    
    print("\n" + "-"*60)
    print("4. Data Profiling")
    print("-"*60)
    
    # Initialize data profiler
    profiler = DataProfiler(client)
    
    # Profile numeric column
    salary_profile = profiler.profile_numeric_column('users', 'salary')
    print(f"\n📊 Salary Profile:")
    print(f"   Count: {salary_profile['count']}")
    print(f"   Min: ${salary_profile['min_value']}")
    print(f"   Max: ${salary_profile['max_value']}")
    print(f"   Avg: ${round(salary_profile['avg_value'], 2)}")
    print(f"   Median: ${salary_profile['median']}")
    
    # Profile text column
    name_profile = profiler.profile_text_column('users', 'name')
    print(f"\n📝 Name Profile:")
    print(f"   Count: {name_profile['count']}")
    print(f"   Distinct: {name_profile['distinct_count']}")
    print(f"   Min Length: {name_profile['min_length']}")
    print(f"   Max Length: {name_profile['max_length']}")
    print(f"   Avg Length: {round(name_profile['avg_length'], 2)}")
    
    # Get value distribution
    dept_distribution = profiler.get_value_distribution('users', 'department')
    print(f"\n📈 Department Distribution:")
    for dept in dept_distribution:
        print(f"   - {dept['department']}: {dept['frequency']} ({dept['percentage']}%)")
    
    print("\n" + "="*60)
    print("✓ Demo Complete!")
    print("="*60 + "\n")
    
    # Close connection
    client.close()


if __name__ == "__main__":
    # Note: Install required package first: pip install trino
    try:
        main()
    except ImportError:
        print("\n✗ Error: 'trino' package not installed")
        print("Please install it with: pip install trino")
    except Exception as e:
        print(f"\n✗ Error: {e}")

