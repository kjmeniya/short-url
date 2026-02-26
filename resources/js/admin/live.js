/**
 * Live Users Chart
 * Real-time chart for displaying live user statistics
 */

class LiveUsersChart {
    constructor(elementId, options = {}) {
        this.elementId = elementId;
        this.chart = null;
        this.data = {
            web: [],
            mobile: [],
            admin: [],
            maxPoints: options.maxPoints || 60
        };
        this.latestData = { web: 0, mobile: 0, admin: 0 };
        this.updateInterval = null;

        this.init();
    }

    init() {
        const el = document.querySelector(`#${this.elementId}`);
        if (!el) {
            console.error(`Element #${this.elementId} not found`);
            return;
        }

        const options = {
            chart: {
                type: 'line',
                height: 300,
                fontFamily: 'inherit',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                }
            },
            series: [
                {
                    name: 'Web',
                    data: []
                },
                {
                    name: 'Mobile',
                    data: []
                },
                {
                    name: 'Admin',
                    data: []
                }
            ],
            colors: ['#0d6efd', '#0dcaf0', '#dc3545'],
            stroke: {
                curve: 'smooth',
                width: 2
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false,
                    formatter: function (val) {
                        return new Intl.DateTimeFormat('en-US', {
                            timeZone: window.appTimezone || 'UTC',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).format(new Date(val));
                    },
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            yaxis: {
                min: 0,
                labels: {
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'left',
                fontSize: '12px'
            },
            grid: {
                borderColor: '#e9ecef'
            },
            tooltip: {
                theme: 'dark',
                x: {
                    // format: 'HH:mm:ss',
                    formatter: function (val) {
                        return new Intl.DateTimeFormat('en-US', {
                            timeZone: window.appTimezone || 'UTC',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).format(new Date(val));
                    }
                }
            }
        };

        this.chart = new ApexCharts(el, options);
        this.chart.render();
    }

    updateData(data) {
        this.latestData = {
            web: data.web || 0,
            mobile: data.mobile || 0,
            admin: data.admin || 0
        };
    }

    start() {
        if (this.updateInterval) {
            return; // Already running
        }

        this.updateInterval = setInterval(() => {
            if (!this.chart) return;

            const timestamp = new Date().getTime();

            // Add new data points
            this.data.web.push({ x: timestamp, y: this.latestData.web });
            this.data.mobile.push({ x: timestamp, y: this.latestData.mobile });
            this.data.admin.push({ x: timestamp, y: this.latestData.admin });

            // Keep only last maxPoints
            if (this.data.web.length > this.data.maxPoints) {
                this.data.web.shift();
                this.data.mobile.shift();
                this.data.admin.shift();
            }

            // Update chart
            this.chart.updateSeries([
                { name: 'Web', data: this.data.web },
                { name: 'Mobile', data: this.data.mobile },
                { name: 'Admin', data: this.data.admin }
            ]);
        }, 1000);
    }

    stop() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    destroy() {
        this.stop();
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    }
}

// Show message when chart is unavailable
function showChartUnavailableMessage(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; height: 300px; text-align: center; color: #6c757d;">
                <div>
                    <i data-lucide="wifi-off" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 14px;">Real-time chart unavailable</p>
                    <p style="margin: 8px 0 0 0; font-size: 12px; opacity: 0.7;">Socket server is not running</p>
                </div>
            </div>
        `;
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

// Initialize chart when SocketManager is available
function initLiveUsersChart(elementId, options = {}) {
    if (!window.SocketManager) {
        showChartUnavailableMessage(elementId);
        return null;
    }

    const startChart = function () {
        if (!window.SocketManager.isConnected()) return;

        // Socket is connected, initialize chart
        const chart = new LiveUsersChart(elementId, options);

        // Listen for Socket.IO updates
        window.SocketManager.on('users:update', function (data) {
            chart.updateData(data);
        });

        // Start the chart update interval
        chart.start();

        // Request initial stats
        window.SocketManager.requestAdminStats();
    };

    if (window.SocketManager.isConnected()) {
        startChart();
    } else {
        // Show loading or wait
        window.SocketManager.on('connect', startChart);

        // Show unavailable message after 10 seconds if still not connected
        setTimeout(() => {
            if (!window.SocketManager.isConnected()) {
                showChartUnavailableMessage(elementId);
            }
        }, 10000);
    }

    return null;
}

// Export for use
window.LiveUsersChart = LiveUsersChart;
window.initLiveUsersChart = initLiveUsersChart;
