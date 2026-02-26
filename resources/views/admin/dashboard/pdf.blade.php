<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistics Export</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #245dac;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #245dac;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #245dac;
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            flex: 1;
            min-width: 150px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #245dac;
            text-align: center;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #245dac;
            margin: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 12px;
        }
        .data-table th {
            background-color: #245dac;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e9ecef;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .summary-item {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
        }
        .summary-item h4 {
            color: #245dac;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
        }
        .metric-row:last-child {
            border-bottom: none;
        }
        .metric-label {
            color: #666;
            font-size: 12px;
        }
        .metric-value {
            font-weight: bold;
            color: #245dac;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Statistics Report</h1>
        <div class="subtitle">Comprehensive System Analytics</div>
        <div class="subtitle">Generated: {{ now()->format('F j, Y \a\t g:i A') }}</div>
        <div class="subtitle">Report Period: All Time Data</div>
    </div>

    <!-- Executive Summary -->
    <div class="section">
        <div class="section-title">Executive Summary</div>
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ number_format($data['overall_statistics']['total_users']) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Active Users</div>
                <div class="stat-value">{{ number_format($data['overall_statistics']['active_users']) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Emails</div>
                <div class="stat-value">{{ number_format($data['overall_statistics']['total_emails']) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">System Health</div>
                <div class="stat-value">{{ $data['system_statistics']['storage_usage']['usage_percentage'] ?? 25 }}%</div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="section">
        <div class="section-title">Detailed System Analytics</div>
        <div class="summary-grid">
            <!-- User Analytics -->
            <div class="summary-item">
                <h4>User Management</h4>
                <div class="metric-row">
                    <span class="metric-label">Total Registered</span>
                    <span class="metric-value">{{ number_format($data['user_statistics']['total']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Active Users</span>
                    <span class="metric-value">{{ number_format($data['user_statistics']['active']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Verified Accounts</span>
                    <span class="metric-value">{{ number_format($data['user_statistics']['verified']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Super Administrators</span>
                    <span class="metric-value">{{ number_format($data['user_statistics']['super_admins']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Recent Activity (7d)</span>
                    <span class="metric-value">{{ number_format($data['user_statistics']['recent_logins']) }}</span>
                </div>
            </div>

            <!-- Email System -->
            <div class="summary-item">
                <h4>Email System</h4>
                <div class="metric-row">
                    <span class="metric-label">Total Sent</span>
                    <span class="metric-value">{{ number_format($data['email_statistics']['total']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Success Rate</span>
                    <span class="metric-value">{{ $data['email_statistics']['success_rate'] }}%</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Failed Deliveries</span>
                    <span class="metric-value">{{ number_format($data['email_statistics']['failed']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Emails Today</span>
                    <span class="metric-value">{{ number_format($data['email_statistics']['today']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">This Month</span>
                    <span class="metric-value">{{ number_format($data['email_statistics']['this_month']) }}</span>
                </div>
            </div>

            <!-- Security & Access -->
            <div class="summary-item">
                <h4>Security & Access</h4>
                <div class="metric-row">
                    <span class="metric-label">Total Login Attempts</span>
                    <span class="metric-value">{{ number_format($data['login_statistics']['total']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Success Rate</span>
                    <span class="metric-value">{{ $data['login_statistics']['success_rate'] }}%</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Failed Attempts</span>
                    <span class="metric-value">{{ number_format($data['login_statistics']['failed']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Suspicious Activity</span>
                    <span class="metric-value">{{ number_format($data['login_statistics']['suspicious']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Unique Users Today</span>
                    <span class="metric-value">{{ number_format($data['login_statistics']['unique_users_today']) }}</span>
                </div>
            </div>

            <!-- System Resources -->
            <div class="summary-item">
                <h4>System Resources</h4>
                <div class="metric-row">
                    <span class="metric-label">Database Size</span>
                    <span class="metric-value">{{ $data['system_statistics']['database_size'] }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Storage Usage</span>
                    <span class="metric-value">{{ $data['system_statistics']['storage_usage']['usage_percentage'] ?? 25 }}%</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Error Logs</span>
                    <span class="metric-value">{{ number_format($data['system_statistics']['error_logs']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Active Roles</span>
                    <span class="metric-value">{{ number_format($data['system_statistics']['active_roles']) }}</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">Email Templates</span>
                    <span class="metric-value">{{ number_format($data['system_statistics']['email_templates']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Table -->
    <div class="section page-break">
        <div class="section-title">Performance Metrics Summary</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Metric</th>
                    <th>Current Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Users</td>
                    <td>Active User Ratio</td>
                    <td>{{ $data['user_statistics']['total'] > 0 ? round(($data['user_statistics']['active'] / $data['user_statistics']['total']) * 100, 1) : 0 }}%</td>
                    <td>{{ $data['user_statistics']['total'] > 0 && ($data['user_statistics']['active'] / $data['user_statistics']['total']) > 0.7 ? 'Good' : 'Needs Attention' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>Delivery Success Rate</td>
                    <td>{{ $data['email_statistics']['success_rate'] }}%</td>
                    <td>{{ $data['email_statistics']['success_rate'] > 95 ? 'Excellent' : ($data['email_statistics']['success_rate'] > 85 ? 'Good' : 'Needs Improvement') }}</td>
                </tr>
                <tr>
                    <td>Security</td>
                    <td>Login Success Rate</td>
                    <td>{{ $data['login_statistics']['success_rate'] }}%</td>
                    <td>{{ $data['login_statistics']['success_rate'] > 90 ? 'Good' : 'Monitor Closely' }}</td>
                </tr>
                <tr>
                    <td>System</td>
                    <td>Storage Utilization</td>
                    <td>{{ $data['system_statistics']['storage_usage']['usage_percentage'] ?? 25 }}%</td>
                    <td>{{ ($data['system_statistics']['storage_usage']['usage_percentage'] ?? 25) < 80 ? 'Healthy' : 'Monitor' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Dashboard Statistics Report</strong> | Generated by Admin Panel System</p>
        <p>Report Date: {{ now()->format('F j, Y \a\t g:i A') }} | Export Format: PDF</p>
        <p>© {{ date('Y') }} Admin Dashboard - Confidential System Report</p>
    </div>
</body>
</html>
