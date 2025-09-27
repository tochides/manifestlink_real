<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <style>
    .modal-bg {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.3);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-bg.active {
        display: flex;
    }
    .modal {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 32px rgba(0,0,0,0.18);
        max-width: 500px;
        width: 100%;
        padding: 0;
        position: relative;
        animation: fadeIn .2s;
    }
    .modal-header {
        padding: 18px 24px 0 24px;
        font-size: 20px;
        color: #3b82f6;
        font-weight: 600;
    }
    .modal-close {
        position: absolute;
        top: 12px;
        right: 18px;
        font-size: 22px;
        color: #888;
        cursor: pointer;
        background: none;
        border: none;
    }
    .modal-content {
        padding: 24px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function loadContent(page, pushState = true) {
            const main = document.getElementById('main-content');
            main.innerHTML = '<div style="text-align:center; color:#888; padding:40px;">Loading...</div>';
            fetch(page)
                .then(res => res.text())
                .then(html => {
                    main.innerHTML = html;
                    if (page === 'content_users.php') attachUserActions();
                    if (page === 'content_qr_scanner.php') setupQrCamera();
                });
            if (pushState) {
                history.pushState({page: page}, '', '?page=' + page.replace('content_', '').replace('.php', ''));
            }
            document.querySelectorAll('.sidebar nav a').forEach(a => a.classList.remove('active'));
            const activeLink = document.querySelector('.sidebar nav a[data-page="' + page + '"]');
            if (activeLink) activeLink.classList.add('active');
        }
        // Modal logic
        const modalBg = document.createElement('div');
        modalBg.className = 'modal-bg';
        modalBg.innerHTML = '<div class="modal"><button class="modal-close" title="Close">&times;</button><div class="modal-header"></div><div class="modal-content"></div></div>';
        document.body.appendChild(modalBg);
        const modal = modalBg.querySelector('.modal');
        const modalHeader = modalBg.querySelector('.modal-header');
        const modalContent = modalBg.querySelector('.modal-content');
        modalBg.querySelector('.modal-close').onclick = closeModal;
        modalBg.onclick = function(e) { if (e.target === modalBg) closeModal(); };
        function openModal(title, url) {
            modalHeader.textContent = title;
            modalContent.innerHTML = '<div style="text-align:center; color:#888; padding:24px;">Loading...</div>';
            modalBg.classList.add('active');
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    modalContent.innerHTML = html;
                    attachModalFormHandler();
                });
        }
        function closeModal() {
            modalBg.classList.remove('active');
        }
        // Attach form handler for modal
        function attachModalFormHandler() {
            const form = modalContent.querySelector('form');
            if (!form) return;
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(resp => {
                    if (resp.trim() === 'success') {
                        closeModal();
                        loadContent('content_users.php', false);
                    } else {
                        modalContent.innerHTML = resp;
                        attachModalFormHandler();
                    }
                });
            };
        }
        // Attach actions for user management
        function attachUserActions() {
            // Add User
            const addBtn = document.querySelector('.add-btn');
            if (addBtn) {
                addBtn.onclick = function(e) {
                    e.preventDefault();
                    openModal('Add User', 'modal_user_add.php');
                };
            }
            // Edit User
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.onclick = function(e) {
                    e.preventDefault();
                    openModal('Edit User', this.getAttribute('href'));
                };
            });
            // Delete User (AJAX)
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.onclick = function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this user?')) {
                        fetch(this.getAttribute('href'))
                            .then(res => res.text())
                            .then(resp => {
                                if (resp.trim() === 'success') {
                                    loadContent('content_users.php', false);
                                } else {
                                    alert('Delete failed.');
                                }
                            });
                    }
                };
            });
        }
        // Sidebar link click
        document.querySelectorAll('.sidebar nav a').forEach(a => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                loadContent(this.getAttribute('data-page'));
            });
        });
        // Handle browser navigation
        window.onpopstate = function(event) {
            if (event.state && event.state.page) {
                loadContent(event.state.page, false);
            }
        };
        // Load default or from URL
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        if (page === 'users') loadContent('content_users.php', false);
        else if (page === 'qrcodes') loadContent('content_qrcodes.php', false);
        else if (page === 'otps') loadContent('content_otps.php', false);
        else if (page === 'qr_scanner') loadContent('content_qr_scanner.php', false);
        else if (page === 'manifest') loadContent('content_manifest.php', false);
        else loadContent('content_dashboard.php', false);
        // Global handler for OTP delete (invalidate) button
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-otp-btn')) {
                e.preventDefault();
                if (!confirm('Delete (invalidate) this OTP?')) return;
                const id = e.target.getAttribute('data-id');
                fetch('admin_otp_delete.php?id=' + encodeURIComponent(id))
                    .then(res => res.text())
                    .then(resp => {
                        if (resp.trim() === 'success') {
                            loadContent('content_otps.php', false);
                        } else {
                            alert('Delete failed.');
                        }
                    });
            }
        });
        
        // Global handler for manifest delete button
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                e.preventDefault();
                const button = e.target.classList.contains('btn-delete') ? e.target : e.target.closest('.btn-delete');
                const manifestId = button.getAttribute('onclick').match(/deleteManifestEntry\((\d+)\)/)[1];
                if (confirm('Are you sure you want to delete this manifest entry?')) {
                    fetch('delete_manifest_entry.php?id=' + encodeURIComponent(manifestId))
                        .then(res => res.text())
                        .then(resp => {
                            if (resp.trim() === 'success') {
                                loadContent('content_manifest.php', false);
                            } else {
                                alert('Delete failed: ' + resp);
                            }
                        })
                        .catch(error => {
                            alert('Delete failed: ' + error.message);
                        });
                }
            }
        });
        
        // Global handler for manifest bulk actions
        document.addEventListener('click', function(e) {
            // Individual checkboxes
            if (e.target.classList.contains('manifest-checkbox')) {
                updateBulkActions();
            }
        });
        
        // Global function for bulk delete
        window.deleteSelectedEntries = function() {
            const selectedCheckboxes = document.querySelectorAll('.manifest-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one entry to delete.');
                return;
            }
            
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            const count = selectedIds.length;
            
            if (confirm(`Are you sure you want to delete ${count} selected manifest entr${count === 1 ? 'y' : 'ies'}?`)) {
                fetch('delete_manifest_entries.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: selectedIds })
                })
                .then(res => res.text())
                .then(resp => {
                    if (resp.trim() === 'success') {
                        loadContent('content_manifest.php', false);
                    } else {
                        alert('Delete failed: ' + resp);
                    }
                })
                .catch(error => {
                    alert('Delete failed: ' + error.message);
                });
            }
        };
        
        // Global function to toggle bulk delete mode
        window.toggleBulkDelete = function() {
            const table = document.getElementById('manifestTable');
            const toggleBtn = document.getElementById('toggleBulkDelete');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const checkboxes = document.querySelectorAll('.manifest-checkbox');
            
            if (table.classList.contains('bulk-mode')) {
                // Exit bulk mode
                table.classList.remove('bulk-mode');
                toggleBtn.textContent = 'Multiple Delete';
                toggleBtn.classList.remove('btn-danger');
                toggleBtn.classList.add('btn-outline');
                bulkDeleteBtn.style.display = 'none';
                checkboxes.forEach(cb => cb.checked = false);
            } else {
                // Enter bulk mode
                table.classList.add('bulk-mode');
                toggleBtn.textContent = 'Cancel';
                toggleBtn.classList.remove('btn-outline');
                toggleBtn.classList.add('btn-danger');
                bulkDeleteBtn.style.display = 'inline-flex';
            }
            updateBulkActions();
        };
        
        // Function to update bulk actions visibility
        function updateBulkActions() {
            const selectedCheckboxes = document.querySelectorAll('.manifest-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const selectedCount = document.getElementById('selectedCount');
            
            if (bulkDeleteBtn) {
                if (selectedCheckboxes.length > 0) {
                    bulkDeleteBtn.style.display = 'inline-flex';
                    selectedCount.textContent = selectedCheckboxes.length;
                } else {
                    bulkDeleteBtn.style.display = 'none';
                }
            }
        }
    });
    function setupQrCamera() {
        console.log('QR Scanner script loaded');
        const video = document.getElementById('qr-video');
        const startBtn = document.getElementById('start-camera');
        const stopBtn = document.getElementById('stop-camera');
        const manifestResult = document.getElementById('manifest-result');
        let stream = null;
        let scanning = false;
        let lastScannedData = '';

        // Add or get hidden canvas for frame capture
        let canvas = document.getElementById('qr-canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'qr-canvas';
            canvas.style.display = 'none';
            video.parentNode.appendChild(canvas);
        }

        if (!video || !startBtn || !stopBtn) return;

        startBtn.onclick = async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-flex';
                scanning = true;
                console.log('Camera started, beginning QR scan loop...');
                scanQRCode();
            } catch (err) {
                alert('Camera error: ' + err.name + ' - ' + err.message);
            }
        };

        stopBtn.onclick = function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.srcObject = null;
            startBtn.style.display = 'inline-flex';
            stopBtn.style.display = 'none';
            scanning = false;
            console.log('Camera stopped.');
        };

        function scanQRCode() {
            if (!scanning) return;
            if (video.readyState !== video.HAVE_ENOUGH_DATA) {
                requestAnimationFrame(scanQRCode);
                return;
            }
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            if (typeof jsQR === 'undefined') {
                console.error('jsQR is not loaded!');
                showScanResult('error', 'QR scanning library (jsQR) not loaded.');
                return;
            }
            console.log('Scanning frame...');
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            if (code && code.data && code.data !== lastScannedData) {
                console.log('QR code detected:', code.data);
                lastScannedData = code.data;
                scanning = false;
                stopBtn.click();
                showScanResult('info', 'Processing QR code...');
                
                // Get trip details
                const port = document.getElementById('port-select') ? document.getElementById('port-select').value : '';
                const destination = document.getElementById('destination-select') ? document.getElementById('destination-select').value : '';
                
                // Send QR data to server with trip details
                fetch('find_user_by_qr.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        qr_data: code.data,
                        port: port,
                        destination: destination,
                        vessel: document.getElementById('vessel-select') ? document.getElementById('vessel-select').value : ''
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user_id) {
                        if (data.manifest_created) {
                            showScanResult('success', `QR Code scanned! Manifest entry created for <b>${data.user_name}</b>.`);
                        } else if (data.already_scanned) {
                            showScanResult('warning', `Passenger <b>${data.user_name}</b> already scanned recently.`);
                        } else {
                            showScanResult('info', `User found: <b>${data.user_name}</b>.`);
                        }
                        // Optionally, load manifest info here
                    } else {
                        showScanResult('error', data.message || 'User not found for this QR code');
                    }
                })
                .catch(error => {
                    showScanResult('error', 'Error processing QR code');
                });
            } else {
                requestAnimationFrame(scanQRCode);
            }
        }

        function showScanResult(type, message) {
            if (!manifestResult) return;
            const alertClass = type === 'success' ? 'success-message' :
                              type === 'warning' ? 'warning-message' :
                              type === 'info' ? 'info-message' : 'error-message';
            manifestResult.innerHTML = `<div class="${alertClass}">${message}</div>`;
            manifestResult.style.display = 'block';
            setTimeout(() => {
                if (manifestResult.querySelector(`.${alertClass}`)) {
                    manifestResult.style.display = 'none';
                }
            }, 5000);
        }

        // Manual entry functionality
        const viewManifestBtn = document.getElementById('view-manifest');
        if (viewManifestBtn) {
            viewManifestBtn.onclick = function() {
                const email = document.getElementById('manual-user-email').value.trim();
                const port = document.getElementById('port-select') ? document.getElementById('port-select').value : '';
                const destination = document.getElementById('destination-select') ? document.getElementById('destination-select').value : '';
                
                if (!email) {
                    alert('Please enter an email address');
                    return;
                }
                
                manifestResult.innerHTML = '<div style="text-align:center; padding:40px; color:#888;">Loading manifest...</div>';
                manifestResult.style.display = 'block';
                
                // Create URL with parameters
                let url = 'get_user_manifest.php?email=' + encodeURIComponent(email);
                if (port) url += '&port=' + encodeURIComponent(port);
                if (destination) url += '&destination=' + encodeURIComponent(destination);
                var vessel = document.getElementById('vessel-select') ? document.getElementById('vessel-select').value : '';
                if (vessel) url += '&vessel=' + encodeURIComponent(vessel);
                
                fetch(url)
                    .then(function(response) { 
                        return response.json(); 
                    })
                    .then(function(data) {
                        if (data.success) {
                            var user = data.user;
                            var initials = user.full_name.split(' ').map(function(n) { 
                                return n[0]; 
                            }).join('').toUpperCase();
                            
                            var manifestStatus = '';
                            if (data.manifest_created) {
                                manifestStatus = '<div class="success-message" style="margin-bottom: 20px; padding: 12px; border-radius: 6px; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">✓ Manifest entry created successfully!</div>';
                            } else if (data.already_scanned) {
                                manifestStatus = '<div class="warning-message" style="margin-bottom: 20px; padding: 12px; border-radius: 6px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">⚠ Passenger already scanned recently</div>';
                            }
                            
                            manifestResult.innerHTML = 
                                manifestStatus +
                                '<div class="manifest-user-card">' +
                                    '<div class="manifest-header">' +
                                        '<div class="manifest-avatar">' + initials + '</div>' +
                                        '<div class="manifest-info">' +
                                            '<h2>' + user.full_name + '</h2>' +
                                            '<p>User ID: ' + user.id + '</p>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="manifest-details">' +
                                        '<div class="manifest-field"><label>Full Name</label><div class="value">' + user.full_name + '</div></div>' +
                                        '<div class="manifest-field"><label>Contact Number</label><div class="value">' + user.contact_number + '</div></div>' +
                                        '<div class="manifest-field"><label>Email Address</label><div class="value">' + user.email + '</div></div>' +
                                        '<div class="manifest-field"><label>Address</label><div class="value">' + user.address + '</div></div>' +
                                        '<div class="manifest-field"><label>Age</label><div class="value">' + user.age + ' years old</div></div>' +
                                        '<div class="manifest-field"><label>Sex</label><div class="value">' + user.sex + '</div></div>' +
                                        '<div class="manifest-field"><label>Registration Date</label><div class="value">' + new Date(user.created_at).toLocaleDateString() + '</div></div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="manifest-actions">' +
                                    '<button class="btn btn-primary" onclick="document.getElementById(\'manual-user-email\').value=\'\';document.getElementById(\'manual-user-email\').focus();document.getElementById(\'manifest-result\').style.display=\'none\';">Done</button>' +
                                '</div>';
                        } else {
                            manifestResult.innerHTML = '<div class="error-message">User not found or error loading manifest</div>';
                        }
                    })
                    .catch(function(error) {
                        manifestResult.innerHTML = '<div class="error-message">Error loading manifest</div>';
                    });
            };
        }
    }
    </script>
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-inner">
            <div class="sidebar-top-controls">
                <button class="sidebar-toggle" title="Collapse Sidebar">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </button>
            </div>
            <div class="logo">
                <img src="../logo.png" alt="Logo">
            </div>
            <div class="admin-name">Welcome, <?php echo ucfirst(trim(htmlspecialchars($admin_username))); ?></div>
            <nav>
                <a href="#" data-page="content_dashboard.php" class="active">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="#" data-page="content_users.php">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="17" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    <span>User Management</span>
                </a>
                <a href="#" data-page="content_qrcodes.php">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/></svg>
                    <span>QR Code Management</span>
                </a>
                <a href="#" data-page="content_otps.php">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>OTP Verification</span>
                </a>
                <a href="#" data-page="content_qr_scanner.php">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                    <span>QR Scanner</span>
                </a>
                <a href="#" data-page="content_manifest.php">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Manifest</span>
                </a>
            </nav>
            <div class="nav-separator"></div>
            <button class="darkmode-toggle" title="Toggle Dark Mode">
                <svg class="sun" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="moon" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none"><path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z"/></svg>
                <span class="darkmode-label">Theme</span>
            </button>
            <a href="admin_logout.php" class="logout-link">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    <main class="main-content" id="main-content">
        <!-- Content will be loaded here -->
    </main>
</div>
<script>
// Collapsible sidebar and dark mode logic
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const darkToggle = document.querySelector('.darkmode-toggle');
    const sunIcon = darkToggle.querySelector('.sun');
    const moonIcon = darkToggle.querySelector('.moon');
    // Sidebar collapse
    function setSidebarCollapsed(collapsed) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }
        localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
    }
    sidebarToggle.onclick = function() {
        setSidebarCollapsed(!sidebar.classList.contains('collapsed'));
    };
    // Restore sidebar state
    if (localStorage.getItem('sidebarCollapsed') === '1') {
        sidebar.classList.add('collapsed');
    }
    // Dark mode
    function setDarkMode(enabled) {
        if (enabled) {
            document.body.classList.add('dark-mode');
            sunIcon.style.display = 'none';
            moonIcon.style.display = '';
        } else {
            document.body.classList.remove('dark-mode');
            sunIcon.style.display = '';
            moonIcon.style.display = 'none';
        }
        localStorage.setItem('darkMode', enabled ? '1' : '0');
    }
    darkToggle.onclick = function() {
        setDarkMode(!document.body.classList.contains('dark-mode'));
    };
    // Restore dark mode state
    if (localStorage.getItem('darkMode') === '1') {
        setDarkMode(true);
    } else {
        setDarkMode(false);
    }
});
</script>
</body>
</html> 