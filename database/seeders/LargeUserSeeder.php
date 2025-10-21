<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LargeUserSeeder extends Seeder
{
    /**
     * Seed 50,000 users for performance testing
     */
    public function run()
    {
        $this->command->info('Starting to seed 50,000 users...');
        
        // Use MySQL connection
        $connection = DB::connection('mysql');
        
        // Departments for variety
        $departments = ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance', 'Operations', 'Support', 'Product'];
        
        // Batch size for efficient insertion
        $batchSize = 1000;
        $totalRows = 50000;
        $batches = $totalRows / $batchSize;
        
        $startTime = microtime(true);
        
        for ($batch = 0; $batch < $batches; $batch++) {
            $users = [];
            
            for ($i = 0; $i < $batchSize; $i++) {
                $num = ($batch * $batchSize) + $i + 1;
                
                $users[] = [
                    'name' => "User " . str_pad($num, 5, '0', STR_PAD_LEFT),
                    'email' => "user{$num}@example.com",
                    'department' => $departments[array_rand($departments)],
                    'salary' => rand(40000, 150000),
                    'created_at' => now()->subDays(rand(0, 365))->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ];
            }
            
            // Bulk insert for performance
            $connection->table('users')->insert($users);
            
            $progress = ($batch + 1) * $batchSize;
            $this->command->info("Inserted {$progress} / {$totalRows} users...");
        }
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        $this->command->info("✅ Successfully seeded 50,000 users in {$duration} seconds!");
        
        // Show stats
        $totalUsers = $connection->table('users')->count();
        $this->command->info("Total users in database: {$totalUsers}");
    }
}




