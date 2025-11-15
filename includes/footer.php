<style>
    .site-footer { border-top:1px solid #eee; background:#fff; position:fixed; left:0; right:0; bottom:0; z-index:999; box-shadow:0 -6px 18px rgba(0,0,0,0.06); }
    body { padding-bottom:100px; }
    .footer-inner { display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto; padding:18px 160px; }
    .footer-social a { display:inline-block; margin-right:14px; color:#f06d21; font-size:18px; text-decoration:none; background:transparent; border:none; padding:0; line-height:1; }
    .footer-social a:last-child { margin-right:0; }
    .footer-social a:hover { color:#d85b17; transform:translateY(-2px); }
    .footer-legal { color:#8f9aa0; font-size:14px; line-height:1.2; text-align:center; }
    .footer-legal .small-note { color:#bfc6cb; font-size:12px; margin-top:6px; }
    .btn-ghost { border:1px solid #f06d21; color:#f06d21; background:transparent; padding:8px 14px; border-radius:8px; font-size:14px; text-decoration:none; }
    @media (max-width:1100px) {
        .footer-inner { padding:14px 60px; }
    }
    @media (max-width:640px) {
        .footer-inner { flex-direction:column; gap:10px; text-align:center; padding:12px 16px; }
        body { padding-bottom:140px; }
    }
</style>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-social" aria-hidden="false">
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
        </div>

        <div class="footer-legal">
            <div>&copy; <?= date('Y') ?> BrickMMO. All rights reserved.</div>
            <div class="small-note">LEGO, the LEGO logo and the Minifigure are trademarks of the LEGO Group.</div>
        </div>

        <div class="footer-cta">
            <a href="select.php" class="btn-ghost">Get Started</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let responseChart;
let currentAssetId = null;

setTimeout(function() {
    if (!document.getElementById('loadingIndicator').style.display === 'block') {
        location.reload();
    }
}, 300000);

function updateTimestamps() {
    const elements = document.querySelectorAll('[data-timestamp]');
    elements.forEach(function(el) {
        const timestamp = parseInt(el.dataset.timestamp);
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;
        
        if (diff < 60) {
            el.textContent = 'Just now';
        } else if (diff < 3600) {
            el.textContent = Math.floor(diff / 60) + ' minutes ago';
        } else if (diff < 86400) {
            el.textContent = Math.floor(diff / 3600) + ' hours ago';
        } else {
            el.textContent = Math.floor(diff / 86400) + ' days ago';
        }
    });
}

function updateAssetInfo() {
    const select = document.getElementById('assetSelect');
    const checkButton = document.getElementById('checkButton');
    
    if (select.value) {
        checkButton.disabled = false;
        currentAssetId = select.value;
    } else {
        checkButton.disabled = true;
        currentAssetId = null;
    }
}

function checkAssetStatus() {
    if (!currentAssetId) return;
    
    const loadingIndicator = document.getElementById('loadingIndicator');
    const checkResults = document.getElementById('checkResults');
    const checkButton = document.getElementById('checkButton');
    
    loadingIndicator.style.display = 'block';
    checkResults.style.display = 'none';
    checkButton.disabled = true;
    checkButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    
    const formData = new FormData();
    formData.append('check_asset', '1');
    formData.append('asset_id', currentAssetId);
    
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCheckResults(data);
        } else {
            showError(data.error || 'Unknown error occurred');
        }
    })
    .catch(error => {
        showError('Network error: ' + error.message);
    })
    .finally(() => {
        loadingIndicator.style.display = 'none';
        checkButton.disabled = false;
        checkButton.innerHTML = '<i class="fas fa-play"></i> Check Status Now';
    });
}

function displayCheckResults(data) {
    const results = document.getElementById('checkResults');
    const asset = data.asset;
    const check = data.current_check;
    
    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');
    
    if (check.status === 'up') {
        statusIcon.innerHTML = '<i class="fas fa-check-circle w3-text-green"></i>';
        statusText.innerHTML = '<span class="w3-text-green">UP</span>';
        statusText.className = 'w3-margin-top w3-text-green';
    } else {
        statusIcon.innerHTML = '<i class="fas fa-times-circle w3-text-red"></i>';
        statusText.innerHTML = '<span class="w3-text-red">DOWN</span>';
        statusText.className = 'w3-margin-top w3-text-red';
    }
    
    document.getElementById('responseTime').textContent = Math.round(check.response_time) + 'ms';
    document.getElementById('httpCode').textContent = check.http_code || 'N/A';
    document.getElementById('ipAddress').textContent = check.ip_address || 'N/A';
    
    const errorDiv = document.getElementById('errorMessage');
    if (check.error) {
        document.getElementById('errorText').textContent = check.error;
        errorDiv.style.display = 'block';
    } else {
        errorDiv.style.display = 'none';
    }
    
    updateHistoryTable(data.recent_checks);
    
    updateResponseChart(data.recent_checks);
    
    const assetId = asset.asset_id || asset.id;
    document.getElementById('detailsLink').href = 'asset.php?id=' + assetId;
    document.getElementById('visitLink').href = asset.url;
    
    results.style.display = 'block';
    results.scrollIntoView({ behavior: 'smooth' });
}

function updateHistoryTable(checks) {
    const tbody = document.getElementById('historyTableBody');
    tbody.innerHTML = '';
    
    checks.forEach(check => {
        const row = tbody.insertRow();
        const checkTime = new Date(check.checked_at);
        
        row.innerHTML = `
            <td>${checkTime.toLocaleString()}</td>
            <td>
                <span class="w3-tag w3-round w3-${check.status === 'up' ? 'green' : 'red'}">
                    <i class="fas fa-${check.status === 'up' ? 'check' : 'times'}"></i>
                    ${check.status.toUpperCase()}
                </span>
            </td>
            <td>${check.response_time ? Math.round(check.response_time) + 'ms' : 'N/A'}</td>
            <td>${check.response_code || check.status_code || 'N/A'}</td>
        `;
    });
}

function updateResponseChart(checks) {
    const ctx = document.getElementById('responseChart').getContext('2d');
    
    if (responseChart) {
        responseChart.destroy();
    }
    
    const labels = checks.reverse().map(check => {
        const date = new Date(check.checked_at);
        return date.toLocaleTimeString();
    });
    
    const responseData = checks.map(check => check.response_time || 0);
    const statusData = checks.map(check => check.status === 'up' ? 1 : 0);
    
    responseChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Response Time (ms)',
                data: responseData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                yAxisID: 'y'
            }, {
                label: 'Status (Up=1, Down=0)',
                data: statusData,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Response Time (ms)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Status'
                    },
                    min: 0,
                    max: 1,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Response Time and Status History'
                }
            }
        }
    });
}

function showError(message) {
    alert('Error: ' + message);
}

updateTimestamps();
setInterval(updateTimestamps, 60000);

window.currentAssetId = currentAssetId;
window.updateAssetInfo = updateAssetInfo;
window.checkAssetStatus = checkAssetStatus;
window.displayCheckResults = displayCheckResults;
</script>

</body>
</html>
