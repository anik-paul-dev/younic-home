import './bootstrap';
import io from 'socket.io-client';

document.addEventListener('DOMContentLoaded', () => {
    // --- Socket.IO Setup ---
    const userId = window.AppConfig?.userId;
    const socketHost = window.AppConfig?.socketHost || 'http://localhost';
    const socketPort = window.AppConfig?.socketPort || '6001';

    if (userId) {
        const socket = io(`${socketHost}:${socketPort}`);

        socket.on('connect', () => {
            console.log('Connected to realtime server');
        });

        // Listen for specific user notifications
        socket.on(`user-notification:${userId}`, (data) => {
            showToast(data.title, data.message, data.type);
            updateNotificationBadge();
            addNotificationToList(data);
        });

        // Listen for admin broadcasts
        socket.on('admin-notification', (data) => {
            showToast(data.title, data.message, 'info');
            if (window.AppConfig.isAdmin) {
                updateNotificationBadge();
                addNotificationToList(data);
            }
        });

        // General announcements
        socket.on('announcement', (data) => {
            showToast(data.title, data.message, 'announcement');
            updateNotificationBadge();
            addNotificationToList({ ...data, type: 'announcement', created_at: new Date().toISOString() });
        });
    }

    // --- UI Interactions ---

    // Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // Notifications Dropdown
    const notifBtn = document.getElementById('notification-btn');
    const notifDropdown = document.getElementById('notification-dropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }
        });
    }

    // Toast Notification System
    window.showToast = function(title, message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const colors = {
            info: 'bg-blue-500/20 border-blue-500/50 text-blue-200',
            success: 'bg-emerald-500/20 border-emerald-500/50 text-emerald-200',
            warning: 'bg-amber-500/20 border-amber-500/50 text-amber-200',
            error: 'bg-red-500/20 border-red-500/50 text-red-200',
            announcement: 'bg-purple-500/20 border-purple-500/50 text-purple-200'
        };

        const iconColors = {
            info: 'text-blue-400', success: 'text-emerald-400',
            warning: 'text-amber-400', error: 'text-red-400', announcement: 'text-purple-400'
        };

        // Fallback to info
        const colorClass = colors[type] || colors.info;
        const iconColor = iconColors[type] || iconColors.info;

        const toast = document.createElement('div');
        toast.className = `toast-enter p-4 rounded-lg border backdrop-blur-md shadow-lg flex items-start space-x-3 w-80 ${colorClass}`;
        
        toast.innerHTML = `
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold">${title}</h4>
                <p class="text-xs mt-1 opacity-90">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    };

    function updateNotificationBadge() {
        const badge = document.getElementById('notif-badge');
        if (badge) {
            const current = parseInt(badge.innerText || '0');
            badge.innerText = current + 1;
            badge.classList.remove('hidden');
        }
    }

    function addNotificationToList(data) {
        const list = document.getElementById('notif-list');
        const emptyState = document.getElementById('notif-empty');
        if (emptyState) emptyState.classList.add('hidden');
        
        if (list) {
            const item = document.createElement('div');
            item.className = 'p-3 hover:bg-slate-700/50 transition border-b border-slate-700 last:border-0';
            item.innerHTML = `
                <p class="text-sm font-medium text-slate-200">${data.title}</p>
                <p class="text-xs text-slate-400 mt-1">${data.message}</p>
            `;
            list.prepend(item);
        }
    }
});
