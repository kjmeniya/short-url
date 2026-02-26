<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistics Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #245dac;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #245dac;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section h2 {
            color: #245dac;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #245dac;
        }
        .stat-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #245dac;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th,
        .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #245dac;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body {
                margin: 0;
            }
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Statistics Report</h1>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>System Administrator Dashboard</p>
    </div>

    <!-- Overall Statistics -->
    <div class="section">
        <h2>Overall Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ number_format($data['overall']['total_users']) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Active Users</div>
                <div class="stat-value">{{ number_format($data['overall']['active_users']) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Emails</div>
                <div class="stat-value">{{ number_format($data['overall']['total_emails']) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Logins</div>
                <div class="stat-value">{{ number_format($data['overall']['total_logins']) }}</div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="section">
        <h2>User Management Statistics</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Users</td>
                    <td>{{ number_format($data['users']['total']) }}</td>
                    <td>100%</td>
                </tr>
                <tr>
                    <td>Active Users</td>
                    <td>{{ number_format($data['users']['active']) }}</td>
                    <td>{{ $data['users']['total'] > 0 ? round(($data['users']['active'] / $data['users']['total']) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Verified Users</td>
                    <td>{{ number_format($data['users']['verified']) }}</td>
                    <td>{{ $data['users']['total'] > 0 ? round(($data['users']['verified'] / $data['users']['total']) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Super Admins</td>
                    <td>{{ number_format($data['users']['super_admins']) }}</td>
                    <td>{{ $data['users']['total'] > 0 ? round(($data['users']['super_admins'] / $data['users']['total']) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Recent Logins (7 days)</td>
                    <td>{{ number_format($data['users']['recent_logins']) }}</td>
                    <td>{{ $data['users']['total'] > 0 ? round(($data['users']['recent_logins'] / $data['users']['total']) * 100, 1) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Email Statistics -->
    <div class="section">
        <h2>Email System Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Success Rate</div>
                <div class="stat-value">{{ $data['emails']['success_rate'] }}%</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Emails Today</div>
                <div class="stat-value">{{ number_format($data['emails']['today']) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">This Week</div>
                <div class="stat-value">{{ number_format($data['emails']['this_week']) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">This Month</div>
                <div class="stat-value">{{ number_format($data['emails']['this_month']) }}</div>
            </div>
        </div>
    </div>

    <!-- Login Statistics -->
    <div class="section">
        <h2>Login Security Statistics</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Login Attempts</td>
                    <td>{{ number_format($data['logins']['total']) }}</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>Successful Logins</td>
                    <td>{{ number_format($data['logins']['successful']) }}</td>
                    <td>{{ $data['logins']['success_rate'] }}%</td>
                </tr>
                <tr>
                    <td>Failed Logins</td>
                    <td>{{ number_format($data['logins']['failed']) }}</td>
                    <td>{{ $data['logins']['total'] > 0 ? round((($data['logins']['failed'] / $data['logins']['total']) * 100), 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Suspicious Activity</td>
                    <td>{{ number_format($data['logins']['suspicious']) }}</td>
                    <td>{{ $data['logins']['total'] > 0 ? round((($data['logins']['suspicious'] / $data['logins']['total']) * 100), 1) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- System Statistics -->
    <div class="section">
        <h2>System Health Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Database Size</div>
                <div class="stat-value">{{ $data['system']['database_size'] }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Storage Used</div>
                <div class="stat-value">{{ $data['system']['storage_usage']['usage_percentage'] ?? 25 }}%</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Error Logs</div>
                <div class="stat-value">{{ number_format($data['system']['error_logs']) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Roles</div>
                <div class="stat-value">{{ number_format($data['system']['roles']) }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This report was generated automatically by the Admin Dashboard System</p>
        <p>© {{ date('Y') }} Admin Panel - All rights reserved</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
