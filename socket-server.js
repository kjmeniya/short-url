import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import cors from 'cors';
import dotenv from 'dotenv';

dotenv.config();

const app = express();
app.use(cors());
app.use(express.json()); // Parse JSON request bodies

const httpServer = createServer(app);
const io = new Server(httpServer, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const PORT = process.env.SOCKET_SERVER_PORT || 3000;

// Store connected users
// key: socket.id, value: user info
const users = new Map();

// Laravel endpoint configuration
// In production, these should be set in environment variables
const LARAVEL_API_URL = process.env.LARAVEL_API_URL || 'http://localhost:8000/admin/live/page-visit';
const INTERNAL_TOKEN = process.env.INTERNAL_ANALYTICS_TOKEN || 'secret';

/**
 * Unified API Endpoint: Send notifications to admins and/or app users
 * POST /api/notification
 * 
 * Parameters:
 * - target: 'admin' | 'app' | 'all' (required)
 * - type: 'info' | 'success' | 'warning' | 'error'
 * - title: notification title (required)
 * - message: notification message
 * - data: additional data object
 * - user_id: specific user ID (optional, for targeted app notifications)
 */
app.post('/api/notification', (req, res) => {
    // Verify internal token for security
    const token = req.headers['x-internal-token'];
    if (token !== INTERNAL_TOKEN) {
        console.error('[Socket] Unauthorized notification attempt');
        return res.status(401).json({ success: false, message: 'Unauthorized' });
    }

    const { target, type, title, message, data, user_id } = req.body;

    // Validate required fields
    if (!title) {
        return res.status(400).json({ success: false, message: 'Title is required' });
    }

    if (!target || !['admin', 'app', 'all'].includes(target)) {
        return res.status(400).json({
            success: false,
            message: 'Target is required and must be one of: admin, app, all'
        });
    }

    // Build notification object
    const notification = {
        type: type || 'info',
        title: title,
        message: message || '',
        data: data || {},
        timestamp: new Date().toISOString()
    };

    let sentTo = [];

    // Send to admins
    if (target === 'admin' || target === 'all') {
        io.to('admin-room').emit('notification:admin', notification);
        sentTo.push('admin');
        console.log(`[Socket] Notification sent to admin-room: "${title}"`);
    }

    // Send to app users
    if (target === 'app' || target === 'all') {
        if (user_id) {
            // Find socket for specific user
            let targetSocket = null;
            for (const [socketId, userInfo] of users.entries()) {
                if (userInfo.userId === user_id && userInfo.platform === 'app') {
                    targetSocket = socketId;
                    break;
                }
            }

            if (targetSocket) {
                io.to(targetSocket).emit('notification:app', notification);
                sentTo.push(`app-user-${user_id}`);
                console.log(`[Socket] Notification sent to app user ${user_id}: "${title}"`);
            } else {
                console.log(`[Socket] App user ${user_id} not connected`);
            }
        } else {
            // Broadcast to all app users
            io.to('app-room').emit('notification:app', notification);
            sentTo.push('app-all');
            console.log(`[Socket] Notification broadcasted to app-room: "${title}"`);
        }
    }

    return res.json({
        success: true,
        message: 'Notification sent',
        sent_to: sentTo,
        user_id: user_id || null
    });
});



io.on('connection', (socket) => {
    // Handle user Identification
    socket.on('user:identify', (data) => {
        const userInfo = {
            id: socket.id,
            userId: data.userId || null,
            guestId: data.guestId || null,
            userName: data.userName || (data.userId ? 'Authenticated User' : 'Guest'),
            userEmail: data.userEmail || null,
            userAvatar: data.userAvatar || null,
            platform: data.platform || 'web',
            device: data.device || 'desktop',
            currentPage: data.currentPage || '/',
            ipAddress: socket.handshake.address,
            connectedAt: new Date().toISOString(),
            lastActivity: new Date().toISOString()
        };

        users.set(socket.id, userInfo);

        // Join appropriate rooms based on platform
        if (userInfo.platform === 'admin') {
            socket.join('admin-room');
            console.log(`[Socket] User joined admin-room: ${userInfo.userName}`);
        } else if (userInfo.platform === 'app') {
            socket.join('app-room');
            console.log(`[Socket] User joined app-room: ${userInfo.userName} (ID: ${userInfo.userId})`);
        }

        broadcastStats();
        notifyLaravel(userInfo);
    });

    // Handle page changes
    socket.on('page:change', (data) => {
        const user = users.get(socket.id);
        if (user) {
            user.currentPage = data.page || '/';
            user.lastActivity = new Date().toISOString();
            users.set(socket.id, user);
            broadcastStats();
            notifyLaravel(user);
        }
    });

    // Handle admin stats request
    socket.on('admin:request_stats', () => {
        sendStatsToSocket(socket);
    });

    socket.on('disconnect', () => {
        users.delete(socket.id);
        broadcastStats();
    });
});

function getStats() {
    const userList = Array.from(users.values());
    const stats = {
        total: userList.length,
        web: userList.filter(u => u.platform === 'web').length,
        mobile: userList.filter(u => u.device === 'mobile').length,
        tablet: userList.filter(u => u.device === 'tablet').length,
        desktop: userList.filter(u => u.device === 'desktop').length,
        admin: userList.filter(u => u.platform === 'admin').length,
        app: userList.filter(u => u.platform === 'app').length,
        authenticated: userList.filter(u => u.userId !== null).length,
        guest: userList.filter(u => u.userId === null).length,
        users: userList // Full list for admin dashboard
    };
    return stats;
}

function broadcastStats() {
    const stats = getStats();
    // Broadcast to admins only for privacy/performance, 
    // or broadcast just the counts to everyone if needed.
    // Here we broadcast to the admin room.
    io.to('admin-room').emit('users:update', stats);

    // Also emit basic counts to everyone if needed for a "online now" badge
    io.emit('stats:counts', {
        total: stats.total,
        authenticated: stats.authenticated,
        guest: stats.guest
    });
}

function sendStatsToSocket(socket) {
    const stats = getStats();
    socket.emit('users:update', stats);
}

/**
 * Notify Laravel about a page visit
 * This is non-blocking - we don't await the result
 */
function notifyLaravel(user) {
    if (!user.currentPage) return;

    const payload = {
        user_id: user.userId,
        guest_id: user.guestId,
        page: user.currentPage,
        platform: user.platform,
        device: user.device,
        ip: user.ipAddress,
        visited_at: new Date().toISOString()
    };

    const options = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Internal-Token': INTERNAL_TOKEN
        },
        body: JSON.stringify(payload)
    };

    // Use native fetch (Node 18+) or a basic http request to avoid dependencies
    // Fire and forget
    if (globalThis.fetch) {
        globalThis.fetch(LARAVEL_API_URL, options)
            .then(res => {
                if (!res.ok) console.error(`[Socket] Laravel API returned error: ${res.status}`);
            })
            .catch(err => {
                console.error(`[Socket] Failed to connect to Laravel API: ${err.message}`);
            });
    } else {
        // Fallback for older Node versions if needed, but modern Node has fetch
        try {
            const url = new URL(LARAVEL_API_URL);
            const http = url.protocol === 'https:' ? require('https') : require('http');
            const req = http.request(url, options, () => { });
            req.on('error', () => { });
            req.write(JSON.stringify(payload));
            req.end();
        } catch (e) { }
    }
}

httpServer.listen(PORT, () => {
    console.log(`Socket server running on port ${PORT}`);
});
