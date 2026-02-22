<?php

require 'vendor/autoload.php';

use App\Entity\User;
use App\Entity\Medecin;
use App\Entity\Coach;
use App\Entity\Nutritionist;
use App\Entity\Patient;
use App\Entity\Administrator;

// Load environment variables from .env.local
if (file_exists('.env.local')) {
    $dotenv = new \Symfony\Component\Dotenv\Dotenv();
    $dotenv->load('.env.local');
}

echo 'Starting test...' . PHP_EOL;

try {
    $kernel = new \App\Kernel('dev', true);
    $kernel->boot();
    echo 'Kernel booted' . PHP_EOL;
    
    $container = $kernel->getContainer();
    echo 'Container obtained' . PHP_EOL;
    
    $em = $container->get('doctrine')->getManager();
    echo 'EntityManager obtained' . PHP_EOL;
    
    $repo = $em->getRepository(User::class);
    echo 'Repository obtained' . PHP_EOL;
    
    $allUsers = $repo->findAll();
    echo 'All users: ' . count($allUsers) . PHP_EOL;
    
    // Test findAllWithFilters with role filter
    $result = $repo->findAllWithFilters('ROLE_MEDECIN', null, null, null);
    echo 'findAllWithFilters ROLE_MEDECIN: ' . count($result) . PHP_EOL;
    foreach ($result as $u) { echo ' - ' . $u->getEmail() . PHP_EOL; }
    
    $result2 = $repo->findAllWithFilters('ROLE_COACH', null, null, null);
    echo 'findAllWithFilters ROLE_COACH: ' . count($result2) . PHP_EOL;
    
    $result3 = $repo->findAllWithFilters('ROLE_NUTRITIONIST', null, null, null);
    echo 'findAllWithFilters ROLE_NUTRITIONIST: ' . count($result3) . PHP_EOL;
    
    $result4 = $repo->findAllWithFilters('ROLE_PATIENT', null, null, null);
    echo 'findAllWithFilters ROLE_PATIENT: ' . count($result4) . PHP_EOL;
    
    $result5 = $repo->findAllWithFilters('ROLE_ADMIN', null, null, null);
    echo 'findAllWithFilters ROLE_ADMIN: ' . count($result5) . PHP_EOL;
    
    // Test getUserStatistics
    $stats = $repo->getUserStatistics();
    echo PHP_EOL . 'Statistics:' . PHP_EOL;
    echo 'Total: ' . $stats['total'] . PHP_EOL;
    echo 'Patients: ' . $stats['patients'] . PHP_EOL;
    echo 'Medecins: ' . $stats['medecins'] . PHP_EOL;
    echo 'Coaches: ' . $stats['coaches'] . PHP_EOL;
    echo 'Nutritionists: ' . $stats['nutritionists'] . PHP_EOL;
    echo 'Admins: ' . $stats['admins'] . PHP_EOL;
    echo 'Active: ' . $stats['active'] . PHP_EOL;
    
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
}
