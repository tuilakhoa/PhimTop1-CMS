<?php
class FirestoreClient {
    private $projectId;
    private $serviceAccount;
    private $tokenFile;

    public function __construct($projectId, $serviceAccount) {
        $this->projectId = $projectId;
        $this->serviceAccount = $serviceAccount;
        $this->tokenFile = __DIR__ . '/../.firestore_token_' . md5($projectId) . '.json';
    }

    private function getAccessToken() {
        if (file_exists($this->tokenFile)) {
            $cache = json_decode(file_get_contents($this->tokenFile), true);
            if ($cache && isset($cache['access_token']) && $cache['exp'] > time() + 300) {
                return $cache['access_token'];
            }
        }

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/datastore',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signatureInput = $base64UrlHeader . "." . $base64UrlPayload;

        if (!openssl_sign($signatureInput, $signature, $this->serviceAccount['private_key'], 'SHA256')) {
            return null;
        }
        
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $signatureInput . "." . $base64UrlSignature;

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            file_put_contents($this->tokenFile, json_encode([
                'access_token' => $data['access_token'],
                'exp' => $now + 3600
            ]));
            return $data['access_token'];
        }

        return null;
    }

    private function parseFields($fields) {
        $result = [];
        if (!is_array($fields)) return $result;
        foreach ($fields as $k => $v) {
            if (!is_array($v)) continue;
            $type = array_key_first($v);
            $val = $v[$type];
            if ($type === 'integerValue') $result[$k] = (int)$val;
            else if ($type === 'booleanValue') $result[$k] = (bool)$val;
            else if ($type === 'doubleValue') $result[$k] = (float)$val;
            else if ($type === 'nullValue') $result[$k] = null;
            else if ($type === 'arrayValue') {
                $arr = [];
                if (isset($val['values'])) {
                    foreach ($val['values'] as $item) {
                        $itemType = array_key_first($item);
                        $arr[] = $item[$itemType];
                    }
                }
                $result[$k] = $arr;
            }
            else $result[$k] = $val;
        }
        return $result;
    }

    private function buildFields($data) {
        $fields = [];
        foreach ($data as $k => $v) {
            if (is_int($v)) $fields[$k] = ['integerValue' => (string)$v];
            else if (is_bool($v)) $fields[$k] = ['booleanValue' => $v];
            else if (is_float($v)) $fields[$k] = ['doubleValue' => $v];
            else if (is_null($v)) $fields[$k] = ['nullValue' => null];
            else if (is_array($v)) {
                $arr = [];
                foreach ($v as $item) {
                    if (is_int($item)) $arr[] = ['integerValue' => (string)$item];
                    else if (is_bool($item)) $arr[] = ['booleanValue' => $item];
                    else $arr[] = ['stringValue' => (string)$item];
                }
                $fields[$k] = ['arrayValue' => ['values' => $arr]];
            }
            else $fields[$k] = ['stringValue' => (string)$v];
        }
        return ['fields' => $fields];
    }

    private function request($method, $path, $body = null, $auth = false) {
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents" . $path;
        $ch = curl_init($url);
        
        $headers = [];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        
        if ($auth) {
            $token = $this->getAccessToken();
            if ($token) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function getDocument($collection, $docId) {
        $data = $this->request('GET', "/{$collection}/{$docId}");
        if (isset($data['fields'])) {
            return $this->parseFields($data['fields']);
        }
        return null;
    }

    public function setDocument($collection, $docId, $data) {
        $body = $this->buildFields($data);
        // Using PATCH with updateMask for proper upsert
        $query = "?";
        foreach (array_keys($data) as $field) {
            $query .= "updateMask.fieldPaths=" . urlencode($field) . "&";
        }
        $query = rtrim($query, '&');
        
        $response = $this->request('PATCH', "/{$collection}/{$docId}{$query}", $body, true);
        return isset($response['fields']);
    }

    public function getAllDocuments($collection) {
        $data = $this->request('GET', "/{$collection}");
        $results = [];
        if (isset($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                if (isset($doc['fields'])) {
                    $item = $this->parseFields($doc['fields']);
                    // Extract ID from name
                    $parts = explode('/', $doc['name']);
                    $item['_id'] = end($parts);
                    $results[] = $item;
                }
            }
        }
        return $results;
    }
}
