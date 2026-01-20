$baseUrl = "http://192.168.1.6:8081/api"
$userParams = @{
    email = "rahul.sharma@test.com"
    password = "password"
}

# 1. Login
Write-Output "Logging in..."
try {
    $login = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -Body $userParams
    $token = $login.data.token
    Write-Output "Login Success."
} catch {
    Write-Output "Login Request Failed: $_"
    exit
}

$headers = @{Authorization = "Bearer $token"}

Write-Output "`n--- Testing EXAM RESULTS Dashboard ---"
try {
    $dashboard = Invoke-RestMethod -Uri "$baseUrl/exams/results" -Method Get -Headers $headers
    
    Write-Output "Performance Stats:"
    $dashboard.data.performance_stats | Format-List
    
    Write-Output "Results List Count: $($dashboard.data.results.Count)"
    
    if($dashboard.data.results.Count -gt 0) {
        $first = $dashboard.data.results[0]
        Write-Output "First Result:"
        Write-Output "  Exam: $($first.exam_name)"
        Write-Output "  Status: $($first.status)"
        Write-Output "  Is Passed: $($first.is_passed)"
        if ($first.belt_transition) {
            Write-Output "  Transition: $($first.belt_transition.from) -> $($first.belt_transition.to)"
        }
    }

} catch {
    Write-Output "Results Dashboard Failed: $_"
}
