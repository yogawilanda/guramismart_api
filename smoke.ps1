$base = 'https://guramismartapi.yogawilanda.com/api/v1'
$script:pass = 0; $script:fail = 0

function Call($method, $path, $body = $null, $token = $null) {
    $h = @{ Accept = 'application/json' }
    if ($token) { $h.Authorization = "Bearer $token" }
    $p = @{ Uri = "$base$path"; Method = $method; Headers = $h; UseBasicParsing = $true }
    if ($body) { $p.Body = ($body | ConvertTo-Json -Depth 5); $p.ContentType = 'application/json' }
    try { $r = Invoke-WebRequest @p; $s = [int]$r.StatusCode; $c = $r.Content }
    catch {
        if (-not $_.Exception.Response) { throw }
        $s = [int]$_.Exception.Response.StatusCode; $c = $_.ErrorDetails.Message
    }
    $j = $null; if ($c) { try { $j = $c | ConvertFrom-Json } catch {} }
    [pscustomobject]@{ Status = $s; Json = $j }
}

function Check($name, $cond, $info = '') {
    if ($cond) { $script:pass++; "PASS  $name" } else { $script:fail++; "FAIL  $name $info" }
}

$stamp = Get-Date -Format 'yyyyMMddHHmmss'
$d = { param($n) (Get-Date).AddDays(-$n).ToString('yyyy-MM-dd') }

"=== AUTH ==="
$r = Call POST '/auth/register' @{ name='Tester A'; email="a$stamp@test.id"; password='password123'; password_confirmation='password123' }
Check 'register A -> 201' ($r.Status -eq 201) $r.Status
$tA = $r.Json.token
$r = Call POST '/auth/register' @{ name='Tester B'; email="b$stamp@test.id"; password='password123'; password_confirmation='password123' }
$tB = $r.Json.token
$r = Call POST '/auth/login' @{ email="a$stamp@test.id"; password='salah' }
Check 'login password salah -> 422' ($r.Status -eq 422) $r.Status
$r = Call GET '/auth/me' $null $tA
Check 'me -> 200' ($r.Status -eq 200) $r.Status
$r = Call GET '/cycles'
Check 'tanpa token -> 401' ($r.Status -eq 401) $r.Status

"=== SIKLUS ==="
$r = Call POST '/cycles' @{ name='Smoke'; pond_area_m2=20; seed_count=500; stocking_date=(& $d 30); initial_capital=3000000 } $tA
Check 'buat siklus -> 201' ($r.Status -eq 201) $r.Status
$cid = $r.Json.id
Check 'durasi otomatis = 30' ($r.Json.duration_days -eq 30) $r.Json.duration_days
Check 'target_days default 120' ($r.Json.target_days -eq 120) $r.Json.target_days
$r = Call POST '/cycles' @{ pond_area_m2=-5; seed_count=0; stocking_date=(& $d -5); initial_capital=1 } $tA
Check 'siklus invalid -> 422' ($r.Status -eq 422) $r.Status

"=== ISOLASI DATA ==="
$r = Call GET "/cycles/$cid" $null $tB
Check 'B baca siklus A -> 404' ($r.Status -eq 404) $r.Status
$r = Call DELETE "/cycles/$cid" $null $tB
Check 'B hapus siklus A -> 404' ($r.Status -eq 404) $r.Status
$r = Call POST "/cycles/$cid/feed-logs" @{ fed_on=(& $d 1); amount_kg=1 } $tB
Check 'B input pakan ke siklus A -> 404' ($r.Status -eq 404) $r.Status

"=== PAKAN & AIR ==="
$r = Call POST "/cycles/$cid/feed-logs" @{ fed_on=(& $d 1); session='pagi'; amount_kg=2 } $tA
Check 'pakan pagi -> 201' ($r.Status -eq 201) $r.Status
$r = Call POST "/cycles/$cid/feed-logs" @{ fed_on=(& $d 1); session='sore'; amount_kg=3 } $tA
Check 'pakan sore -> 201' ($r.Status -eq 201) $r.Status
$r = Call POST "/cycles/$cid/feed-logs" @{ fed_on=(& $d 40); amount_kg=1 } $tA
Check 'pakan sebelum tebar -> 422' ($r.Status -eq 422) $r.Status
$r = Call POST "/cycles/$cid/water-logs" @{ recorded_on=(& $d 1); temperature_c=27; ph=9 } $tA
Check 'air -> 201' ($r.Status -eq 201) $r.Status
Check 'status pH 9 = high' ($r.Json.status.ph -eq 'high') $r.Json.status.ph
Check 'status suhu 27 = optimal' ($r.Json.status.temperature -eq 'optimal') $r.Json.status.temperature
$r = Call POST "/cycles/$cid/water-logs" @{ recorded_on=(& $d 1); temperature_c=27; ph=50 } $tA
Check 'pH 50 -> 422' ($r.Status -eq 422) $r.Status

"=== DASHBOARD SEBELUM PANEN ==="
$r = Call GET "/cycles/$cid/dashboard" $null $tA
Check 'dashboard -> 200' ($r.Status -eq 200) $r.Status
Check 'profit null sebelum panen' ($null -eq $r.Json.data.finance.profit) $r.Json.data.finance.profit
Check 'rata pakan = 5 kg/hari' ($r.Json.data.feed.avg_per_day_kg -eq 5) $r.Json.data.feed.avg_per_day_kg

"=== PANEN ==="
$r = Call POST "/cycles/$cid/harvest" @{ harvest_date=(& $d 0); total_weight_kg=100; total_sales=4500000 } $tA
Check 'panen -> 201' ($r.Status -eq 201) $r.Status
$r = Call POST "/cycles/$cid/harvest" @{ harvest_date=(& $d 0); total_weight_kg=100; total_sales=4500000 } $tA
Check 'panen ganda -> 422' ($r.Status -eq 422) $r.Status
$r = Call GET "/cycles/$cid" $null $tA
Check 'status = harvested' ($r.Json.status -eq 'harvested') $r.Json.status
$r = Call PATCH "/cycles/$cid" @{ seed_count=999 } $tA
Check 'ubah siklus selesai -> 422' ($r.Status -eq 422) $r.Status

"=== DASHBOARD SETELAH PANEN ==="
$r = Call GET "/cycles/$cid/dashboard" $null $tA
Check 'profit = 1.500.000' ($r.Json.data.finance.profit -eq 1500000) $r.Json.data.finance.profit
Check 'ROI = 50%' ($r.Json.data.finance.roi_percent -eq 50) $r.Json.data.finance.roi_percent
Check 'produktivitas = 5 kg/m2' ($r.Json.data.productivity.kg_per_m2 -eq 5) $r.Json.data.productivity.kg_per_m2
$r = Call GET '/dashboard' $null $tA
Check 'ringkasan -> 200' ($r.Status -eq 200) $r.Status

"=== LAINNYA ==="
$r = Call GET '/guides' $null $tA
Check 'panduan = 6 bab' (@($r.Json).Count -eq 6) @($r.Json).Count
$r = Call GET '/guides/manajemen-pakan' $null $tA
Check 'detail panduan -> 200' ($r.Status -eq 200) $r.Status
$r = Call GET '/notifications' $null $tA
Check 'notifikasi -> 200' ($r.Status -eq 200) $r.Status
$r = Call DELETE "/cycles/$cid" $null $tA
Check 'hapus siklus -> 204' ($r.Status -eq 204) $r.Status
$r = Call POST '/auth/logout' $null $tA
Check 'logout -> 200' ($r.Status -eq 200) $r.Status
$r = Call GET '/auth/me' $null $tA
Check 'token lama ditolak -> 401' ($r.Status -eq 401) $r.Status

""
"HASIL: $script:pass PASS, $script:fail FAIL"



