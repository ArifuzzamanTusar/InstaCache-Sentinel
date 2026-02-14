<?php
// --- 1. API HEADERS ---
header('Content-Type: application/json');      
header('Access-Control-Allow-Origin: *');      
header('X-Robots-Tag: noindex, nofollow');     

// --- CONFIGURATION ---
$db_host = 'localhost';
$db_user = 'add_your_db_user';     
$db_pass = 'add_your_db_pass';       
$db_name = 'add_your_db_name'; 

// RapidAPI Keys | https://rapidapi.com/3205/api/instagram120
$api_key = "add_your_rapidapi_key"; // UPDATE IF ROTATED
$username = "add_target_instagram_username";

// Turn off error reporting for production JSON
error_reporting(0); 

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database Connection Failed"]);
    exit();
}

// --- 2. CHECK CACHE ---
$sql = "SELECT * FROM insta_cache WHERE id = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_updated_ts = strtotime($row['last_updated']);
    $current_time = time();
    $time_diff = $current_time - $last_updated_ts;
} else {
    // Force update if empty
    $time_diff = 999999; 
    $row = ['followers' => 0, 'last_updated' => 'Never'];
}

// --- 3. UPDATE IF OLDER THAN 1 HOUR ---
if ($time_diff > 3600) {
    
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://instagram120.p.rapidapi.com/api/instagram/profile",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode(['username' => $username]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-rapidapi-host: instagram120.p.rapidapi.com",
            "x-rapidapi-key: " . $api_key
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if (!$err) {
        $data = json_decode($response, true);
        
        if (isset($data['result']['edge_followed_by']['count'])) {
            $new_count = $data['result']['edge_followed_by']['count'];
            
            // Update Database
            $update_stmt = $conn->prepare("UPDATE insta_cache SET followers = ?, last_updated = NOW() WHERE id = 1");
            $update_stmt->bind_param("i", $new_count);
            $update_stmt->execute();
            
            // Update variables for the immediate JSON response
            $row['followers'] = $new_count;
            $row['last_updated'] = date('Y-m-d H:i:s'); // Show current time
            $time_diff = 0; // Reset diff to show it's fresh
        }
    }
}

// --- 4. RETURN JSON RESPONSE ---
echo json_encode([
    "followers" => (int)$row['followers'],
    "username" => $username,
    "last_updated" => $row['last_updated'],
    "cached" => ($time_diff > 0 && $time_diff <= 3600) // True if from DB, False if just updated
]);

$conn->close();
?>