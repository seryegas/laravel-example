<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Core\Database\Seeders\UserSeeder;
use App\Modules\Service\Database\Seeders\ServiceSeeder;
use App\Modules\Booking\Database\Seeders\BookingSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ServiceSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
