<?php
$content = file_get_contents("views/api-document.ejs");

// It looks like api-country lacks the PHP/NodeJS/cURL snippets
$append = <<<EOT
            <h3 class="text-white font-bold mt-8 mb-4">Code mẫu (Integration Examples):</h3>
            <div class="code-tabs">
                <div class="code-tab active" data-target="country-php">PHP</div>
                <div class="code-tab" data-target="country-node">Node.js</div>
                <div class="code-tab" data-target="country-curl">cURL</div>
            </div>
            
            <!-- PHP Example -->
            <div id="country-php" class="code-content active">
<pre><code class="language-php">&lt;?php
\$curl = curl_init();
curl_setopt_array(\$curl, array(
  CURLOPT_URL => 'https://api.phimtop1.asia/api/quoc-gia',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

\$response = curl_exec(\$curl);
curl_close(\$curl);
echo \$response;
</code></pre>
            </div>

            <!-- NodeJS Example -->
            <div id="country-node" class="code-content hidden">
<pre><code class="language-javascript">var request = require('request');
var options = {
  'method': 'GET',
  'url': 'https://api.phimtop1.asia/api/quoc-gia'
};
request(options, function (error, response) {
  if (error) throw new Error(error);
  console.log(response.body);
});
</code></pre>
            </div>

            <!-- cURL Example -->
            <div id="country-curl" class="code-content hidden">
<pre><code class="language-bash">curl --location 'https://api.phimtop1.asia/api/quoc-gia'</code></pre>
            </div>
        </div>
    </div>
</div>
EOT;

// Replace the end of api-country
$content = preg_replace('/      \{\n        "name": "Trung Quốc",\n        "slug": "trung-quoc"\n      \}\n    \]\n  \}\n\}<\/code><\/pre>\n        <\/div>\n    <\/div>\n<\/div>/s', "      {\n        \"name\": \"Trung Quốc\",\n        \"slug\": \"trung-quoc\"\n      }\n    ]\n  }\n}</code></pre>\n" . $append, $content);
file_put_contents("views/api-document.ejs", $content);
