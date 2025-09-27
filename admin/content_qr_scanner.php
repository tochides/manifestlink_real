<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
?>

<div class="qr-scanner-page">
    <!-- Header Section -->
    <div class="scanner-header">
        <div class="header-content">
            <div class="header-left">
                            <div class="header-title">
                <h1>Passenger QR Scanner</h1>
                <p>Scan passenger QR codes to verify boarding and manage manifest entries</p>
            </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="scanner-content">
        <!-- Scanner Section -->
        <div class="scanner-section">
            <div class="section-header">
                <h2>Camera Scanner</h2>
                <p>Position the QR code within the scanning frame</p>
            </div>
            
            <div class="camera-container">
                <video id="qr-video" autoplay playsinline></video>
                <canvas id="qr-canvas" style="display: none;"></canvas>
                <div class="scanner-overlay">
                    <div class="scanner-frame">
                        <div class="corner top-left"></div>
                        <div class="corner top-right"></div>
                        <div class="corner bottom-left"></div>
                        <div class="corner bottom-right"></div>
                    </div>
                </div>
                <div class="camera-placeholder" id="camera-placeholder">
                    <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M23 7a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 1 7v10a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 23 17Z"/>
                        <polyline points="2.3 6 12 13 21.7 6"/>
                        <line x1="12" y1="22" x2="12" y2="13"/>
                    </svg>
                    <p>Camera not active</p>
                </div>
            </div>
            
            <div class="scanner-controls">
                <button id="start-camera" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M23 7a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 1 7v10a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 23 17Z"/>
                        <polyline points="2.3 6 12 13 21.7 6"/>
                        <line x1="12" y1="22" x2="12" y2="13"/>
                    </svg>
                    Start Camera
                </button>
                <button id="stop-camera" class="btn btn-outline" style="display: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="6" y="6" width="12" height="12" rx="2" ry="2"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Stop Camera
                </button>
            </div>
        </div>

        <!-- Trip Details Section -->
        <div class="trip-details-section">
            <div class="section-header">
                <h2>Trip Details</h2>
                <p>Configure departure port and destination</p>
            </div>
            
            <div class="trip-inputs">
                <div class="trip-box">
                    <div class="trip-box-header">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span>Departure Port</span>
                    </div>
                    <div class="trip-box-content">
                        <select id="port-select" class="form-control">
                            <option value="Buenavista Port" selected>Buenavista Port</option>
                        </select>
                    </div>
                </div>
                <div class="trip-box">
                    <div class="trip-box-header">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        <span>Destination</span>
                    </div>
                    <div class="trip-box-content">
                        <select id="destination-select" class="form-control">
                            <option value="Guimaras" selected>Guimaras</option>
                            <option value="Iloilo">Iloilo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="trip-box" style="margin-top:18px;">
                <div class="trip-box-header">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 21v-2a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Vessel</span>
                </div>
                <div class="trip-box-content">
                    <select id="vessel-select" class="form-control">
                        <option value="MV Starlite" selected>MV Starlite</option>
                        <option value="MV OceanJet">MV OceanJet</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Manual Entry Section -->
        <div class="manual-entry-section">
            <div class="section-header">
                <h2>Manual Entry</h2>
                <p>Enter user email to view passenger information</p>
            </div>
            
            <div class="input-group">
                <input type="email" id="manual-user-email" placeholder="Enter User Email" class="form-control">
                <button id="view-manifest" class="btn btn-primary">View</button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div id="manifest-result" class="manifest-result" style="display: none;">
        <!-- Manifest data will be loaded here -->
    </div>
</div>

<style>
/* QR Scanner Page Styles */
.qr-scanner-page {
    max-width: 100%;
    margin: 0;
    padding: 0;
}

/* Dark Mode Styles */
.dark-mode .qr-scanner-page {
    background: #111827;
}

.dark-mode .scanner-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.dark-mode .scanner-header::before {
    opacity: 0.2;
}

.dark-mode .scanner-section {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(55, 65, 81, 0.8);
}

.dark-mode .manual-entry-section {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(55, 65, 81, 0.8);
}

.dark-mode .trip-details-section {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(55, 65, 81, 0.8);
}

.dark-mode .section-header h2 {
    color: #f9fafb;
}

.dark-mode .section-header p {
    color: #9ca3af;
}

.dark-mode .trip-box {
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    border-color: #4b5563;
    box-shadow: 
        0 4px 16px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(75, 85, 99, 0.8);
}

.dark-mode .trip-box:hover {
    border-color: #6b7280;
    box-shadow: 
        0 8px 24px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(107, 114, 128, 0.9);
}

.dark-mode .trip-box-header span {
    color: #f9fafb;
}

.dark-mode .trip-box-content .form-control {
    background: linear-gradient(135deg, #374151, #1f2937);
    border-color: #4b5563;
    color: #f9fafb;
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(75, 85, 99, 0.8);
}

.dark-mode .trip-box-content .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 
        0 0 0 4px rgba(59, 130, 246, 0.2),
        0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Dropdown styling for dark mode */
.dark-mode .form-control option {
    background-color: #374151;
    color: #f9fafb;
}

.dark-mode .form-control:focus option {
    background-color: #4b5563;
    color: #f9fafb;
}

.dark-mode .form-control option:hover {
    background-color: #6b7280;
}

.dark-mode .form-control option:checked {
    background-color: #3b82f6;
    color: #ffffff;
}

.dark-mode .input-group .form-control {
    background: linear-gradient(135deg, #374151, #1f2937);
    border-color: #4b5563;
    color: #f9fafb;
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(75, 85, 99, 0.8);
}

.dark-mode .input-group .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 
        0 0 0 4px rgba(59, 130, 246, 0.2),
        0 4px 12px rgba(59, 130, 246, 0.3);
}

.dark-mode .input-group .form-control::placeholder {
    color: #9ca3af;
}

.dark-mode .manifest-result {
    background: #1f2937;
    border-color: #374151;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.dark-mode .manifest-header {
    border-bottom-color: #374151;
}

.dark-mode .manifest-info h2 {
    color: #f9fafb;
}

.dark-mode .manifest-info p {
    color: #9ca3af;
}

.dark-mode .manifest-field {
    background: #374151;
    border-left-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.dark-mode .manifest-field label {
    color: #9ca3af;
}

.dark-mode .manifest-field .value {
    color: #f9fafb;
}

.dark-mode .success-message {
    background: #065f46 !important;
    color: #d1fae5 !important;
    border-color: #047857 !important;
}

.dark-mode .warning-message {
    background: #92400e !important;
    color: #fef3c7 !important;
    border-color: #b45309 !important;
}

.dark-mode .error-message {
    background: #991b1b !important;
    color: #fecaca !important;
    border-color: #dc2626 !important;
}

.dark-mode .info-message {
    background: #1e40af !important;
    color: #dbeafe !important;
    border-color: #3b82f6 !important;
}

/* Header */
.scanner-header {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1d4ed8 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom: 3px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 20px rgba(30, 64, 175, 0.3);
}

.scanner-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(255, 255, 255, 0.06) 0%, transparent 50%);
    opacity: 1;
}



.header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.header-left {
    flex: 1;
    text-align: center;
}

.header-title h1 {
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin: 0 0 10px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    letter-spacing: -0.01em;
    line-height: 1.1;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.header-title p {
    color: rgba(255, 255, 255, 0.92);
    margin: 0;
    font-size: 15px;
    line-height: 1.4;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    margin: 0 auto;
}

/* Main Content */
.scanner-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

@media (max-width: 1024px) {
    .scanner-content {
        grid-template-columns: 1fr;
        gap: 24px;
    }
}

.scanner-section, .trip-details-section, .manual-entry-section {
    padding: 80px 56px 56px 56px;
    min-height: 400px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
}

.manual-entry-section {
    grid-column: 1 / -1;
    width: 100%;
    max-width: 420px;
    margin-left: auto;
    margin-right: auto;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e5e7eb;
    border-radius: 18px;
    padding: 28px 20px;
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.07), 0 0 0 1px rgba(59, 130, 246, 0.04);
    position: relative;
    overflow: hidden;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin-top: 32px;
}

@media (max-width: 700px) {
  .manual-entry-section {
    max-width: 100%;
    margin-left: 0;
    margin-right: 0;
  }
}

.manual-entry-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 50%, #06b6d4 100%);
    border-radius: 18px 18px 0 0;
}

.manual-entry-section .section-header {
    margin-bottom: 18px;
}

.manual-entry-section .input-group {
    display: flex;
    gap: 16px;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
}

.manual-entry-section .form-control {
    flex: 1;
    padding: 18px 22px;
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    font-size: 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
    transition: border-color 0.3s, box-shadow 0.3s;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.04), inset 0 1px 0 rgba(59, 130, 246, 0.08);
    color: #1e293b;
}

.manual-entry-section .form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.13), 0 4px 12px rgba(59, 130, 246, 0.10);
}

.manual-entry-section .form-control:hover {
    border-color: #8b5cf6;
}

/* Scanner Section */
.scanner-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.08),
        0 0 0 1px rgba(255, 255, 255, 0.8);
    position: relative;
    overflow: hidden;
}

.scanner-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
}

.section-header {
    text-align: center;
    margin-bottom: 28px;
}

.scanner-section .section-header {
    margin-bottom: 32px;
}

.section-header h2 {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 12px 0;
}

.section-header p {
    color: #6b7280;
    margin: 0;
    font-size: 16px;
    line-height: 1.5;
}

.camera-container {
    position: relative;
    width: 100%;
    max-width: 480px;
    height: 380px;
    border-radius: 20px;
    overflow: hidden;
    background: linear-gradient(145deg, #1a1a1a, #2d2d2d);
    margin: 0 auto 28px auto;
    box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

#qr-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.camera-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    position: relative;
}

.camera-placeholder::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="qr-pattern-dark" x="0" y="0" width="25" height="25" patternUnits="userSpaceOnUse"><rect width="25" height="25" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/><rect x="6" y="6" width="6" height="6" fill="rgba(255,255,255,0.08)"/><rect x="19" y="6" width="6" height="6" fill="rgba(255,255,255,0.08)"/><rect x="6" y="19" width="6" height="6" fill="rgba(255,255,255,0.08)"/><rect x="19" y="19" width="6" height="6" fill="rgba(255,255,255,0.08)"/></pattern></defs><rect width="100" height="100" fill="url(%23qr-pattern-dark)"/></svg>');
    opacity: 0.4;
}

.camera-placeholder svg {
    margin-bottom: 24px;
    color: #60a5fa;
    width: 100px;
    height: 100px;
    filter: drop-shadow(0 4px 8px rgba(96, 165, 250, 0.3));
    position: relative;
    z-index: 2;
}

.camera-placeholder p {
    margin: 0;
    font-size: 18px;
    color: #e2e8f0;
    font-weight: 600;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 2;
}

.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.scanner-frame {
    width: 250px;
    height: 250px;
    position: relative;
}

.corner {
    position: absolute;
    width: 40px;
    height: 40px;
    border: 4px solid #60a5fa;
    border-radius: 4px;
    box-shadow: 
        0 0 0 2px rgba(96, 165, 250, 0.3),
        0 0 20px rgba(96, 165, 250, 0.5);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { 
        box-shadow: 
            0 0 0 2px rgba(96, 165, 250, 0.3),
            0 0 20px rgba(96, 165, 250, 0.5);
    }
    50% { 
        box-shadow: 
            0 0 0 4px rgba(96, 165, 250, 0.5),
            0 0 30px rgba(96, 165, 250, 0.8);
    }
}

.top-left {
    top: 0;
    left: 0;
    border-right: none;
    border-bottom: none;
}

.top-right {
    top: 0;
    right: 0;
    border-left: none;
    border-bottom: none;
}

.bottom-left {
    bottom: 0;
    left: 0;
    border-right: none;
    border-top: none;
}

.bottom-right {
    bottom: 0;
    right: 0;
    border-left: none;
    border-top: none;
}

.scanner-controls {
    display: flex;
    gap: 16px;
    align-items: center;
    justify-content: center;
    margin-top: 0;
}

/* Trip Details Section */
.trip-details-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.08),
        0 0 0 1px rgba(255, 255, 255, 0.8);
    position: relative;
    overflow: hidden;
}

.trip-details-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
}

.trip-inputs {
    display: flex;
    flex-direction: row;
    gap: 24px;
    width: 100%;
    max-width: 600px;
    justify-content: center;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .scanner-content {
        padding: 0 20px;
    }
    
    .scanner-header {
        padding: 32px 0;
    }
    
    .header-title h1 {
        font-size: 28px;
    }
    
    .header-title p {
        font-size: 16px;
    }
    
    .scanner-section,
    .manual-entry-section {
        padding: 32px;
    }
    
    .camera-container {
        height: 350px;
        max-width: 100%;
    }
    
    .input-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .manifest-details {
        grid-template-columns: 1fr;
    }
    
    .manifest-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .scanner-header {
        padding: 20px 0;
    }
    
    .header-title h1 {
        font-size: 20px;
    }
    
    .header-title p {
        font-size: 13px;
    }
    
    .scanner-section,
    .manual-entry-section {
        padding: 20px;
    }
    
    .camera-container {
        height: 200px;
    }
    
    .scanner-frame {
        width: 150px;
        height: 150px;
    }
    
    .manifest-header {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
}

/* Dark Mode Styles */
body.dark-mode .scanner-header {
    background: #232b3b;
    border-bottom-color: #2d3748;
}

body.dark-mode .header-title h1 {
    color: #e0e7ef;
}

body.dark-mode .header-title p {
    color: #b6c3e0;
}

body.dark-mode .scanner-section,
body.dark-mode .manual-entry-section,
body.dark-mode .manifest-result {
    background: #232b3b;
    border-color: #2d3748;
}

body.dark-mode .section-header h2 {
    color: #e0e7ef;
}

body.dark-mode .section-header p {
    color: #9ca3af;
}

body.dark-mode .camera-placeholder {
    background: #2d3748;
    color: #9ca3af;
}

body.dark-mode .camera-placeholder svg {
    color: #4a5568;
}

body.dark-mode .input-group .form-control {
    background: #2d3748;
    border-color: #4a5568;
    color: #e0e7ef;
}

body.dark-mode .input-group .form-control:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
}

body.dark-mode .btn-outline {
    background: #232b3b;
    color: #e0e7ef;
    border-color: #2d3748;
}

body.dark-mode .btn-outline:hover {
    background: #2d3748;
    border-color: #4a5568;
}

body.dark-mode .manifest-info h2 {
    color: #e0e7ef;
}

body.dark-mode .manifest-info p {
    color: #9ca3af;
}

body.dark-mode .manifest-field {
    background: #2d3748;
    border-left-color: #60a5fa;
}

body.dark-mode .manifest-field label {
    color: #9ca3af;
}

body.dark-mode .manifest-field .value {
    color: #e0e7ef;
}

body.dark-mode .manifest-actions {
    border-top-color: #2d3748;
}

.trip-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e5e7eb;
    border-radius: 18px;
    padding: 32px 28px 24px 28px;
    box-shadow: 0 8px 32px rgba(59, 130, 246, 0.07), 0 0 0 1px rgba(59, 130, 246, 0.04);
    transition: box-shadow 0.3s, border-color 0.3s;
    position: relative;
    overflow: hidden;
    width: 100%;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.trip-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 50%, #06b6d4 100%);
    border-radius: 18px 18px 0 0;
}

.trip-box:hover {
    border-color: #3b82f6;
    box-shadow: 0 12px 32px rgba(59, 130, 246, 0.13), 0 0 0 2px #3b82f6;
}

.trip-box-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
    color: #374151;
    font-weight: 600;
    font-size: 17px;
    letter-spacing: 0.01em;
}

.trip-box-header svg {
    color: #3b82f6;
    flex-shrink: 0;
    width: 22px;
    height: 22px;
}

.trip-box-header span {
    font-size: 17px;
    font-weight: 600;
    color: #1e293b;
}

.trip-box-content .form-control {
    padding: 18px 22px;
    border: 2px solid #e5e7eb;
    border-radius: 14px;
    font-size: 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);
    transition: border-color 0.3s, box-shadow 0.3s;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.04), inset 0 1px 0 rgba(59, 130, 246, 0.08);
    width: 100%;
    color: #1e293b;
}

.trip-box-content .form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.13), 0 4px 12px rgba(59, 130, 246, 0.10);
}

.trip-box-content .form-control:hover {
    border-color: #8b5cf6;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
// Define functions globally BEFORE DOMContentLoaded
window.testManualEntry = function() {
    const email = document.getElementById('manual-user-email').value.trim();
    const resultDiv = document.getElementById('manifest-result');
    
    if (email) {
        resultDiv.innerHTML = '<div style="text-align:center; padding:40px; color:#888;">Loading manifest...</div>';
        resultDiv.style.display = 'block';
        
        // Load manifest by email
        fetch('get_user_manifest.php?email=' + encodeURIComponent(email) + '&port=' + encodeURIComponent(document.getElementById('port-select').value) + '&destination=' + encodeURIComponent(document.getElementById('destination-select').value) + '&vessel=' + encodeURIComponent(document.getElementById('vessel-select').value))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.displayManifest(data.user);
                } else {
                    resultDiv.innerHTML = '<div class="error-message">User not found or error loading manifest</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="error-message">Error loading manifest</div>';
            });
    } else {
        alert('Please enter an email address');
    }
};

// Define displayManifest globally as well
window.displayManifest = function(user) {
    const resultDiv = document.getElementById('manifest-result');
    const initials = user.full_name.split(' ').map(n => n[0]).join('').toUpperCase();
    
    resultDiv.innerHTML = `
        <div class="manifest-header">
            <div class="manifest-avatar">${initials}</div>
            <div class="manifest-info">
                <h2>${user.full_name}</h2>
                <p>User ID: ${user.id}</p>
            </div>
        </div>
        <div class="manifest-details">
            <div class="manifest-field">
                <label>Full Name</label>
                <div class="value">${user.full_name}</div>
            </div>
            <div class="manifest-field">
                <label>Contact Number</label>
                <div class="value">${user.contact_number}</div>
            </div>
            <div class="manifest-field">
                <label>Email Address</label>
                <div class="value">${user.email}</div>
            </div>
            <div class="manifest-field">
                <label>Address</label>
                <div class="value">${user.address}</div>
            </div>
            <div class="manifest-field">
                <label>Age</label>
                <div class="value">${user.age} years old</div>
            </div>
            <div class="manifest-field">
                <label>Sex</label>
                <div class="value">${user.sex}</div>
            </div>
            <div class="manifest-field">
                <label>Registration Date</label>
                <div class="value">${new Date(user.created_at).toLocaleDateString()}</div>
            </div>
        </div>
        <div class="manifest-actions">
            <button class="btn btn-primary" onclick="window.open('content_users.php', '_blank')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                </svg>
                View in User Management
            </button>
            <button class="btn btn-secondary" onclick="window.open('content_qrcodes.php', '_blank')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="2"/>
                    <rect x="14" y="14" width="7" height="7" rx="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="2"/>
                </svg>
                View QR Codes
            </button>
        </div>
    `;
};

document.addEventListener('DOMContentLoaded', function() {
    
    const video = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');
    const startBtn = document.getElementById('start-camera');
    const stopBtn = document.getElementById('stop-camera');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    let stream = null;
    let scanning = false;

    startBtn.onclick = async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            });
            video.srcObject = stream;
            video.style.display = 'block';
            cameraPlaceholder.style.display = 'none';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';
            scanning = true;
            scanQRCode();
        } catch (err) {
            console.error('Camera error:', err);
            alert('Camera error: ' + err.name + ' - ' + err.message);
        }
    };

    stopBtn.onclick = function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.srcObject = null;
        video.style.display = 'none';
        cameraPlaceholder.style.display = 'flex';
        startBtn.style.display = 'inline-flex';
        stopBtn.style.display = 'none';
        scanning = false;
    };

    const viewManifestBtn = document.getElementById('view-manifest');
    const manualUserEmail = document.getElementById('manual-user-email');

    // Manual manifest view
    if (viewManifestBtn && manualUserEmail) {
        viewManifestBtn.addEventListener('click', function() {
            const userEmail = manualUserEmail.value.trim();
            if (userEmail) {
                // Simple test first
                const resultDiv = document.getElementById('manifest-result');
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="info-message">Searching for user with email: ' + userEmail + '</div>';
                    resultDiv.style.display = 'block';
                    loadManifestByEmail(userEmail);
                } else {
                    alert('Result div not found');
                }
            } else {
                alert('Please enter a valid email address');
            }
        });
    } else {
        // Show error if elements not found
        if (!viewManifestBtn) {
            alert('View Manifest button not found');
        }
        if (!manualUserEmail) {
            alert('Email input field not found');
        }
    }

    // Check if user_id is provided in URL (from QR code scan)
    const urlParams = new URLSearchParams(window.location.search);
    const urlUserId = urlParams.get('user_id');
    if (urlUserId) {
        loadManifest(urlUserId);
        // Clear the URL parameter to avoid reloading on page refresh
        const newUrl = window.location.pathname + '?page=qr_scanner';
        window.history.replaceState({}, '', newUrl);
    }

    // QR Code scanning function
    function scanQRCode() {
        if (!scanning) return;

        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        if (code) {
            console.log('QR Code detected:', code.data);
            
            // Try to extract user ID from QR data
            const userId = extractUserIdFromQRData(code.data);
            if (userId) {
                loadManifest(userId);
                // Stop scanning after successful scan
                stopBtn.click();
            }
        }
        
        // Continue scanning
        requestAnimationFrame(scanQRCode);
    }

    // Extract user ID from QR data
    function extractUserIdFromQRData(qrData) {
        // Check if QR data contains admin URL with user_id
        const adminUrlMatch = qrData.match(/Admin URL: .*user_id=(\d+)/);
        if (adminUrlMatch) {
            return adminUrlMatch[1]; // Return the user ID from URL
        }
        
        // If no URL found, try to find user by QR data content
        return findUserIdByQRData(qrData);
    }

    // Find user ID by QR data
    function findUserIdByQRData(qrData) {
        // Get port and destination values
        const portSelect = document.getElementById('port-select');
        const destinationSelect = document.getElementById('destination-select');
        const port = portSelect.value;
        const destination = destinationSelect.value;
        
        // Send QR data to server to find matching user
        fetch('find_user_by_qr.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                qr_data: qrData,
                port: port,
                destination: destination,
                vessel: document.getElementById('vessel-select') ? document.getElementById('vessel-select').value : ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user_id) {
                // Show success message for manifest creation
                if (data.manifest_created) {
                    showScanResult('success', `QR Code scanned successfully!<br>Passenger: ${data.user_name}<br>Contact: ${data.user_contact}<br>Port: ${port || 'Not specified'}<br>Destination: ${destination || 'Not specified'}<br>Manifest entry created.`);
                } else if (data.already_scanned) {
                    showScanResult('warning', `Passenger already scanned recently.<br>Passenger: ${data.user_name}<br>Contact: ${data.user_contact}`);
                } else {
                    showScanResult('info', `User found.<br>Passenger: ${data.user_name}<br>Contact: ${data.user_contact}`);
                }
                loadManifest(data.user_id);
            } else {
                showScanResult('error', 'User not found for this QR code');
            }
        })
        .catch(error => {
            console.error('Error finding user:', error);
            showScanResult('error', 'Error processing QR code');
        });
    }

    // Show scan result message
    function showScanResult(type, message) {
        const resultDiv = document.getElementById('manifest-result');
        const alertClass = type === 'success' ? 'success-message' : 
                          type === 'warning' ? 'warning-message' : 
                          type === 'info' ? 'info-message' : 'error-message';
        
        resultDiv.innerHTML = `<div class="${alertClass}">${message}</div>`;
        resultDiv.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (resultDiv.querySelector(`.${alertClass}`)) {
                resultDiv.style.display = 'none';
            }
        }, 5000);
    }

    // Load manifest data by user ID
    function loadManifest(userId) {
        const resultDiv = document.getElementById('manifest-result');
        resultDiv.innerHTML = '<div style="text-align:center; padding:40px; color:#888;">Loading manifest...</div>';
        resultDiv.style.display = 'block';
        
        fetch('get_user_manifest.php?user_id=' + encodeURIComponent(userId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayManifest(data.user);
                } else {
                    resultDiv.innerHTML = '<div class="error-message">User not found or error loading manifest</div>';
                }
            })
            .catch(error => {
                console.error('Error loading manifest:', error);
                resultDiv.innerHTML = '<div class="error-message">Error loading manifest</div>';
            });
    }

    // Load manifest data by email
    function loadManifestByEmail(userEmail) {
        const resultDiv = document.getElementById('manifest-result');
        
        if (!resultDiv) {
            return;
        }
        
        resultDiv.innerHTML = '<div style="text-align:center; padding:40px; color:#888;">Loading manifest...</div>';
        resultDiv.style.display = 'block';
        
        const url = 'get_user_manifest.php?email=' + encodeURIComponent(userEmail) + '&port=' + encodeURIComponent(document.getElementById('port-select').value) + '&destination=' + encodeURIComponent(document.getElementById('destination-select').value) + '&vessel=' + encodeURIComponent(document.getElementById('vessel-select').value);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayManifest(data.user);
                } else {
                    resultDiv.innerHTML = '<div class="error-message">User not found or error loading manifest</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="error-message">Error loading manifest</div>';
            });
    }

    // Display manifest data function is now defined globally above
});
</script> 