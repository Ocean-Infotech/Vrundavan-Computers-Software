# Vrundavan Shield Native Windows Agent
# Zero Dependencies: Runs on any Windows PC without XAMPP, PHP, or Python

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$configFile = Join-Path $scriptDir "config.json"

$port = 9988
$secretKey = ""

if (Test-Path $configFile) {
    try {
        $configContent = Get-Content $configFile -Raw | ConvertFrom-Json
        if ($configContent.local_port) { $port = [int]$configContent.local_port }
        if ($configContent.secret_key) { $secretKey = [string]$configContent.secret_key }
    } catch {
        Write-Host "Warning: Could not parse config.json, using defaults."
    }
}

if ([string]::IsNullOrWhiteSpace($secretKey)) {
    $secretKey = "base64:H0naO/XQbCIIp4wRCFsLY9qHcGyNpJiogSwbkNYyotE="
}

if ($secretKey.StartsWith("base64:")) {
    $rawKeyBytes = [System.Convert]::FromBase64String($secretKey.Substring(7))
} else {
    $rawKeyBytes = [System.Text.Encoding]::UTF8.GetBytes($secretKey)
}

function Get-CleanSerial($val, $fallback) {
    if ([string]::IsNullOrWhiteSpace($val)) { return $fallback }
    $val = $val.Trim()
    $placeholders = @('To be filled by O.E.M.', 'Default String', 'None', 'Unknown', 'Not Specified', '00000000000')
    foreach ($ph in $placeholders) {
        if ($val -eq $ph) { return $fallback }
    }
    return $val
}

# 1. Collect Hardware Information (Cached in memory for lightning-fast responses)
Write-Host "Reading hardware serials..."
$mb = (Get-CimInstance Win32_BaseBoard -ErrorAction SilentlyContinue).SerialNumber
$mb = Get-CleanSerial $mb "MB-SERIAL-UNKNOWN"

$bios = (Get-CimInstance Win32_BIOS -ErrorAction SilentlyContinue).SerialNumber
$bios = Get-CleanSerial $bios "BIOS-SERIAL-UNKNOWN"

$cpu = (Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue).ProcessorId
$cpu = Get-CleanSerial $cpu "CPU-ID-UNKNOWN"

$rawCombined = "$mb$bios$cpu"
$sha256 = [System.Security.Cryptography.SHA256]::Create()
$hashBytes = $sha256.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($rawCombined))
$hardwareId = ($hashBytes | ForEach-Object { $_.ToString("x2") }) -join ""

$pcName = $env:COMPUTERNAME
if ([string]::IsNullOrWhiteSpace($pcName)) { $pcName = [System.Net.Dns]::GetHostName() }

Write-Host "=================================================="
Write-Host "       VRUNDAVAN SHIELD NATIVE WINDOWS AGENT      "
Write-Host "=================================================="
Write-Host "Motherboard  : $mb"
Write-Host "BIOS Serial  : $bios"
Write-Host "CPU ID       : $cpu"
Write-Host "Hardware ID  : $hardwareId"
Write-Host "PC Name      : $pcName"
Write-Host "Listening on : http://127.0.0.1:$port/hardware"
Write-Host "=================================================="

# 2. Start HttpListener
$listener = New-Object System.Net.HttpListener
$prefix = "http://127.0.0.1:$port/"
$listener.Prefixes.Add($prefix)

try {
    $listener.Start()
} catch {
    Write-Host "Error starting listener on $prefix : $_"
    exit 1
}

$hmac = New-Object System.Security.Cryptography.HMACSHA256
$hmac.Key = $rawKeyBytes

while ($listener.IsListening) {
    try {
        $context = $listener.GetContext()
        $request = $context.Request
        $response = $context.Response

        # Add CORS Headers
        $response.Headers.Add("Access-Control-Allow-Origin", "*")
        $response.Headers.Add("Access-Control-Allow-Methods", "GET, OPTIONS")
        $response.Headers.Add("Access-Control-Allow-Headers", "Content-Type, Accept")

        if ($request.HttpMethod -eq "OPTIONS") {
            $response.StatusCode = 200
            $response.Close()
            continue
        }

        $urlPath = $request.Url.AbsolutePath.TrimEnd('/')
        if ($urlPath -eq "/hardware" -or $urlPath -eq "") {
            $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
            $nonce = [System.Guid]::NewGuid().ToString("N")
            $payload = "$hardwareId$timestamp$nonce"

            $sigBytes = $hmac.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($payload))
            $signature = ($sigBytes | ForEach-Object { $_.ToString("x2") }) -join ""

            $data = [ordered]@{
                hardware_id = $hardwareId
                motherboard_serial = $mb
                bios_serial = $bios
                cpu_id = $cpu
                timestamp = $timestamp
                nonce = $nonce
                signature = $signature
                pc_name = $pcName
                latitude = ""
                longitude = ""
            }

            $jsonStr = $data | ConvertTo-Json
            $buffer = [System.Text.Encoding]::UTF8.GetBytes($jsonStr)
            $response.ContentType = "application/json; charset=utf-8"
            $response.ContentLength64 = $buffer.Length
            $response.StatusCode = 200
            $response.OutputStream.Write($buffer, 0, $buffer.Length)
            $response.OutputStream.Flush()
            $response.Close()
        } else {
            $response.StatusCode = 404
            $response.Close()
        }
    } catch {
        # Handle client abort or network reset silently
    }
}
