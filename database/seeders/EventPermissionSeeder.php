<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;

class EventPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds to add event permissions.
     *
     * @return void
     */
    public function run()
    {
        // Check if event permissions already exist
        $existingPermission = Permission::where('attribute', 'event')->first();

        if ($existingPermission) {
            $this->command->info('Event permissions already exist. Skipping...');
            return;
        }

        // Add event permissions
        $eventPermission = new Permission();
        $eventPermission->attribute = 'event';
        $eventPermission->keywords = [
            'read' => 'event_read',
            'create' => 'event_create',
            'update' => 'event_update',
            'delete' => 'event_delete',
        ];
        $eventPermission->save();

        // Add event_product permissions
        $eventProductPermission = new Permission();
        $eventProductPermission->attribute = 'event_product';
        $eventProductPermission->keywords = [
            'read' => 'event_product_read',
            'create' => 'event_product_create',
            'update' => 'event_product_update',
            'delete' => 'event_product_delete',
        ];
        $eventProductPermission->save();

        $this->command->info('Event permissions created successfully!');
        $this->command->newLine();
        $this->command->info('Event Permissions:');
        $this->command->info('- event_read');
        $this->command->info('- event_create');
        $this->command->info('- event_update');
        $this->command->info('- event_delete');
        $this->command->info('- event_product_read');
        $this->command->info('- event_product_create');
        $this->command->info('- event_product_update');
        $this->command->info('- event_product_delete');
        $this->command->newLine();
        $this->command->comment('Now run: php artisan db:seed --class=RoleSeeder to assign permissions to admin role');
    }
}