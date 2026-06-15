<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\EmailConfig;

class EmailConfigSeeder extends Seeder {
    public function run(): void {
        EmailConfig::firstOrCreate(['from_address' => 'billing@twb.to'], [
            'mailer' => 'smtp', 'host' => 'smtp.mailtrap.io', 'port' => 587,
            'username' => null, 'password' => null, 'encryption' => 'tls',
            'from_address' => 'billing@twb.to', 'from_name' => 'TWB Water Billing',
            'is_active' => false, 'notes' => 'Default config. Update credentials before enabling.',
        ]);
    }
}
