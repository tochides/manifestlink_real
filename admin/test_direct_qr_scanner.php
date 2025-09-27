<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct QR Scanner Test</title>
    <style>
        body { font-family: Arial, sans-serif; background: #181f2a; color: #fff; text-align: center; padding: 40px; }
        .container { background: #232b3e; border-radius: 12px; padding: 32px; max-width: 500px; margin: 0 auto; }
        video { width: 100%; max-width: 400px; height: 300px; background: #000; border-radius: 8px; margin-bottom: 20px; }
        #msg { color: #ff6b6b; margin-top: 10px; }
        button { padding: 12px 24px; border: none; border-radius: 6px; background: #3b82f6; color: #fff; font-size: 16px; cursor: pointer; }
        button:active { background: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Direct QR Scanner Test</h2>
        <video id="video" autoplay playsinline></video>
        <div id="msg"></div>
        <button id="start-camera">Start Camera</button>
        <button id="stop-camera" style="display:none;">Stop Camera</button>
    </div>
    <script>
    console.log('Direct QR Scanner script loaded');
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('video');
        const startBtn = document.getElementById('start-camera');
        const stopBtn = document.getElementById('stop-camera');
        let stream = null;

        startBtn.onclick = async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-block';
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
            startBtn.style.display = 'inline-block';
            stopBtn.style.display = 'none';
        };
    });
    </script>
</body>
</html> 