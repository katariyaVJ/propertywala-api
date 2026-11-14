<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['config_key' => 'app_name', 'config_value' => 'Propertywala', 'description' => 'Application Name'],
            ['config_key' => 'helpline_phone', 'config_value' => '+91 98765 00000', 'description' => 'Customer Care Phone Helpline'],
            ['config_key' => 'support_email', 'config_value' => 'support@propertywala.com', 'description' => 'Official Support Email'],
            ['config_key' => 'app_min_version', 'config_value' => '1.0.0', 'description' => 'Minimum Flutter App Version Required'],
            ['config_key' => 'terms_url', 'config_value' => 'https://propertywala.com/terms', 'description' => 'Terms of Service Link'],
            ['config_key' => 'privacy_policy_url', 'config_value' => 'https://propertywala.com/privacy', 'description' => 'Privacy Policy Link'],
            ['config_key' => 'pro_plan_monthly_price', 'config_value' => '999', 'description' => 'Pro Membership Monthly Fee (INR)'],
            ['config_key' => 'pro_plan_contact_limit', 'config_value' => '40', 'description' => 'Monthly Free Direct Contact Unlocks for Pro Members'],
            ['config_key' => 'sarfaesi_disclaimer', 'config_value' => 'Propertywala acts strictly as an information discovery portal for bank auctions and seized assets under SARFAESI Act. All tenders must be submitted directly to the concerned nodal bank.', 'description' => 'SARFAESI Bank Auction Legal Disclaimer'],
        ];

        foreach ($configs as $cfg) {
            SystemConfig::updateOrCreate(['config_key' => $cfg['config_key']], $cfg);
        }
    }
}
