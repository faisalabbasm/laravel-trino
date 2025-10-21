<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trino Data Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <h1 class="text-2xl font-bold text-gray-900">🔍 Trino Data Dashboard</h1>
                    <div class="flex space-x-4">
                        <a href="/" class="text-blue-600 hover:text-blue-700 px-3 py-2 rounded-md text-sm font-medium bg-blue-50">Dashboard</a>
                        <a href="/query" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Query</a>
                        <a href="/drift" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">🔍 Drift Detection</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Laravel + Trino + MySQL</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p id="totalUsers" class="text-2xl font-semibold text-gray-900">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Products</p>
                        <p id="totalProducts" class="text-2xl font-semibold text-gray-900">-</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Avg Salary</p>
                        <p id="avgSalary" class="text-2xl font-semibold text-gray-900">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Department Distribution -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Users by Department</h2>
                <canvas id="departmentChart"></canvas>
            </div>

            <!-- Salary by Department -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Average Salary by Department</h2>
                <canvas id="salaryChart"></canvas>
            </div>
        </div>

        <!-- Product Stock Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Inventory</h2>
            <canvas id="productChart"></canvas>
        </div>

        <!-- Data Tables -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Users Data</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salary</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Products Data</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch and display data
        async function loadDashboard() {
            try {
                // Fetch users
                const usersResponse = await fetch('/trino/users?limit=100');
                const usersData = await usersResponse.json();
                const users = usersData.result.data;

                // Fetch products
                const productsResponse = await fetch('/trino/products?limit=100');
                const productsData = await productsResponse.json();
                const products = productsData.result.data;

                // Update stats
                document.getElementById('totalUsers').textContent = users.length;
                document.getElementById('totalProducts').textContent = products.length;
                
                const avgSalary = users.reduce((sum, u) => sum + parseFloat(u.salary), 0) / users.length;
                document.getElementById('avgSalary').textContent = '$' + avgSalary.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                // Department distribution
                const deptCounts = {};
                const deptSalaries = {};
                users.forEach(user => {
                    deptCounts[user.department] = (deptCounts[user.department] || 0) + 1;
                    if (!deptSalaries[user.department]) {
                        deptSalaries[user.department] = [];
                    }
                    deptSalaries[user.department].push(parseFloat(user.salary));
                });

                // Chart: Department Distribution
                new Chart(document.getElementById('departmentChart'), {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(deptCounts),
                        datasets: [{
                            data: Object.values(deptCounts),
                            backgroundColor: [
                                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });

                // Chart: Salary by Department
                const avgSalariesByDept = {};
                Object.keys(deptSalaries).forEach(dept => {
                    const salaries = deptSalaries[dept];
                    avgSalariesByDept[dept] = salaries.reduce((a, b) => a + b, 0) / salaries.length;
                });

                new Chart(document.getElementById('salaryChart'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(avgSalariesByDept),
                        datasets: [{
                            label: 'Average Salary',
                            data: Object.values(avgSalariesByDept),
                            backgroundColor: '#3B82F6'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => '$' + value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                                }
                            }
                        }
                    }
                });

                // Chart: Product Stock
                new Chart(document.getElementById('productChart'), {
                    type: 'bar',
                    data: {
                        labels: products.map(p => p.name),
                        datasets: [{
                            label: 'Stock Level',
                            data: products.map(p => p.stock),
                            backgroundColor: '#10B981'
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        scales: {
                            x: { beginAtZero: true }
                        }
                    }
                });

                // Fill Users Table
                const usersTableBody = document.getElementById('usersTableBody');
                usersTableBody.innerHTML = users.map(user => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.department}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(user.salary).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</td>
                    </tr>
                `).join('');

                // Fill Products Table
                const productsTableBody = document.getElementById('productsTableBody');
                productsTableBody.innerHTML = products.map(product => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.stock}</td>
                    </tr>
                `).join('');

            } catch (error) {
                console.error('Error loading dashboard:', error);
                alert('Error loading data. Make sure the API endpoints are working.');
            }
        }

        // Load dashboard on page load
        document.addEventListener('DOMContentLoaded', loadDashboard);
    </script>
</body>
</html>

