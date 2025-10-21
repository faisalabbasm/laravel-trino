// Initialize MongoDB with sample data
db = db.getSiblingDB('test_db');

// Create users collection
db.createCollection('users');

// Insert sample users
db.users.insertMany([
    {
        user_id: 1,
        name: 'John Doe',
        email: 'john.doe@example.com',
        age: 30,
        status: 'active',
        created_at: new Date('2024-01-15'),
        address: {
            street: '123 Main St',
            city: 'New York',
            state: 'NY',
            zipcode: '10001'
        },
        tags: ['premium', 'verified']
    },
    {
        user_id: 2,
        name: 'Jane Smith',
        email: 'jane.smith@example.com',
        age: 28,
        status: 'active',
        created_at: new Date('2024-02-20'),
        address: {
            street: '456 Oak Ave',
            city: 'Los Angeles',
            state: 'CA',
            zipcode: '90001'
        },
        tags: ['verified']
    },
    {
        user_id: 3,
        name: 'Bob Johnson',
        email: 'bob.johnson@example.com',
        age: 35,
        status: 'inactive',
        created_at: new Date('2023-11-10'),
        address: {
            street: '789 Pine Rd',
            city: 'Chicago',
            state: 'IL',
            zipcode: '60601'
        },
        tags: ['premium']
    }
]);

// Create products collection
db.createCollection('products');

// Insert sample products
db.products.insertMany([
    {
        product_id: 101,
        name: 'Laptop',
        category: 'Electronics',
        price: 999.99,
        in_stock: true,
        quantity: 50,
        specs: {
            brand: 'TechCorp',
            model: 'Pro X1',
            warranty_years: 2
        },
        tags: ['electronics', 'computers', 'featured']
    },
    {
        product_id: 102,
        name: 'Wireless Mouse',
        category: 'Electronics',
        price: 29.99,
        in_stock: true,
        quantity: 200,
        specs: {
            brand: 'TechCorp',
            model: 'Mouse Pro',
            warranty_years: 1
        },
        tags: ['electronics', 'accessories']
    },
    {
        product_id: 103,
        name: 'Office Chair',
        category: 'Furniture',
        price: 199.99,
        in_stock: false,
        quantity: 0,
        specs: {
            brand: 'ComfortSeating',
            model: 'Ergo Plus',
            warranty_years: 3
        },
        tags: ['furniture', 'office']
    },
    {
        product_id: 104,
        name: 'Mechanical Keyboard',
        category: 'Electronics',
        price: 149.99,
        in_stock: true,
        quantity: 75,
        specs: {
            brand: 'TechCorp',
            model: 'KeyMaster RGB',
            warranty_years: 2
        },
        tags: ['electronics', 'accessories', 'featured']
    }
]);

// Create orders collection
db.createCollection('orders');

// Insert sample orders
db.orders.insertMany([
    {
        order_id: 1001,
        user_id: 1,
        order_date: new Date('2024-03-15'),
        status: 'completed',
        total_amount: 1179.97,
        items: [
            { product_id: 101, product_name: 'Laptop', quantity: 1, price: 999.99 },
            { product_id: 102, product_name: 'Wireless Mouse', quantity: 2, price: 29.99 },
            { product_id: 104, product_name: 'Mechanical Keyboard', quantity: 1, price: 149.99 }
        ],
        shipping_address: {
            street: '123 Main St',
            city: 'New York',
            state: 'NY',
            zipcode: '10001'
        }
    },
    {
        order_id: 1002,
        user_id: 2,
        order_date: new Date('2024-03-20'),
        status: 'shipped',
        total_amount: 29.99,
        items: [
            { product_id: 102, product_name: 'Wireless Mouse', quantity: 1, price: 29.99 }
        ],
        shipping_address: {
            street: '456 Oak Ave',
            city: 'Los Angeles',
            state: 'CA',
            zipcode: '90001'
        }
    },
    {
        order_id: 1003,
        user_id: 1,
        order_date: new Date('2024-04-01'),
        status: 'pending',
        total_amount: 199.99,
        items: [
            { product_id: 103, product_name: 'Office Chair', quantity: 1, price: 199.99 }
        ],
        shipping_address: {
            street: '123 Main St',
            city: 'New York',
            state: 'NY',
            zipcode: '10001'
        }
    }
]);

print('MongoDB initialized with sample data successfully!');
print('Collections created: users, products, orders');




