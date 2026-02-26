/**
 * Notification System for Web Interface
 * Handles real-time notification updates and user interactions
 */

class NotificationManager {
    constructor() {
        this.isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.content === 'true';
        this.updateInterval = 120000; // 2 minutes (reduced from 30s since we have real-time sockets)
        this.intervalId = null;

        // Only initialize if authenticated and on admin pages
        if (this.isAuthenticated && window.location.pathname.startsWith('/admin')) {
            this.init();
        }
    }

    init() {
        this.injectStyles();
        this.bindEvents();
        this.setupSocketListener(); // Listen for real-time notifications

        // Delay initial notification loading to prevent interference with login redirects
        // Also check if page is still loading or if we're being redirected
        setTimeout(() => {
            if (document.readyState === 'complete' && window.location.pathname.startsWith('/admin')) {
                this.loadNotifications();
                this.startPolling();
            }
        }, 1500);
    }

    injectStyles() {
        // Add spin animation for refresh icon
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Setup Socket.IO listener for real-time admin notifications
     */
    setupSocketListener() {
        // Check if SocketManager is available
        if (!window.SocketManager) {
            console.log('[Notifications] SocketManager not available, using polling only');
            return;
        }

        // Listen for real-time admin notifications
        window.SocketManager.on('notification:admin', (notification) => {
            console.log('[Notification] Received real-time notification:', notification);

            // Show toast notification using SweetAlert2
            this.showToastNotification(notification);

            // Refresh notification dropdown to show the new notification
            setTimeout(() => {
                this.loadNotifications();
            }, 500);
        });

        console.log('[Notifications] Socket.IO listener registered for admin notifications');
    }

    /**
     * Show toast notification using SweetAlert2
     */
    showToastNotification(notification) {
        if (typeof Swal === 'undefined') {
            console.warn('[Notifications] SweetAlert2 not available, skipping toast');
            return;
        }

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: notification.type || 'info',
            title: notification.title || 'New Notification',
            text: notification.message || ''
        });
    }

    bindEvents() {
        // Clear all notifications
        const clearAllBtn = document.getElementById('clear-all-notifications');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => this.clearAllNotifications());
        }

        // Refresh notifications
        const refreshBtn = document.getElementById('refresh-notifications-header');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshNotifications());
        }

        // Handle notification dropdown show
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.addEventListener('show.bs.dropdown', () => {
                this.loadNotifications();
            });
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('/admin/notifications/navbar', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }

            const data = await response.json();
            this.updateNotificationUI(data);
        } catch (error) {
            console.error('Error loading notifications:', error);
            // Don't show error UI on initial load to avoid disrupting user experience
        }
    }

    updateNotificationUI(data) {
        // Validate data structure
        if (!data || typeof data !== 'object') {
            console.error('Invalid notification data received:', data);
            return;
        }

        // Update notification count
        const countElement = document.getElementById('notification-count');
        if (countElement) {
            const count = data.unread_count || 0;
            countElement.textContent = count === 0 ? 'No new notifications' :
                count === 1 ? '1 New Notification' : `${count} New Notifications`;
        }

        // Update indicator visibility
        const indicator = document.getElementById('notification-indicator');
        if (indicator) {
            indicator.style.display = data.has_unread ? 'block' : 'none';
        }

        // Update notification list
        const listElement = document.getElementById('notification-list');
        if (listElement) {
            // Check if notifications is an array
            if (!data.notifications || !Array.isArray(data.notifications) || data.notifications.length === 0) {
                listElement.innerHTML = `
                    <div class="text-center py-4">
                        <i data-lucide="bell" class="icon-lg text-muted mb-2"></i>
                        <p class="text-muted">No notifications yet</p>
                    </div>
                `;
            } else {
                try {
                    listElement.innerHTML = data.notifications.map(notification =>
                        this.createNotificationHTML(notification)
                    ).join('');
                } catch (error) {
                    console.error('Error mapping notifications:', error);
                    listElement.innerHTML = `
                        <div class="text-center py-4">
                            <i data-lucide="alert-circle" class="icon-lg text-danger mb-2"></i>
                            <p class="text-muted">Error loading notifications</p>
                        </div>
                    `;
                }
            }

            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    createNotificationHTML(notification) {
        const colorClass = this.getColorClass(notification.color);
        const isUnread = notification.is_unread ? 'bg-light' : '';
        const url = notification.url || '#';

        return `
            <a href="${url}" class="dropdown-item d-flex align-items-center py-2 ${isUnread}"
               onclick="event.preventDefault(); window.notificationManager?.markAsRead('${notification.id}'); if('${url}' !== '#' && '${url}' !== '') { setTimeout(() => window.location.href = '${url}', 100); }">
                <div class="w-30px h-30px d-flex align-items-center justify-content-center ${colorClass} rounded-circle me-3">
                    <i class="icon-sm text-white" data-lucide="${notification.icon}"></i>
                </div>
                <div class="flex-grow-1 me-2 text-wrap">
                    <p class="mb-1 fw-${notification.is_unread ? 'bold' : 'normal'}">${notification.title}</p>
                    <p class="fs-12px text-secondary mb-0">${notification.time_ago}</p>
                </div>
                ${notification.is_unread ? '<div class="w-8px h-8px bg-primary rounded-circle"></div>' : ''}
            </a>
        `;
    }

    getColorClass(color) {
        const colorMap = {
            'primary': 'bg-primary',
            'success': 'bg-success',
            'danger': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info',
            'secondary': 'bg-secondary'
        };
        return colorMap[color] || 'bg-primary';
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/admin/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                // Reload notifications to update UI
                setTimeout(() => this.loadNotifications(), 100);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async refreshNotifications() {
        const refreshIcon = document.querySelector('#refresh-notifications-header i');

        // Add spinning animation
        if (refreshIcon) {
            refreshIcon.style.animation = 'spin 1s linear infinite';
        }

        try {
            await this.loadNotifications();

            // Show success feedback (optional)
            if (refreshIcon) {
                refreshIcon.style.color = '#28a745';
                setTimeout(() => {
                    refreshIcon.style.color = '';
                }, 500);
            }
        } catch (error) {
            console.error('Error refreshing notifications:', error);

            // Show error feedback
            if (refreshIcon) {
                refreshIcon.style.color = '#dc3545';
                setTimeout(() => {
                    refreshIcon.style.color = '';
                }, 500);
            }
        } finally {
            // Remove spinning animation
            if (refreshIcon) {
                refreshIcon.style.animation = '';
            }
        }
    }

    async clearAllNotifications() {
        try {
            const response = await fetch('/admin/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                this.loadNotifications();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'All notifications marked as read',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        } catch (error) {
            console.error('Error clearing notifications:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error clearing notifications',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    }

    startPolling() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }

        this.intervalId = setInterval(() => {
            this.loadNotifications();
        }, this.updateInterval);
    }

    stopPolling() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    // Public method to manually refresh notifications
    refresh() {
        this.loadNotifications();
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    window.notificationManager = new NotificationManager();
});

// Handle page visibility change to pause/resume polling
document.addEventListener('visibilitychange', function () {
    if (window.notificationManager) {
        if (document.hidden) {
            window.notificationManager.stopPolling();
        } else {
            window.notificationManager.startPolling();
            window.notificationManager.refresh();
        }
    }
});
