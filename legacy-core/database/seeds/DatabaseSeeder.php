<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        # seeders that must run for the application to work are called inside the
        # ProductionSeeder class and then are called here with all seeders
        # Please add any core data to the Product Seeder classes
        $this->call('ProductionSeeder');

        // seeders that must be run before other seeders will work
        $this->call('CategoriesTableSeeder');
        $this->call('ProductsTableSeeder');
        $this->call('BundlesTableSeeder');
        $this->call('FakeUsersTableSeeder');

        // other seeders
        $this->call('AddressesTableSeeder');
        $this->call('AnnouncementsTableSeeder');
        $this->call('InventoriesTableSeeder');
        $this->call('ShippingRatesTableSeeder');
        $this->call('InvoicesTableSeeder');
        $this->call('SubscriptionsUserTableSeeder');
        $this->call('ControlpadMediaTableSeeder');
        $this->call('CouponsTableSeeder');
        $this->call('CategoryHeadersTableSeeder');
        $this->call('UpdateInvetoriesTableSeeder');

        // these need to be run last because it creates customers for the orders
        $this->call('OrdersTableSeeder');
        $this->call('PhonesTableSeeder');
        $this->call('UserSettingTableSeeder');

        $this->call('GeoLocationSeeder');
        $this->call('StoreSettingsTableSeeder');
        $this->call('StoreSettingsCorporateTableSeeder');
        $this->call('DefaultUsersProfileImageSeeder');
    }
}
