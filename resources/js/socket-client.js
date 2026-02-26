/**
 * SocketManager
 * Handles Socket.IO connection and user tracking
 */

class SocketManager {
    constructor() {
        this.socket = null;
        this.connected = false;
        this.config = window.SOCKET_CONFIG || {};
        this.serverUrl = window.SOCKET_SERVER_URL || 'http://localhost:3000';
        this.listeners = new Map();

        this.initGuestId();
        this.init();
    }

    initGuestId() {
        let guestId = localStorage.getItem('guest_id');
        if (!guestId) {
            guestId = this.generateUUID();
            localStorage.setItem('guest_id', guestId);
        }
        this.guestId = guestId;
    }

    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    init() {
        if (!this.config.enabled) {
            console.log('Socket tracking is disabled');
            return;
        }

        if (typeof io === 'undefined') {
            console.error('Socket.io client not loaded');
            return;
        }

        this.socket = io(this.serverUrl, {
            reconnectionAttempts: this.config.reconnectionAttempts || 5,
            reconnectionDelay: this.config.reconnectionDelay || 3000,
            timeout: this.config.timeout || 10000,
        });

        this.socket.on('connect', () => {
            this.connected = true;
            this.identify();
            this.trigger('connect');
        });

        this.socket.on('disconnect', () => {
            this.connected = false;
            this.trigger('disconnect');
        });

        this.socket.on('connect_error', (error) => {
            this.trigger('error', error);
        });

        // Dynamic event forwarding
        const events = ['users:update', 'stats:counts', 'notification:new', 'notification:admin'];
        events.forEach(event => {
            this.socket.on(event, (data) => {
                this.trigger(event, data);
            });
        });
    }

    identify() {
        if (!this.socket) return;

        const platform = this.getPlatform();
        const info = {
            userId: document.querySelector('meta[name="user-id"]')?.content || null,
            guestId: this.guestId,
            userName: document.querySelector('meta[name="user-name"]')?.content || null,
            userEmail: document.querySelector('meta[name="user-email"]')?.content || null,
            userAvatar: document.querySelector('meta[name="user-avatar"]')?.content || null,
            platform: platform,
            device: this.getDeviceType(),
            currentPage: window.location.pathname,
            referrer: document.referrer
        };

        this.socket.emit('user:identify', info);
    }

    getPlatform() {
        // Check if we are in admin area
        if (window.location.pathname.startsWith('/admin')) {
            return 'admin';
        }
        // Check for mobile app (you might have a specific JS var or check)
        if (window.isMobileApp) {
            return 'app';
        }
        return 'web';
    }

    getDeviceType() {
        const ua = navigator.userAgent;
        if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
            return 'tablet';
        }
        if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/i.test(ua)) {
            return 'mobile';
        }
        return 'desktop';
    }

    trackPageChange(page) {
        if (this.socket && this.connected) {
            this.socket.emit('page:change', { page: page || window.location.pathname });
        }
    }

    requestAdminStats() {
        if (this.socket && this.connected) {
            this.socket.emit('admin:request_stats');
        }
    }

    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    trigger(event, data) {
        if (this.listeners.has(event)) {
            this.listeners.get(event).forEach(callback => callback(data));
        }
    }

    isConnected() {
        return this.connected;
    }
}

// Initialize and make available globally
window.SocketManager = new SocketManager();

// Track page transitions if using SPAs or just on load
document.addEventListener('DOMContentLoaded', () => {
    // For normal page loads, identification already happens on connect
    // If you have client-side routing, you should call window.SocketManager.trackPageChange()
});
