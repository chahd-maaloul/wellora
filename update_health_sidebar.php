<?php
// Update the health sidebar to include Health Journal and Symptom Tracker

$filePath = 'templates/layouts/app.html.twig';
$content = file_get_contents($filePath);

// Update the x-data and x-show attributes to include new routes
$oldPattern = "x-data=\"{ open: !collapsed && isGroupActive(['/health/records', '/health/prescriptions', '/health/lab-results', '/health/billing']) }\" x-show=\"!collapsed || isGroupActive(['/health/records', '/health/prescriptions', '/health/lab-results', '/health/billing']) }\"";
$newPattern = "x-data=\"{ open: !collapsed && isGroupActive(['/health/dashboard', '/health/journal', '/health/symptoms', '/health/records', '/health/prescriptions', '/health/lab-results', '/health/billing']) }\" x-show=\"!collapsed || isGroupActive(['/health/dashboard', '/health/journal', '/health/symptoms', '/health/records', '/health/prescriptions', '/health/lab-results', '/health/billing']) }\"";
$content = str_replace($oldPattern, $newPattern, $content);

// Update the title from "Dossier médical" to "Santé & Journal"
$content = str_replace(
    '<span x-show="!collapsed" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="sidebar-text">Dossier médical</span>',
    '<span x-show="!collapsed" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="sidebar-text">Santé & Journal</span>',
    $content
);

// Add new menu items after "Dashboard Santé"
$newItems = '
                                <li role="none">
                                    <a href="/health/journal" role="menuitem" class="sidebar-sublink" :class="isActive(\'/health/journal\') ? \'sidebar-sublink-active\' : \'sidebar-sublink-inactive\'">Journal de Santé</a>
                                </li>
                                <li role="none">
                                    <a href="/health/symptoms" role="menuitem" class="sidebar-sublink" :class="isActive(\'/health/symptoms\') ? \'sidebar-sublink-active\' : \'sidebar-sublink-inactive\'">Suivi des Symptômes</a>
                                </li>';

$content = str_replace(
    '                                    <a href="/health/dashboard" role="menuitem" class="sidebar-sublink" :class="isActive(\'/health/dashboard\') ? \'sidebar-sublink-active\' : \'sidebar-sublink-inactive\'">Dashboard Santé</a>
                                </li>
                                <li role="none">
                                    <a href="/health/records" role="menuitem" class="sidebar-sublink" :class="isActive(\'/health/records\') ? \'sidebar-sublink-active\' : \'sidebar-sublink-inactive\'">Historique Médical</a>',
    '                                    <a href="/health/dashboard" role="menuitem" class="sidebar-sublink" :class="isActive(\'/health/dashboard\') ? \'sidebar-sublink-active\' : \'sidebar-sublink-inactive\'">Dashboard Santé</a>
                                </li>' . $newItems . '
                                <li role="none">
                                    <a href="/health/records" role="menuitem" class="sidebar-sublink" :class="isActive(\'/health/records\') ? \'sidebar-sublink-active\' : \'sidebar-sublink-inactive\'">Historique Médical</a>',
    $content
);

file_put_contents($filePath, $content);
echo "Updated sidebar with Health Journal and Symptom Tracker entries\n";
