<?php
// Fix remaining Alpine.js bindings with ! characters in waiting-room.html.twig

$filePath = 'templates/teleconsultation/waiting-room.html.twig';
$content = file_get_contents($filePath);

// Fix the remaining network class binding that wasn't fixed
$content = str_replace(
    ':class="connection.network === \'good\' ? \'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600\' : connection.network === \'poor\' ? \'bg-red-100 dark:bg-red-900/30 text-red-600\' : \'bg-amber-100 dark:bg-amber-900/30 text-amber-600\'"',
    ':class="{% verbatim %}connection.network === \'good\' ? \'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600\' : connection.network === \'poor\' ? \'bg-red-100 dark:bg-red-900/30 text-red-600\' : \'bg-amber-100 dark:bg-amber-900/30 text-amber-600\'{% endverbatim %}"',
    $content
);

// Fix the remaining join button class binding
$content = str_replace(
    ':class="canJoin ? \'bg-wellcare-500 text-white hover:bg-wellcare-600 hover:shadow-xl transform -translate-y-0.5\' : \'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed\'"',
    ':class="{% verbatim %}canJoin ? \'bg-wellcare-500 text-white hover:bg-wellcare-600 hover:shadow-xl transform -translate-y-0.5\' : \'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed\'{% endverbatim %}"',
    $content
);

file_put_contents($filePath, $content);
echo "Fixed remaining bindings in waiting-room.html.twig\n";
