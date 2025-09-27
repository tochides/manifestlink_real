<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include '../connect.php';

// Get all manifest records with user information
$query = "SELECT m.*, u.full_name, u.contact_number, u.email, u.address, u.age, u.sex 
          FROM manifest m 
          LEFT JOIN users u ON m.user_id = u.id 
          ORDER BY m.scan_time DESC";
$result = $conn->query($query);

// Get statistics
$total_scans = 15;
$scanned_count = $conn->query("SELECT COUNT(*) as count FROM manifest")->fetch_assoc()['count'];
?>

<div class="manifest-page">
    <!-- Header Section -->
    <div class="manifest-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-title">
                    <h1>Boarding Manifest</h1>
                    <p>Real-time passenger tracking and boarding management</p>
                </div>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="window.print()" title="Print Manifest">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Print
                    </button>
                    <button class="btn btn-outline" onclick="exportToCSV()" title="Export to CSV">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" onclick="addManifestEntry()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Entry
                    </button>
                    <button class="btn btn-outline" id="toggleBulkDelete" onclick="toggleBulkDelete()" title="Toggle Multiple Delete">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="3,6 5,6 21,6"></polyline>
                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                        </svg>
                        Multiple Delete
                    </button>
                    <button class="btn btn-danger" id="bulkDeleteBtn" onclick="deleteSelectedEntries()" style="display: none;">
                        Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_scans; ?></div>
            <div class="stat-label">Total Passenger</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $scanned_count; ?></div>
            <div class="stat-label">Scanned</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="manifest-content">
        <!-- Controls -->
        <div class="controls-bar">
            <div class="search-section">
                <div class="search-box">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="manifestSearch" placeholder="Search passengers...">
                </div>
            </div>
            <div class="filter-section">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="scanned">Scanned</option>
                    <option value="boarded">Boarded</option>
                    <option value="departed">Departed</option>
                </select>
                <select id="vesselFilter" class="filter-select">
                    <option value="">All Vessels</option>
                    <?php
                    $vessels = $conn->query("SELECT DISTINCT vessel_name FROM manifest WHERE vessel_name IS NOT NULL ORDER BY vessel_name");
                    while ($vessel = $vessels->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($vessel['vessel_name']) . '">' . htmlspecialchars($vessel['vessel_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>

        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="manifest-table" id="manifestTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Passenger</th>
                        <th>Contact</th>
                        <th>Scan Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Vessel</th>
                        <th>Destination</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        $counter = 1;
                        while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr data-manifest-id="<?php echo $row['id']; ?>">
                        <td class="row-number"><?php echo $counter; ?></td>
                        <td class="passenger-cell">
                            <div class="passenger-info">
                                <div class="passenger-name"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                <div class="passenger-id">ID: <?php echo $row['user_id']; ?></div>
                            </div>
                        </td>
                        <td class="contact-cell"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                        <td class="time-cell"><?php echo date('M d, Y H:i', strtotime($row['scan_time'])); ?></td>
                        <td class="location-cell"><?php echo htmlspecialchars($row['scan_location']); ?></td>
                        <td class="status-cell">
                            <span class="status-badge status-scanned">
                                Scanned
                            </span>
                        </td>
                        <td class="vessel-cell">
                            <?php if ($row['vessel_name']) { ?>
                                <div class="vessel-info">
                                    <div class="vessel-name"><?php echo htmlspecialchars($row['vessel_name']); ?></div>
                                    <?php if ($row['vessel_number']) { ?>
                                        <div class="vessel-number"><?php echo htmlspecialchars($row['vessel_number']); ?></div>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                        <td class="destination-cell"><?php echo htmlspecialchars($row['destination'] ?? '-'); ?></td>
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <button class="btn-icon" onclick="viewManifestDetails(<?php echo $row['id']; ?>)" title="View Details">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <div class="delete-section">
                                    <input type="checkbox" class="manifest-checkbox" value="<?php echo $row['id']; ?>" style="display: none;">
                                    <button class="btn-icon btn-delete" onclick="deleteManifestEntry(<?php echo $row['id']; ?>)" title="Delete Entry">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php 
                            $counter++;
                        }
                    } else {
                        echo '<tr><td colspan="9" class="no-data">
                                <div class="empty-state">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3>No manifest records found</h3>
                                    <p>Start by adding a new entry or scanning a QR code</p>
                                </div>
                              </td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Manifest Page Styles */
.manifest-page {
    max-width: 100%;
    margin: 0;
    padding: 0;
}

/* Header */
.manifest-header {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 24px;
    padding: 32px 0;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 32px;
}

.header-left {
    flex: 1;
}

.header-title h1 {
    font-size: 28px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
    letter-spacing: -0.025em;
    line-height: 1.2;
}

.header-title p {
    color: #6b7280;
    margin: 0;
    font-size: 16px;
    line-height: 1.5;
    font-weight: 400;
}

.header-right {
    display: flex;
    align-items: center;
}

.header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-right: 20px;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}

.btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.btn-outline {
    background: #fff;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: #f3f4f6;
    color: #374151;
    border-color: #9ca3af;
}

.btn-delete {
    color: #dc2626;
    border-color: #fecaca;
}

.btn-delete:hover {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fca5a5;
}

/* Checkbox and Bulk Actions */
.delete-section {
    display: flex;
    align-items: center;
    gap: 8px;
}

.manifest-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.btn-danger {
    background: #dc2626;
    color: #fff;
}

.btn-danger:hover {
    background: #b91c1c;
    transform: translateY(-1px);
}

.bulk-mode .manifest-checkbox {
    display: inline-block !important;
}

.bulk-mode .btn-delete {
    display: none;
}

/* Statistics */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    text-align: center;
    transition: all 0.2s ease;
}

.stat-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

/* Main Content */
.manifest-content {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

/* Controls */
.controls-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    gap: 20px;
    flex-wrap: wrap;
}

.search-section {
    flex: 1;
    max-width: 400px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.search-box svg {
    position: absolute;
    left: 12px;
    color: #9ca3af;
    z-index: 1;
}

.search-box input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    transition: all 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.filter-section {
    display: flex;
    gap: 12px;
}

.filter-select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Table */
.table-container {
    overflow-x: auto;
    width: 100%;
}

.manifest-table {
    width: 100%;
    min-width: 1200px;
    border-collapse: collapse;
    font-size: 14px;
}

.manifest-table th {
    background: #f9fafb;
    padding: 16px 20px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.manifest-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.manifest-table tbody tr:hover {
    background: #f9fafb;
}

.manifest-table tbody tr:last-child td {
    border-bottom: none;
}

/* Table Cells */
.row-number {
    font-weight: 500;
    color: #6b7280;
    text-align: center;
    width: 40px;
}

.passenger-cell {
    min-width: 180px;
}

.passenger-name {
    font-weight: 500;
    color: #111827;
    margin-bottom: 4px;
}

.passenger-id {
    font-size: 12px;
    color: #6b7280;
}

.contact-cell {
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    color: #374151;
}

.time-cell {
    color: #374151;
    white-space: nowrap;
}

.location-cell {
    color: #374151;
}

.scanner-cell {
    color: #374151;
}

.status-cell {
    text-align: center;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-scanned {
    background: #dbeafe;
    color: #1e40af;
}

.status-boarded {
    background: #fef3c7;
    color: #92400e;
}

.status-departed {
    background: #dcfce7;
    color: #166534;
}

.vessel-cell {
    min-width: 120px;
}

.vessel-name {
    font-weight: 500;
    color: #111827;
    margin-bottom: 2px;
}

.vessel-number {
    font-size: 12px;
    color: #6b7280;
}

.destination-cell {
    color: #374151;
}

.departure-cell {
    color: #374151;
    white-space: nowrap;
}

.actions-cell {
    text-align: center;
    width: 80px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

/* Empty State */
.no-data {
    text-align: center;
    padding: 60px 20px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.empty-state svg {
    color: #9ca3af;
}

.empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.empty-state p {
    color: #6b7280;
    margin: 0;
}

.text-muted {
    color: #9ca3af;
}

/* Responsive */
@media (max-width: 1024px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .header-actions {
        flex-direction: row;
        justify-content: flex-start;
        width: 100%;
    }
    
    .controls-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .search-section {
        max-width: none;
    }
    
    .filter-section {
        justify-content: stretch;
    }
    
    .filter-select {
        flex: 1;
    }
}

@media (max-width: 768px) {
    .manifest-page {
        padding: 0 16px;
    }
    
    .manifest-header {
        padding: 24px 0;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .header-title h1 {
        font-size: 24px;
    }
    
    .header-title p {
        font-size: 14px;
    }
    
    .header-actions {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .btn {
        font-size: 13px;
        padding: 8px 12px;
    }
    
    .manifest-table {
        font-size: 13px;
    }
    
    .manifest-table th,
    .manifest-table td {
        padding: 12px 16px;
    }
}

@media (max-width: 480px) {
    .manifest-header {
        padding: 20px 0;
    }
    
    .header-content {
        flex-direction: column;
        gap: 16px;
    }
    
    .header-title h1 {
        font-size: 20px;
    }
    
    .header-title p {
        font-size: 13px;
    }
    
    .header-actions {
        flex-direction: column;
        width: 100%;
        gap: 8px;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

/* Print Styles */
@media print {
    .header-actions,
    .controls-bar,
    .action-buttons {
        display: none !important;
    }
    
    .manifest-page {
        max-width: none;
        padding: 0;
    }
    
    .manifest-content {
        border: none;
        border-radius: 0;
    }
    
    .manifest-table th {
        background: #f3f4f6 !important;
    }
}

/* Dark Mode Styles - Matching Admin Panel Theme */
body.dark-mode .manifest-page {
    background: #1e2533;
}

body.dark-mode .manifest-header {
    background: linear-gradient(135deg, #1e2533 0%, #232b3b 100%);
    border-bottom-color: #2d3748;
}

body.dark-mode .header-title h1 {
    color: #e0e7ef;
}

body.dark-mode .header-title p {
    color: #b6c3e0;
}

body.dark-mode .btn-outline {
    background: #232b3b;
    color: #e0e7ef;
    border-color: #2d3748;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

body.dark-mode .btn-outline:hover {
    background: #2d3748;
    border-color: #4a5568;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

body.dark-mode .btn-icon {
    background: #232b3b;
    color: #b6c3e0;
    border-color: #2d3748;
}

body.dark-mode .btn-icon:hover {
    background: #2d3748;
    color: #e0e7ef;
    border-color: #4a5568;
}

body.dark-mode .stat-card {
    background: #232b3b;
    border-color: #2d3748;
    box-shadow: 0 2px 12px rgba(30,41,59,0.18);
}

body.dark-mode .stat-card:hover {
    border-color: #4a5568;
    box-shadow: 0 4px 16px rgba(30,41,59,0.25);
}

body.dark-mode .stat-number {
    color: #93c5fd;
}

body.dark-mode .stat-label {
    color: #b6c3e0;
}

body.dark-mode .manifest-content {
    background: #232b3b;
    border-color: #2d3748;
    box-shadow: 0 2px 12px rgba(30,41,59,0.18);
}

body.dark-mode .controls-bar {
    border-bottom-color: #2d3748;
}

body.dark-mode .search-box input {
    background: #2d3748;
    border-color: #4a5568;
    color: #e0e7ef;
}

body.dark-mode .search-box input:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

body.dark-mode .search-box input::placeholder {
    color: #718096;
}

body.dark-mode .filter-select {
    background: #2d3748;
    border-color: #4a5568;
    color: #e0e7ef;
}

body.dark-mode .filter-select:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

body.dark-mode .manifest-table th {
    background: #2d3748;
    color: #e0e7ef;
    border-bottom-color: #4a5568;
}

body.dark-mode .manifest-table td {
    border-bottom-color: #2d3748;
    color: #e0e7ef;
}

body.dark-mode .manifest-table tbody tr:hover {
    background: #2d3748;
}

body.dark-mode .row-number {
    color: #718096;
}

body.dark-mode .passenger-name {
    color: #e0e7ef;
}

body.dark-mode .passenger-id {
    color: #718096;
}

body.dark-mode .contact-cell {
    color: #e0e7ef;
}

body.dark-mode .time-cell {
    color: #e0e7ef;
}

body.dark-mode .location-cell {
    color: #e0e7ef;
}

body.dark-mode .scanner-cell {
    color: #e0e7ef;
}

body.dark-mode .vessel-name {
    color: #e0e7ef;
}

body.dark-mode .vessel-number {
    color: #718096;
}

body.dark-mode .destination-cell {
    color: #e0e7ef;
}

body.dark-mode .departure-cell {
    color: #e0e7ef;
}

body.dark-mode .text-muted {
    color: #718096;
}

body.dark-mode .empty-state svg {
    color: #718096;
}

body.dark-mode .empty-state h3 {
    color: #e0e7ef;
}

body.dark-mode .empty-state p {
    color: #b6c3e0;
}

/* Dark mode status badges - Enhanced visibility */
body.dark-mode .status-scanned {
    background: rgba(96, 165, 250, 0.15);
    color: #93c5fd;
    border: 1px solid rgba(96, 165, 250, 0.3);
}

body.dark-mode .status-boarded {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

body.dark-mode .status-departed {
    background: rgba(34, 197, 94, 0.15);
    color: #86efac;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
</style>

<script>
document.getElementById('manifestSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#manifestTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

document.getElementById('vesselFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const vesselFilter = document.getElementById('vesselFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#manifestTable tbody tr');
    
    rows.forEach(row => {
        const statusCell = row.querySelector('.status-badge');
        const vesselCell = row.querySelector('.vessel-name');
        
        const status = statusCell ? statusCell.textContent.toLowerCase() : '';
        const vessel = vesselCell ? vesselCell.textContent.toLowerCase() : '';
        
        const statusMatch = !statusFilter || status === statusFilter;
        const vesselMatch = !vesselFilter || vessel === vesselFilter;
        
        row.style.display = (statusMatch && vesselMatch) ? '' : 'none';
    });
}

function viewManifestDetails(manifestId) {
    window.open(`modal_manifest_details.php?manifest_id=${manifestId}`, '_blank', 'width=600,height=500');
}

function updateStatus(manifestId) {
    const newStatus = prompt('Update status (scanned/boarded/departed):');
    if (newStatus && ['scanned', 'boarded', 'departed'].includes(newStatus.toLowerCase())) {
        fetch('update_manifest_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ manifest_id: manifestId, status: newStatus.toLowerCase() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Update failed: ' + data.message);
            }
        });
    }
}

function addManifestEntry() {
    window.open('modal_add_manifest.php', '_blank', 'width=600,height=500');
}

function exportToCSV() {
    const table = document.getElementById('manifestTable');
    const rows = table.querySelectorAll('tbody tr');
    let csv = 'Passenger Name,Contact Number,Scan Time,Scan Location,Scanned By,Boarding Status,Vessel,Destination,Departure Time\n';
    
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const rowData = [
                cells[1].querySelector('.passenger-name').textContent,
                cells[2].textContent,
                cells[3].textContent,
                cells[4].textContent,
                cells[5].textContent,
                cells[6].querySelector('.status-badge').textContent,
                cells[7].querySelector('.vessel-name') ? cells[7].querySelector('.vessel-name').textContent : '-',
                cells[8].textContent,
                cells[9].textContent
            ];
            csv += rowData.map(cell => `"${cell}"`).join(',') + '\n';
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'boarding_manifest.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script> 