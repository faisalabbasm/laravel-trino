<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🔍 Database Drift Detection Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .drift-card {
            transition: all 0.3s ease;
        }
        .drift-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .badge-critical { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-lg">
        <div class="container mx-auto px-6 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center">
                        🔍 Database Drift Detection
                    </h1>
                    <p class="text-blue-100 mt-1">Monitor and track database schema changes in real-time</p>
                </div>
                <div class="flex gap-4">
                    <a href="/" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition">
                        🏠 Dashboard
                    </a>
                    <a href="/query" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition">
                        🔍 Query Tool
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 py-8">
        <!-- Configuration Panel -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">⚙️ Configuration</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Source Database -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Source Database</label>
                    <input type="text" id="sourceDb" value="mysql" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Trino catalog name</p>
                </div>

                <!-- Target Database -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Target Database</label>
                    <input type="text" id="targetDb" value="mysql" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Trino catalog name</p>
                </div>

                <!-- Schema -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Schema Name</label>
                    <input type="text" id="schema" value="test_db" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Database schema</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 mt-6 flex-wrap">
                <button onclick="runFullReport()" 
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <span>📊</span> Run Full Drift Report
                </button>
                
                <button onclick="detectSchemaDrift()" 
                    class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <span>🔍</span> Schema Drift
                </button>
                
                <button onclick="detectRowCountDrift()" 
                    class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <span>📈</span> Row Count Drift
                </button>
                
                <button onclick="captureSnapshot()" 
                    class="bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-yellow-700 transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <span>📸</span> Capture Snapshot
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div id="summarySection" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 hidden">
            <div class="bg-white rounded-xl shadow-md p-6 drift-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Total Drifts</p>
                        <p id="totalDrifts" class="text-3xl font-bold text-gray-800 mt-2">-</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <span class="text-3xl">📊</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 drift-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Critical Issues</p>
                        <p id="criticalDrifts" class="text-3xl font-bold text-red-600 mt-2">-</p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-full">
                        <span class="text-3xl">⚠️</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 drift-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Schema Changes</p>
                        <p id="schemaDrifts" class="text-3xl font-bold text-purple-600 mt-2">-</p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <span class="text-3xl">🔧</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 drift-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Data Drifts</p>
                        <p id="dataDrifts" class="text-3xl font-bold text-green-600 mt-2">-</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <span class="text-3xl">📈</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="hidden">
            <!-- Schema Drift Results -->
            <div id="schemaDriftResults" class="bg-white rounded-xl shadow-md p-6 mb-6 hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span>🔍</span> Schema Drift Details
                </h3>
                <div id="schemaDriftContent"></div>
            </div>

            <!-- Missing Tables -->
            <div id="missingTablesResults" class="bg-white rounded-xl shadow-md p-6 mb-6 hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span>🗂️</span> Missing Tables
                </h3>
                <div id="missingTablesContent"></div>
            </div>

            <!-- Row Count Drift -->
            <div id="rowCountResults" class="bg-white rounded-xl shadow-md p-6 mb-6 hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span>📊</span> Row Count Differences
                </h3>
                <div id="rowCountContent"></div>
            </div>
        </div>

        <!-- Snapshots Section -->
        <div id="snapshotsSection" class="bg-white rounded-xl shadow-md p-6 mb-8 hidden">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>📸</span> Snapshots
            </h3>
            <div id="snapshotsContent"></div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-2xl p-8">
            <div class="text-center">
                <div class="loading mx-auto mb-4" style="width: 60px; height: 60px; border-width: 6px;"></div>
                <p class="text-lg font-semibold text-gray-700">Analyzing databases...</p>
                <p class="text-sm text-gray-500 mt-2">This may take a few moments</p>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div id="toast" class="hidden fixed top-4 right-4 bg-white rounded-lg shadow-xl p-4 max-w-md z-50">
            <div class="flex items-start gap-3">
                <span id="toastIcon" class="text-2xl"></span>
                <div class="flex-1">
                    <p id="toastTitle" class="font-semibold text-gray-800"></p>
                    <p id="toastMessage" class="text-sm text-gray-600 mt-1"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSnapshots = [];

        // Get CSRF token
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // Show toast notification
        function showToast(title, message, icon = '✅') {
            const toast = document.getElementById('toast');
            document.getElementById('toastIcon').textContent = icon;
            document.getElementById('toastTitle').textContent = title;
            document.getElementById('toastMessage').textContent = message;
            
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        // Show/hide loading
        function setLoading(show) {
            document.getElementById('loadingIndicator').classList.toggle('hidden', !show);
        }

        // Get configuration
        function getConfig() {
            return {
                source_db: document.getElementById('sourceDb').value,
                target_db: document.getElementById('targetDb').value,
                schema: document.getElementById('schema').value
            };
        }

        // Fetch with CSRF token
        async function fetchWithCsrf(url, options = {}) {
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                ...options.headers
            };
            
            return fetch(url, {
                ...options,
                headers
            });
        }

        // Run full drift report
        async function runFullReport() {
            setLoading(true);
            const config = getConfig();

            try {
                const response = await fetchWithCsrf('/drift/report', {
                    method: 'POST',
                    body: JSON.stringify(config)
                });

                const data = await response.json();

                if (data.success) {
                    displayFullReport(data.report);
                    showToast('Success', 'Drift report generated successfully', '✅');
                } else {
                    showToast('Error', data.error || 'Failed to generate report', '❌');
                }
            } catch (error) {
                showToast('Error', error.message, '❌');
            } finally {
                setLoading(false);
            }
        }

        // Display full report
        function displayFullReport(report) {
            // Show summary
            document.getElementById('summarySection').classList.remove('hidden');
            document.getElementById('totalDrifts').textContent = report.summary.total_drifts;
            document.getElementById('criticalDrifts').textContent = report.summary.critical_drifts;
            document.getElementById('schemaDrifts').textContent = report.summary.columns_different;
            document.getElementById('dataDrifts').textContent = report.summary.row_count_drifts;

            // Show results section
            document.getElementById('resultsSection').classList.remove('hidden');

            // Display schema drifts
            if (report.schema_drifts && report.schema_drifts.length > 0) {
                displaySchemaDrifts(report.schema_drifts);
            }

            // Display missing tables
            if (report.missing_tables && report.missing_tables.length > 0) {
                displayMissingTables(report.missing_tables);
            }

            // Display row count drifts
            if (report.data_drifts && report.data_drifts.length > 0) {
                displayRowCountDrifts(report.data_drifts);
            }
        }

        // Display schema drifts
        function displaySchemaDrifts(drifts) {
            const container = document.getElementById('schemaDriftResults');
            const content = document.getElementById('schemaDriftContent');
            
            if (drifts.length === 0) {
                content.innerHTML = '<p class="text-green-600">✅ No schema drifts detected</p>';
                container.classList.remove('hidden');
                return;
            }

            let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
            html += '<thead class="bg-gray-50"><tr>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Column</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source Type</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target Type</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Drift Type</th>';
            html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

            drifts.forEach(drift => {
                const badgeClass = drift.drift_type.includes('Missing') ? 'badge-critical' : 
                                   drift.drift_type.includes('Mismatch') ? 'badge-warning' : 'badge-info';
                
                html += '<tr>';
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${drift.table_name}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drift.column_name}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drift.source_type || '-'}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drift.target_type || '-'}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap"><span class="badge ${badgeClass}">${drift.drift_type}</span></td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            content.innerHTML = html;
            container.classList.remove('hidden');
        }

        // Display missing tables
        function displayMissingTables(tables) {
            const container = document.getElementById('missingTablesResults');
            const content = document.getElementById('missingTablesContent');
            
            if (tables.length === 0) {
                content.innerHTML = '<p class="text-green-600">✅ No missing tables</p>';
                container.classList.remove('hidden');
                return;
            }

            let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
            tables.forEach(table => {
                html += `<div class="border border-gray-200 rounded-lg p-4">`;
                html += `<p class="font-semibold text-gray-800">${table.table_name}</p>`;
                html += `<span class="badge badge-critical mt-2">${table.drift_type}</span>`;
                html += `</div>`;
            });
            html += '</div>';

            content.innerHTML = html;
            container.classList.remove('hidden');
        }

        // Display row count drifts
        function displayRowCountDrifts(drifts) {
            const container = document.getElementById('rowCountResults');
            const content = document.getElementById('rowCountContent');
            
            if (drifts.length === 0) {
                content.innerHTML = '<p class="text-green-600">✅ No row count differences</p>';
                container.classList.remove('hidden');
                return;
            }

            let html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
            html += '<thead class="bg-gray-50"><tr>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Table</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source Count</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target Count</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Difference</th>';
            html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Drift %</th>';
            html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

            drifts.forEach(drift => {
                const diffClass = Math.abs(drift.drift_percentage) > 10 ? 'text-red-600 font-semibold' : 'text-yellow-600';
                
                html += '<tr>';
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${drift.table_name}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drift.source_count.toLocaleString()}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${drift.target_count.toLocaleString()}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm ${diffClass}">${drift.difference > 0 ? '+' : ''}${drift.difference.toLocaleString()}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm ${diffClass}">${drift.drift_percentage}%</td>`;
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            content.innerHTML = html;
            container.classList.remove('hidden');
        }

        // Detect schema drift only
        async function detectSchemaDrift() {
            setLoading(true);
            const config = getConfig();

            try {
                const response = await fetchWithCsrf('/drift/detect-schema', {
                    method: 'POST',
                    body: JSON.stringify(config)
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('resultsSection').classList.remove('hidden');
                    displaySchemaDrifts(data.drifts);
                    showToast('Success', `Found ${data.count} schema drift(s)`, '🔍');
                } else {
                    showToast('Error', data.error, '❌');
                }
            } catch (error) {
                showToast('Error', error.message, '❌');
            } finally {
                setLoading(false);
            }
        }

        // Detect row count drift
        async function detectRowCountDrift() {
            setLoading(true);
            const config = getConfig();

            try {
                const response = await fetchWithCsrf('/drift/detect-rowcount', {
                    method: 'POST',
                    body: JSON.stringify(config)
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('resultsSection').classList.remove('hidden');
                    displayRowCountDrifts(data.drifts);
                    showToast('Success', `Found ${data.count} row count drift(s)`, '📈');
                } else {
                    showToast('Error', data.error, '❌');
                }
            } catch (error) {
                showToast('Error', error.message, '❌');
            } finally {
                setLoading(false);
            }
        }

        // Capture snapshot
        async function captureSnapshot() {
            setLoading(true);
            const config = {
                database: document.getElementById('sourceDb').value,
                schema: document.getElementById('schema').value
            };

            try {
                const response = await fetchWithCsrf('/drift/snapshot', {
                    method: 'POST',
                    body: JSON.stringify(config)
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Success', `Snapshot ${data.snapshot_id} captured`, '📸');
                    // Reload snapshots from database
                    await loadSnapshots();
                } else {
                    showToast('Error', data.error, '❌');
                }
            } catch (error) {
                showToast('Error', error.message, '❌');
            } finally {
                setLoading(false);
            }
        }

        // Display snapshots
        function displaySnapshots() {
            const container = document.getElementById('snapshotsSection');
            const content = document.getElementById('snapshotsContent');

            if (currentSnapshots.length === 0) {
                container.classList.add('hidden');
                return;
            }

            let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
            currentSnapshots.forEach(snapshot => {
                html += `<div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">`;
                html += `<div class="flex items-start justify-between">`;
                html += `<div class="flex-1">`;
                html += `<p class="font-semibold text-gray-800 text-sm">${snapshot.id}</p>`;
                html += `<p class="text-xs text-gray-500 mt-1">${snapshot.database}.${snapshot.schema}</p>`;
                html += `<p class="text-xs text-gray-400 mt-1">${new Date(snapshot.timestamp).toLocaleString()}</p>`;
                html += `</div>`;
                html += `<button onclick="compareWithSnapshot('${snapshot.id}')" class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-xs font-semibold hover:bg-blue-200 transition">Compare</button>`;
                html += `</div>`;
                html += `</div>`;
            });
            html += '</div>';

            content.innerHTML = html;
            container.classList.remove('hidden');
        }

        // Compare with snapshot
        async function compareWithSnapshot(snapshotId) {
            setLoading(true);
            const config = {
                database: document.getElementById('sourceDb').value,
                schema: document.getElementById('schema').value
            };

            try {
                const response = await fetchWithCsrf(`/drift/compare/${snapshotId}`, {
                    method: 'POST',
                    body: JSON.stringify(config)
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('resultsSection').classList.remove('hidden');
                    displaySchemaDrifts(data.drifts);
                    showToast('Success', `Found ${data.count} change(s) since snapshot`, '📸');
                } else {
                    showToast('Error', data.error, '❌');
                }
            } catch (error) {
                showToast('Error', error.message, '❌');
            } finally {
                setLoading(false);
            }
        }

        // Load snapshots from database
        async function loadSnapshots() {
            try {
                const config = getConfig();
                const response = await fetch(`/drift/snapshots?database=${config.source_db}&schema=${config.schema}`);
                const data = await response.json();

                if (data.success && data.snapshots) {
                    currentSnapshots = data.snapshots.map(s => ({
                        id: s.snapshot_id,
                        database: s.database,
                        schema: s.schema,
                        timestamp: s.captured_at,
                        columns: s.columns_captured
                    }));
                    displaySnapshots();
                }
            } catch (error) {
                console.error('Failed to load snapshots:', error);
            }
        }

        // Initialize
        window.onload = function() {
            showToast('Welcome', 'Configure databases and run drift detection', '👋');
            loadSnapshots(); // Load existing snapshots
        };
    </script>
</body>
</html>

