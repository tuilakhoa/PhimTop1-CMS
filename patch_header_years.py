import re

with open('themes/phimhayok/header.php', 'r', encoding='utf-8') as f:
    content = f.read()

old_loop = """<?php for($y = date('Y'); $y >= 2010; $y--): ?>
                                        <a href="/nam/<?= $y ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded text-center transition-colors"><?= $y ?></a>
                                    <?php endfor; ?>"""

new_loop = """<?php 
                                    $apiYears = function_exists('getYearsList') ? getYearsList() : [];
                                    $yearsList = [];
                                    if (is_array($apiYears)) {
                                        // Handle both flat array and object array
                                        $items = $apiYears['data'] ?? ($apiYears['items'] ?? $apiYears);
                                        if (is_array($items)) {
                                            foreach ($items as $item) {
                                                if (is_array($item) && isset($item['name'])) {
                                                    $yearsList[] = $item['name'];
                                                } elseif (is_string($item) || is_numeric($item)) {
                                                    $yearsList[] = $item;
                                                }
                                            }
                                        }
                                    }
                                    if (empty($yearsList)) {
                                        for($y = date('Y'); $y >= 2010; $y--) $yearsList[] = $y;
                                    }
                                    // Sort desc
                                    rsort($yearsList);
                                    foreach ($yearsList as $y): 
                                    ?>
                                        <a href="/nam/<?= htmlspecialchars($y) ?>" class="text-sm text-gray-400 hover:text-white hover:bg-gray-800 px-2 py-1 rounded text-center transition-colors"><?= htmlspecialchars($y) ?></a>
                                    <?php endforeach; ?>"""

content = content.replace(old_loop, new_loop)

with open('themes/phimhayok/header.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Patched header years")
