<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trino Query Playground</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <h1 class="text-2xl font-bold text-gray-900">⚡ Trino Query Playground</h1>
                    <div class="flex space-x-4">
                        <a href="/" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                        <a href="/query" class="text-blue-600 hover:text-blue-700 px-3 py-2 rounded-md text-sm font-medium bg-blue-50">Query</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Laravel + Trino + MySQL + MongoDB</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Query Editor Section -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">SQL Query Editor</h2>
                <div class="flex space-x-2">
                    <button id="clearBtn" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                        Clear
                    </button>
                    <button id="executeBtn" class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-md transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Execute Query
                    </button>
                </div>
            </div>
            <div class="p-6">
                <textarea id="sqlEditor" class="w-full h-48 p-4 font-mono text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">SELECT * FROM mysql.test_db.users LIMIT 10</textarea>
            </div>
        </div>

        <!-- Example Queries Section -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">📝 Example Queries</h2>
                <p class="text-sm text-gray-500 mt-1">Click any query to load it into the editor</p>
            </div>
            <div class="p-6">
                <!-- MySQL Queries -->
                <h3 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-2">MySQL</span>
                    Relational Database Queries
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SELECT * FROM mysql.test_db.users LIMIT 10">
                        <div class="font-semibold text-gray-900 mb-1">👥 Get All Users</div>
                        <code class="text-xs text-gray-600">SELECT * FROM mysql.test_db.users LIMIT 10</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SELECT * FROM mysql.test_db.products LIMIT 10">
                        <div class="font-semibold text-gray-900 mb-1">📦 Get All Products</div>
                        <code class="text-xs text-gray-600">SELECT * FROM mysql.test_db.products LIMIT 10</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SELECT department, COUNT(*) as count, AVG(salary) as avg_salary FROM mysql.test_db.users GROUP BY department">
                        <div class="font-semibold text-gray-900 mb-1">📊 Users by Department</div>
                        <code class="text-xs text-gray-600">SELECT department, COUNT(*) as count...</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SELECT category, COUNT(*) as count, SUM(stock) as total_stock FROM mysql.test_db.products GROUP BY category">
                        <div class="font-semibold text-gray-900 mb-1">📈 Products by Category</div>
                        <code class="text-xs text-gray-600">SELECT category, COUNT(*) as count...</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SELECT name, salary FROM mysql.test_db.users ORDER BY salary DESC LIMIT 5">
                        <div class="font-semibold text-gray-900 mb-1">💰 Top 5 Highest Salaries</div>
                        <code class="text-xs text-gray-600">SELECT name, salary FROM mysql.test_db.users...</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SELECT name, price, stock FROM mysql.test_db.products WHERE stock < 100 ORDER BY stock ASC">
                        <div class="font-semibold text-gray-900 mb-1">⚠️ Low Stock Products</div>
                        <code class="text-xs text-gray-600">SELECT name, price, stock WHERE stock < 100...</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SHOW CATALOGS">
                        <div class="font-semibold text-gray-900 mb-1">🗂️ Show Catalogs</div>
                        <code class="text-xs text-gray-600">SHOW CATALOGS</code>
                    </button>
                    
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-blue-500 hover:bg-blue-50 transition" data-query="SHOW TABLES FROM mysql.test_db">
                        <div class="font-semibold text-gray-900 mb-1">📋 Show MySQL Tables</div>
                        <code class="text-xs text-gray-600">SHOW TABLES FROM mysql.test_db</code>
                    </button>
                </div>

                <!-- MongoDB Queries -->
                <h3 class="text-md font-semibold text-gray-900 mb-3 mt-6 flex items-center">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs mr-2">MongoDB</span>
                    NoSQL Document Queries
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT * FROM mongodb.test_db.users LIMIT 10">
                        <div class="font-semibold text-gray-900 mb-1">👥 MongoDB Users</div>
                        <code class="text-xs text-gray-600">SELECT * FROM mongodb.test_db.users</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT * FROM mongodb.test_db.products LIMIT 10">
                        <div class="font-semibold text-gray-900 mb-1">📦 MongoDB Products</div>
                        <code class="text-xs text-gray-600">SELECT * FROM mongodb.test_db.products</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT name, email, age, address.city, address.state FROM mongodb.test_db.users WHERE status = 'active'">
                        <div class="font-semibold text-gray-900 mb-1">🏠 Users with Addresses</div>
                        <code class="text-xs text-gray-600">SELECT name, address.city FROM users...</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT name, category, price, specs.brand, specs.warranty_years FROM mongodb.test_db.products WHERE category = 'Electronics' ORDER BY price DESC">
                        <div class="font-semibold text-gray-900 mb-1">💻 Electronics with Specs</div>
                        <code class="text-xs text-gray-600">SELECT name, specs.brand FROM products...</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT * FROM mongodb.test_db.orders ORDER BY order_date DESC">
                        <div class="font-semibold text-gray-900 mb-1">🛒 All Orders</div>
                        <code class="text-xs text-gray-600">SELECT * FROM mongodb.test_db.orders</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT u.name, u.email, o.order_id, o.total_amount FROM mongodb.test_db.users u JOIN mongodb.test_db.orders o ON u.user_id = o.user_id">
                        <div class="font-semibold text-gray-900 mb-1">🔗 Users ↔ Orders Join</div>
                        <code class="text-xs text-gray-600">SELECT u.name, o.order_id FROM users JOIN orders...</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SELECT category, COUNT(*) as count, AVG(price) as avg_price FROM mongodb.test_db.products GROUP BY category">
                        <div class="font-semibold text-gray-900 mb-1">📊 Products by Category</div>
                        <code class="text-xs text-gray-600">SELECT category, COUNT(*) FROM products...</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-green-500 hover:bg-green-50 transition" data-query="SHOW TABLES FROM mongodb.test_db">
                        <div class="font-semibold text-gray-900 mb-1">📋 Show MongoDB Collections</div>
                        <code class="text-xs text-gray-600">SHOW TABLES FROM mongodb.test_db</code>
                    </button>
                </div>

                <!-- Cross-Database Queries -->
                <h3 class="text-md font-semibold text-gray-900 mb-3 mt-6 flex items-center">
                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs mr-2">Cross-DB</span>
                    Query Across MySQL + MongoDB
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-purple-500 hover:bg-purple-50 transition" data-query="SELECT 'MySQL' as source, COUNT(*) as user_count FROM mysql.test_db.users UNION ALL SELECT 'MongoDB' as source, COUNT(*) as user_count FROM mongodb.test_db.users">
                        <div class="font-semibold text-gray-900 mb-1">🔢 Compare User Counts</div>
                        <code class="text-xs text-gray-600">UNION query across both databases</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-purple-500 hover:bg-purple-50 transition" data-query="SELECT 'MySQL' as db, category, COUNT(*) FROM mysql.test_db.products GROUP BY category UNION ALL SELECT 'MongoDB' as db, category, COUNT(*) FROM mongodb.test_db.products GROUP BY category">
                        <div class="font-semibold text-gray-900 mb-1">📦 Products Across Databases</div>
                        <code class="text-xs text-gray-600">Compare product categories</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-purple-500 hover:bg-purple-50 transition" data-query="SHOW CATALOGS">
                        <div class="font-semibold text-gray-900 mb-1">🗂️ Show All Catalogs</div>
                        <code class="text-xs text-gray-600">SHOW CATALOGS</code>
                    </button>

                    <button class="example-query text-left p-4 border border-gray-200 rounded-md hover:border-purple-500 hover:bg-purple-50 transition" data-query="SELECT 'MySQL' as db, name, 'users' as type FROM mysql.test_db.users LIMIT 5 UNION ALL SELECT 'MongoDB' as db, name, 'users' as type FROM mongodb.test_db.users LIMIT 5">
                        <div class="font-semibold text-gray-900 mb-1">👥 Unified User View</div>
                        <code class="text-xs text-gray-600">Combine users from both databases</code>
                    </button>
                </div>
            </div>
        </div>

        <!-- Query Status -->
        <div id="queryStatus" class="hidden mb-6">
            <div id="statusContent" class="rounded-lg p-4"></div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="bg-white rounded-lg shadow hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900">Query Results</h2>
                        <p id="resultsMeta" class="text-sm text-gray-500 mt-1"></p>
                    </div>
                    <button id="exportBtn" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                        Export JSON
                    </button>
                </div>
                <div class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Executed Query:</p>
                    <code id="executedQuery" class="text-sm text-gray-800 font-mono"></code>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div id="resultsContent" class="p-6"></div>
            </div>
        </div>
    </div>

    <script>
        let editor;
        let currentResults = null;

        // Initialize CodeMirror
        document.addEventListener('DOMContentLoaded', function() {
            editor = CodeMirror.fromTextArea(document.getElementById('sqlEditor'), {
                mode: 'text/x-sql',
                theme: 'dracula',
                lineNumbers: true,
                lineWrapping: true,
                autofocus: true,
                extraKeys: {
                    'Ctrl-Enter': executeQuery,
                    'Cmd-Enter': executeQuery
                }
            });
        });

        // Execute Query
        document.getElementById('executeBtn').addEventListener('click', executeQuery);

        async function executeQuery() {
            const query = editor.getValue().trim();
            
            if (!query) {
                showStatus('error', 'Please enter a query');
                return;
            }

            showStatus('loading', 'Executing query...');
            hideResults();

            try {
                const response = await fetch('/trino/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ query: query })
                });

                const data = await response.json();

                if (data.success) {
                    currentResults = data.result;
                    displayResults(data.result, query);
                    showStatus('success', 'Query executed successfully!');
                    
                    // Scroll to results
                    setTimeout(() => {
                        document.getElementById('resultsSection').scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }, 100);
                } else {
                    showStatus('error', 'Error: ' + data.error);
                    hideResults();
                }
            } catch (error) {
                showStatus('error', 'Request failed: ' + error.message);
            }
        }

        // Display Results
        function displayResults(result, query) {
            const resultsSection = document.getElementById('resultsSection');
            const resultsContent = document.getElementById('resultsContent');
            const resultsMeta = document.getElementById('resultsMeta');
            const executedQuery = document.getElementById('executedQuery');

            resultsSection.classList.remove('hidden');

            // Show executed query
            executedQuery.textContent = query;

            // Meta information
            resultsMeta.textContent = `${result.rowCount} rows returned in ${result.stats.elapsedTimeMillis}ms`;

            // Extract column names from the columns array (API returns objects with name/type)
            let columnNames = [];
            if (Array.isArray(result.columns)) {
                // If columns is an array of objects with 'name' property
                if (result.columns.length > 0 && typeof result.columns[0] === 'object' && result.columns[0].name) {
                    columnNames = result.columns.map(col => col.name);
                } else {
                    // If columns is already an array of strings
                    columnNames = result.columns;
                }
            }

            // Create table
            if (columnNames.length > 0 && result.data.length > 0) {
                let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
                
                // Headers
                html += '<thead class="bg-gray-50"><tr>';
                columnNames.forEach(colName => {
                    html += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${escapeHtml(colName)}</th>`;
                });
                html += '</tr></thead>';

                // Rows
                html += '<tbody class="bg-white divide-y divide-gray-200">';
                result.data.forEach(row => {
                    html += '<tr>';
                    columnNames.forEach(colName => {
                        const value = row[colName];
                        let displayValue;
                        if (value === null || value === undefined) {
                            displayValue = '<span class="text-gray-400 italic">NULL</span>';
                        } else if (typeof value === 'object') {
                            displayValue = escapeHtml(JSON.stringify(value));
                        } else {
                            displayValue = escapeHtml(String(value));
                        }
                        html += `<td class="px-6 py-4 text-sm text-gray-900" style="white-space: normal; max-width: 300px;">${displayValue}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></table></div>';

                resultsContent.innerHTML = html;
            } else if (result.data.length === 0) {
                resultsContent.innerHTML = '<p class="text-gray-500 text-center py-8">Query executed successfully but returned no rows</p>';
            } else {
                resultsContent.innerHTML = '<p class="text-gray-500 text-center py-8">No results returned</p>';
            }
        }

        // Show Status
        function showStatus(type, message) {
            const statusDiv = document.getElementById('queryStatus');
            const statusContent = document.getElementById('statusContent');
            
            statusDiv.classList.remove('hidden');
            
            const icons = {
                loading: '<svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>',
                success: '<svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                error: '<svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
            };

            const colors = {
                loading: 'bg-blue-50 border-blue-200',
                success: 'bg-green-50 border-green-200',
                error: 'bg-red-50 border-red-200'
            };

            statusContent.className = `rounded-lg p-4 border ${colors[type]} flex items-center`;
            statusContent.innerHTML = `${icons[type]}<span class="ml-3">${message}</span>`;

            if (type !== 'loading') {
                setTimeout(() => {
                    statusDiv.classList.add('hidden');
                }, 5000);
            }
        }

        // Hide Results
        function hideResults() {
            document.getElementById('resultsSection').classList.add('hidden');
        }

        // Clear Editor
        document.getElementById('clearBtn').addEventListener('click', function() {
            editor.setValue('');
            editor.focus();
        });

        // Example Queries
        document.querySelectorAll('.example-query').forEach(button => {
            button.addEventListener('click', function() {
                const query = this.getAttribute('data-query');
                editor.setValue(query);
                editor.focus();
            });
        });

        // Export Results
        document.getElementById('exportBtn').addEventListener('click', function() {
            if (currentResults) {
                const dataStr = JSON.stringify(currentResults, null, 2);
                const dataBlob = new Blob([dataStr], {type: 'application/json'});
                const url = URL.createObjectURL(dataBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'query_results_' + new Date().getTime() + '.json';
                link.click();
                URL.revokeObjectURL(url);
            }
        });

        // Helper function
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>

