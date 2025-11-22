<?php
require_once 'config.php';

class UptimeMonitor {
    private $pdo;
    private $output = false;
    
    public function __construct($pdo, $output = false) {
        $this->pdo = $pdo;
        $this->output = $output;
    }
    
    public function checkAllAssets() {

        $stmt = $this->pdo->prepare("SELECT * FROM assets WHERE status = '1'");
        $stmt->execute();
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($assets as $asset) {
            if ($this->output) echo $asset['name'] . "<br>";
            $this->checkAsset($asset);
        }
    }
    
    public function checkAsset($asset) {
        if ($this->output) {
            echo '<div style="border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; background: #f9f9f9;">';
            echo '<h3 style="margin: 0 0 10px 0; color: #333;">Checking: ' . htmlspecialchars($asset['name']) . '</h3>';
            echo '<p style="margin: 5px 0; color: #666;"><strong>URL:</strong> <a href="' . htmlspecialchars($asset['url']) . '" target="_blank">' . htmlspecialchars($asset['url']) . '</a></p>';
        }
        
        $start_time = microtime(true);
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $asset['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => TIMEOUT,
            CURLOPT_USERAGENT => 'BrickMMO Uptime Monitor v1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false
        ]);
        
        if ($this->output) {
            echo '<p style="margin: 5px 0; color: #666;">⏳ Sending HTTP request...</p>';
            flush();
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $primaryIP = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($this->output) {
            echo '<p style="margin: 5px 0; color: #666;"><strong>HTTP Status:</strong> ' . $httpCode . '</p>';
            echo '<p style="margin: 5px 0; color: #666;"><strong>Response Time:</strong> ' . round($totalTime * 1000, 2) . 'ms</p>';
            if ($primaryIP) {
                echo '<p style="margin: 5px 0; color: #666;"><strong>IP Address:</strong> ' . htmlspecialchars($primaryIP) . '</p>';
            }
        }
        
        $end_time = microtime(true);
        $response_time = ($end_time - $start_time) * 1000;
        
        $status = 0;
        $error_message = null;
        
        if ($error) {
            $status = 0;
            $error_message = $error;
            if ($this->output) echo '<p style="margin: 5px 0; color: #d32f2f; font-weight: bold;">❌ Status: DOWN (Error: ' . htmlspecialchars($error) . ')</p>';
        } elseif ($httpCode >= 200 && $httpCode < 400) {
            $status = 1;
            if ($this->output) echo '<p style="margin: 5px 0; color: #4caf50; font-weight: bold;">✅ Status: UP</p>';
        } else {
            $status = 0;
            $error_message = "HTTP Status: $httpCode";
            if ($this->output) echo '<p style="margin: 5px 0; color: #d32f2f; font-weight: bold;">❌ Status: DOWN (HTTP ' . $httpCode . ')</p>';
        }
        
        $asset_id = $asset['asset_id'] ?? $asset['id'];
        if ($this->output) echo '<p style="margin: 5px 0; color: #666;">💾 Recording check to database...</p>';
        $this->recordCheck($asset_id, $status, $response_time, $httpCode, $error_message, $primaryIP);
        
        if ($status === 1 && $response) {
            if ($this->output) echo '<p style="margin: 5px 0; color: #666;">🔍 Checking for page errors...</p>';
            $this->checkPageErrors($asset_id, $response);
        }
        
        if ($this->output) {
            echo '<p style="margin: 5px 0; color: #4caf50;">✓ Check complete!</p>';
            echo '</div>';
            flush();
        }
        
        return [
            'status' => $status,
            'response_time' => $response_time,
            'http_code' => $httpCode,
            'ip_address' => $primaryIP,
            'error' => $error_message
        ];
    }
    
    private function recordCheck($asset_id, $status, $response_time, $status_code, $error_message, $ip_address = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO checks (asset_id, response_time, up, response_code, error_message, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$asset_id, $response_time, $status, $status_code, $error_message, $ip_address]);
        } catch (PDOException $e) {
            $stmt = $this->pdo->prepare("
                INSERT INTO checks (asset_id, up, response_time, status_code, error_message) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$asset_id, $status, $response_time, $status_code, $error_message]);
        }
    }
    
    private function checkPageErrors($asset_id, $response) {
        $errors = [];
        
        if (preg_match('/Uncaught\s+(?:Error|Exception|TypeError|ReferenceError|SyntaxError)|Fatal\s+error|Parse\s+error/i', $response)) {
            $errors[] = ['type' => 'JavaScript Error', 'message' => 'Runtime error detected in page'];
        }
        
        if (preg_match('/<title>[^<]*(?:404|Not Found|Page Not Found)[^<]*<\/title>|<h1>[^<]*(?:404|Not Found)[^<]*<\/h1>/i', $response)) {
            $errors[] = ['type' => '404 Error', 'message' => '404 page detected'];
        }
        
        if (preg_match('/Database\s+(?:connection\s+)?error|MySQL\s+error|Connection\s+to\s+database\s+failed|Could\s+not\s+connect\s+to/i', $response)) {
            $errors[] = ['type' => 'Database Error', 'message' => 'Database connection error detected'];
        }
        
        if (empty($errors)) {
            return;
        }

        if (!$this->tableExists('page_errors')) {
            return;
        }

        foreach ($errors as $error) {
            try {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO page_errors (asset_id, error_type, error_message) VALUES (?, ?, ?)"
                );
                $stmt->execute([$asset_id, $error['type'], $error['message']]);
            } catch (PDOException $e) {
                continue;
            }
        }
    }

    private function tableExists($tableName) {
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            $res = $stmt->fetch(PDO::FETCH_NUM);
            return $res !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAssetUptime($asset_id, $hours = 24) {
        $hours = max(1, min(8760, (int)$hours));
        
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_checks, SUM(CASE WHEN up = 1 THEN 1 ELSE 0 END) as up_checks, AVG(response_time) as avg_response_time FROM checks WHERE asset_id = ? AND checked_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)");
            $stmt->execute([$asset_id, $hours]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $result = ['total_checks' => 0, 'up_checks' => 0, 'avg_response_time' => null];
            }
        } catch (PDOException $e) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_checks, SUM(CASE WHEN up = 1 THEN 1 ELSE 0 END) as up_checks, AVG(response_time) as avg_response_time FROM checks WHERE asset_id = ? AND checked_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)");
            $stmt->execute([$asset_id, $hours]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $result = ['total_checks' => 0, 'up_checks' => 0, 'avg_response_time' => null];
            }
        }
        
        if ($result['total_checks'] > 0) {
            $uptime_percentage = ($result['up_checks'] / $result['total_checks']) * 100;
        } else {
            $uptime_percentage = 0;
        }
        
        $avg = isset($result['avg_response_time']) && $result['avg_response_time'] !== null ? (float)$result['avg_response_time'] : 0.0;
        return [
            'uptime_percentage' => round($uptime_percentage, 2),
            'total_checks' => $result['total_checks'],
            'up_checks' => $result['up_checks'],
            'avg_response_time' => round($avg, 2)
        ];
    }
    
    public function getRecentChecks($asset_id, $limit = 50) {
        $limit = max(1, min(1000, (int)$limit));
        
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM checks WHERE asset_id = ? ORDER BY checked_at DESC LIMIT $limit");
            $stmt->execute([$asset_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $stmt = $this->pdo->prepare("SELECT * FROM checks WHERE asset_id = ? ORDER BY checked_at DESC LIMIT $limit");
            $stmt->execute([$asset_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    public function getPageErrors($asset_id, $hours = 24) {
        if (!$this->tableExists('page_errors')) {
            return [];
        }

        $hours = max(1, min(8760, (int)$hours));

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM page_errors WHERE asset_id = ? AND occurred_at >= DATE_SUB(NOW(), INTERVAL ? HOUR) ORDER BY occurred_at DESC");
            $stmt->execute([$asset_id, $hours]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

$monitor = new UptimeMonitor($pdo, false);