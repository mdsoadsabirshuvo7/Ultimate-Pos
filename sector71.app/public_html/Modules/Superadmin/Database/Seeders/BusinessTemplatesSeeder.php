<?php

namespace Modules\Superadmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Superadmin\Entities\BusinessTemplate;

class BusinessTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'name' => 'All In One',
                'slug' => 'all-in-one',
                'description' => 'Complete business setup with all features',
                'icon' => 'fa fa-star',
                'color' => '#3c8dbc',
                'sort_order' => 1,
                'categories' => [
                    ['name' => 'Electronics', 'description' => 'Electronic items'],
                    ['name' => 'Clothing', 'description' => 'Apparel'],
                    ['name' => 'Food', 'description' => 'Food items'],
                    ['name' => 'Services', 'description' => 'Service offerings'],
                ],
                'units' => [
                    ['name' => 'Piece', 'short_name' => 'Pc', 'allow_decimal' => 0],
                    ['name' => 'Kilogram', 'short_name' => 'Kg', 'allow_decimal' => 1],
                    ['name' => 'Dozen', 'short_name' => 'Dz', 'allow_decimal' => 0],
                ],
                'tax_rates' => [
                    ['name' => 'GST 5%', 'rate' => 5],
                    ['name' => 'GST 12%', 'rate' => 12],
                    ['name' => 'GST 18%', 'rate' => 18],
                ],
                'products' => [],
                'brands' => [
                    ['name' => 'Generic', 'description' => 'Generic brand'],
                ],
            ],
            [
                'name' => 'Pharmacy',
                'slug' => 'pharmacy',
                'description' => 'Medical store and pharmacy setup',
                'icon' => 'fa fa-medkit',
                'color' => '#00a65a',
                'sort_order' => 2,
                'categories' => [
                    ['name' => 'Medicines', 'description' => 'Pharmaceutical medicines'],
                    ['name' => 'First Aid', 'description' => 'First aid supplies'],
                    ['name' => 'Personal Care', 'description' => 'Personal hygiene products'],
                    ['name' => 'Baby Care', 'description' => 'Baby products'],
                ],
                'units' => [
                    ['name' => 'Tablet', 'short_name' => 'Tab', 'allow_decimal' => 0],
                    ['name' => 'Strip', 'short_name' => 'Strip', 'allow_decimal' => 0],
                    ['name' => 'Bottle', 'short_name' => 'Btl', 'allow_decimal' => 0],
                    ['name' => 'Piece', 'short_name' => 'Pc', 'allow_decimal' => 0],
                ],
                'tax_rates' => [
                    ['name' => 'GST 5%', 'rate' => 5],
                    ['name' => 'GST 12%', 'rate' => 12],
                ],
                'products' => [],
                'brands' => [
                    ['name' => 'Generic', 'description' => 'Generic medicines'],
                ],
            ],
            [
                'name' => 'Multi-Service Center',
                'slug' => 'multi-service-center',
                'description' => 'Repair and service center setup',
                'icon' => 'fa fa-wrench',
                'color' => '#f39c12',
                'sort_order' => 3,
                'categories' => [
                    ['name' => 'Mobile Repair', 'description' => 'Mobile phone repairs'],
                    ['name' => 'Computer Repair', 'description' => 'Computer/Laptop repairs'],
                    ['name' => 'Appliance Repair', 'description' => 'Home appliance repairs'],
                    ['name' => 'Parts', 'description' => 'Spare parts'],
                ],
                'units' => [
                    ['name' => 'Service', 'short_name' => 'Srv', 'allow_decimal' => 0],
                    ['name' => 'Piece', 'short_name' => 'Pc', 'allow_decimal' => 0],
                    ['name' => 'Hour', 'short_name' => 'Hr', 'allow_decimal' => 1],
                ],
                'tax_rates' => [
                    ['name' => 'Service Tax 18%', 'rate' => 18],
                    ['name' => 'GST 12%', 'rate' => 12],
                ],
                'products' => [],
                'brands' => [],
            ],
            [
                'name' => 'Electronics & Mobile Shop',
                'slug' => 'electronics-mobile-shop',
                'description' => 'Electronics and mobile store',
                'icon' => 'fa fa-desktop',
                'color' => '#605ca8',
                'sort_order' => 4,
                'categories' => [
                    ['name' => 'Mobile Phones', 'description' => 'Smartphones and feature phones'],
                    ['name' => 'Accessories', 'description' => 'Mobile accessories'],
                    ['name' => 'Computers', 'description' => 'Laptops and desktops'],
                    ['name' => 'Audio', 'description' => 'Headphones and speakers'],
                ],
                'units' => [
                    ['name' => 'Piece', 'short_name' => 'Pc', 'allow_decimal' => 0],
                    ['name' => 'Set', 'short_name' => 'Set', 'allow_decimal' => 0],
                ],
                'tax_rates' => [
                    ['name' => 'GST 12%', 'rate' => 12],
                    ['name' => 'GST 18%', 'rate' => 18],
                ],
                'products' => [],
                'brands' => [
                    ['name' => 'Samsung', 'description' => ''],
                    ['name' => 'Apple', 'description' => ''],
                    ['name' => 'Xiaomi', 'description' => ''],
                ],
            ],
            [
                'name' => 'Super Market',
                'slug' => 'super-market',
                'description' => 'Grocery and supermarket setup',
                'icon' => 'fa fa-shopping-cart',
                'color' => '#dd4b39',
                'sort_order' => 5,
                'categories' => [
                    ['name' => 'Groceries', 'description' => 'Daily groceries'],
                    ['name' => 'Dairy', 'description' => 'Milk and dairy products'],
                    ['name' => 'Beverages', 'description' => 'Drinks and beverages'],
                    ['name' => 'Snacks', 'description' => 'Snacks and chips'],
                    ['name' => 'Personal Care', 'description' => 'Hygiene products'],
                ],
                'units' => [
                    ['name' => 'Piece', 'short_name' => 'Pc', 'allow_decimal' => 0],
                    ['name' => 'Kilogram', 'short_name' => 'Kg', 'allow_decimal' => 1],
                    ['name' => 'Liter', 'short_name' => 'L', 'allow_decimal' => 1],
                    ['name' => 'Packet', 'short_name' => 'Pkt', 'allow_decimal' => 0],
                ],
                'tax_rates' => [
                    ['name' => 'GST 0%', 'rate' => 0],
                    ['name' => 'GST 5%', 'rate' => 5],
                    ['name' => 'GST 12%', 'rate' => 12],
                ],
                'products' => [],
                'brands' => [],
            ],
            [
                'name' => 'Restaurant',
                'slug' => 'restaurant',
                'description' => 'Restaurant and food service setup',
                'icon' => 'fa fa-cutlery',
                'color' => '#00c0ef',
                'sort_order' => 6,
                'categories' => [
                    ['name' => 'Starters', 'description' => 'Appetizers'],
                    ['name' => 'Main Course', 'description' => 'Main dishes'],
                    ['name' => 'Desserts', 'description' => 'Sweet dishes'],
                    ['name' => 'Beverages', 'description' => 'Drinks'],
                ],
                'units' => [
                    ['name' => 'Plate', 'short_name' => 'Plate', 'allow_decimal' => 0],
                    ['name' => 'Piece', 'short_name' => 'Pc', 'allow_decimal' => 0],
                    ['name' => 'Glass', 'short_name' => 'Glass', 'allow_decimal' => 0],
                    ['name' => 'Half Plate', 'short_name' => 'Half', 'allow_decimal' => 0],
                ],
                'tax_rates' => [
                    ['name' => 'GST 5%', 'rate' => 5],
                ],
                'products' => [],
                'brands' => [],
            ],
        ];

        foreach ($templates as $template) {
            BusinessTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        $this->command->info('Business templates seeded successfully!');
    }
}
