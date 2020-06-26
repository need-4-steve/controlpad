<?php

class ProductionSeeder extends DatabaseSeeder
{
    /**
     * Runs all necessary seeders for the application to work
     *
     * @return void
     */
    public function run()
    {
        $this->call('OrderStatusTableSeeder');
        $this->call('RolesTableSeeder');
        $this->call('VisibilitiesTableSeeder');
        $this->call('SellerTypesTableSeeder');
        $this->call('UsersTableSeeder');
        $this->call('SocialiteDriversTableSeeder');
        $this->call('StatesTableSeeder');
        $this->call('PriceTypesTableSeeder');
        $this->call('ProductTypesTableSeeder');
        $this->call('OrderTypesTableSeeder');
        $this->call('ReturnReasonsTableSeeder');
        $this->call('ReturnStatusesTableSeeder');
        $this->call('SettingEmailTableSeeder');
        $this->call('CustomPagesSeeder');
        $this->call('SettingsTableSeeder');
        $this->call('StoreSettingsKeysTableSeeder');
        $this->call('UseBuiltInStoreSettingSeeder');
        $this->call('SettingsRegistrationTableSeeder');
        $this->call('RegistrationTokenTableSeeder');
        $this->call('SettingsTaxesSeeder');
        $this->call('ShippingSettingsSeeder');
        $this->call('ShippingOrdersSettingsSeeder');
        $this->call('StoreSettingsTableSeeder');
        $this->call('StoreSettingsCorporateTableSeeder');
        $this->call('UpdateSettingTableSplash');
        $this->call('CommissionEngineSettingSeeder');
        $this->call('RequireRegistrationCodeSeeder');
        $this->call('ReplicatedSiteSetting');
        $this->call('SubscriptionsUserTableSeeder');
        $this->call('UserSettingTableSeeder');
        $this->call('CommEngineStatusKeysTableSeeder');
        $this->call('LocatorSettingSeeder');
        $this->call('UpdateLocatorSettingsSeeder');
        $this->call('CustomEmailSeeder');
        $this->call('CustomLinksTypesTableSeeder');
        $this->call('SubscriptionsTableSeeder');
        $this->call('AddressesTableSeeder');
        $this->call('ShippingRatesTableSeeder');
    }
}
