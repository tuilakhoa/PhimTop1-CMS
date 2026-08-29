import re

with open('includes/api_client.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Add a function to get years
new_function = """function getYearsList() {
    $cache = new CacheManager();
    $cacheKey = 'years_list';
    
    $cached = $cache->get($cacheKey);
    if ($cached) return json_decode($cached, true);
    
    $ch = curl_init('https://phimapi.com/nam-phat-hanh');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if ($data) {
        $cache->set($cacheKey, json_encode($data), 86400); // 1 day
        return $data;
    }
    
    return [];
}
"""

if "function getYearsList" not in content:
    content += "\n" + new_function

with open('includes/api_client.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Added getYearsList to api_client.php")
