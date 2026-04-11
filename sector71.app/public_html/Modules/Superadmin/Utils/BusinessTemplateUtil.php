<?php

namespace Modules\Superadmin\Utils;

use App\Brands;
use App\Category;
use App\ExpenseCategory;
use App\Product;
use App\ProductVariation;
use App\TaxRate;
use App\Unit;
use App\Variation;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Entities\BusinessTemplate;

class BusinessTemplateUtil
{
    public function applyTemplate($businessId, $templateId, $userId)
    {
        $template = BusinessTemplate::find($templateId);

        if (!$template) {
            return [
                'success' => false,
                'msg' => 'Template not found'
            ];
        }

        try {
            DB::beginTransaction();

            $results = [
                'categories' => 0,
                'units' => 0,
                'tax_rates' => 0,
                'brands' => 0,
                'products' => 0,
                'expense_categories' => 0,
            ];

            if (!empty($template->categories)) {
                $results['categories'] = $this->createCategories($businessId, $template->categories, $userId);
            }

            if (!empty($template->units)) {
                $results['units'] = $this->createUnits($businessId, $template->units, $userId);
            }

            if (!empty($template->tax_rates)) {
                $results['tax_rates'] = $this->createTaxRates($businessId, $template->tax_rates, $userId);
            }

            if (!empty($template->brands)) {
                $results['brands'] = $this->createBrands($businessId, $template->brands, $userId);
            }

            if (!empty($template->expense_categories)) {
                $results['expense_categories'] = $this->createExpenseCategories($businessId, $template->expense_categories, $userId);
            }

            if (!empty($template->products)) {
                $results['products'] = $this->createProducts($businessId, $template->products, $userId);
            }

            DB::commit();

            return [
                'success' => true,
                'msg' => 'Template applied successfully',
                'data' => $results
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('BusinessTemplateUtil Error: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            
            return [
                'success' => false,
                'msg' => 'Error applying template: ' . $e->getMessage()
            ];
        }
    }

    protected function createCategories($businessId, $categories, $userId)
    {
        $count = 0;
        foreach ($categories as $category) {
            $exists = Category::where('business_id', $businessId)
                ->where('name', $category['name'])
                ->where('category_type', 'product')
                ->exists();

            if (!$exists) {
                Category::create([
                    'name' => $category['name'],
                    'business_id' => $businessId,
                    'short_code' => $category['short_code'] ?? null,
                    'parent_id' => 0,
                    'description' => $category['description'] ?? null,
                    'category_type' => 'product',
                    'created_by' => $userId,
                ]);
                $count++;

                if (!empty($category['subcategories'])) {
                    $parentCategory = Category::where('business_id', $businessId)
                        ->where('name', $category['name'])
                        ->where('category_type', 'product')
                        ->first();

                    foreach ($category['subcategories'] as $subcategory) {
                        $subExists = Category::where('business_id', $businessId)
                            ->where('name', $subcategory['name'])
                            ->where('category_type', 'product')
                            ->exists();

                        if (!$subExists) {
                            Category::create([
                                'name' => $subcategory['name'],
                                'business_id' => $businessId,
                                'short_code' => $subcategory['short_code'] ?? null,
                                'parent_id' => $parentCategory->id,
                                'description' => $subcategory['description'] ?? null,
                                'category_type' => 'product',
                                'created_by' => $userId,
                            ]);
                            $count++;
                        }
                    }
                }
            }
        }
        return $count;
    }

    protected function createUnits($businessId, $units, $userId)
    {
        $count = 0;
        foreach ($units as $unit) {
            $exists = Unit::where('business_id', $businessId)
                ->where('actual_name', $unit['name'])
                ->exists();

            if (!$exists) {
                Unit::create([
                    'business_id' => $businessId,
                    'actual_name' => $unit['name'],
                    'short_name' => $unit['short_name'],
                    'allow_decimal' => $unit['allow_decimal'] ?? 0,
                    'created_by' => $userId,
                ]);
                $count++;
            }
        }
        return $count;
    }

    protected function createTaxRates($businessId, $taxRates, $userId)
    {
        $count = 0;
        foreach ($taxRates as $tax) {
            $exists = TaxRate::where('business_id', $businessId)
                ->where('name', $tax['name'])
                ->exists();

            if (!$exists) {
                TaxRate::create([
                    'business_id' => $businessId,
                    'name' => $tax['name'],
                    'amount' => $tax['rate'],
                    'is_tax_group' => 0,
                    'created_by' => $userId,
                ]);
                $count++;
            }
        }
        return $count;
    }

    protected function createBrands($businessId, $brands, $userId)
    {
        $count = 0;
        foreach ($brands as $brand) {
            $exists = Brands::where('business_id', $businessId)
                ->where('name', $brand['name'])
                ->exists();

            if (!$exists) {
                Brands::create([
                    'business_id' => $businessId,
                    'name' => $brand['name'],
                    'description' => $brand['description'] ?? null,
                    'created_by' => $userId,
                ]);
                $count++;
            }
        }
        return $count;
    }

    protected function createExpenseCategories($businessId, $expenseCategories, $userId)
    {
        $count = 0;
        foreach ($expenseCategories as $expenseCat) {
            $exists = ExpenseCategory::where('business_id', $businessId)
                ->where('name', $expenseCat['name'])
                ->exists();

            if (!$exists) {
                ExpenseCategory::create([
                    'business_id' => $businessId,
                    'name' => $expenseCat['name'],
                    'code' => $expenseCat['code'] ?? null,
                ]);
                $count++;
            }
        }
        return $count;
    }

    protected function createProducts($businessId, $products, $userId)
    {
        $count = 0;
        
        $defaultUnit = Unit::where('business_id', $businessId)->first();
        
        if (!$defaultUnit) {
            return 0;
        }

        foreach ($products as $product) {
            $exists = Product::where('business_id', $businessId)
                ->where('name', $product['name'])
                ->exists();

            if (!$exists) {
                $sku = !empty($product['sku']) ? $product['sku'] : $this->generateSku($businessId);

                $newProduct = Product::create([
                    'name' => $product['name'],
                    'business_id' => $businessId,
                    'type' => 'single',
                    'unit_id' => $defaultUnit->id,
                    'enable_stock' => 1,
                    'alert_quantity' => 5,
                    'sku' => $sku,
                    'product_description' => $product['description'] ?? null,
                    'created_by' => $userId,
                    'is_inactive' => 0,
                ]);

                $productVariation = ProductVariation::create([
                    'name' => 'DUMMY',
                    'product_id' => $newProduct->id,
                    'is_dummy' => 1,
                ]);

                $variation = Variation::create([
                    'name' => 'DUMMY',
                    'product_id' => $newProduct->id,
                    'product_variation_id' => $productVariation->id,
                    'sub_sku' => $sku,
                    'default_purchase_price' => $product['purchase_price'] ?? 0,
                    'dpp_inc_tax' => $product['purchase_price'] ?? 0,
                    'profit_percent' => 25,
                    'default_sell_price' => $product['selling_price'] ?? 0,
                    'sell_price_inc_tax' => $product['selling_price'] ?? 0,
                ]);

                $count++;
            }
        }
        return $count;
    }

    protected function generateSku($businessId)
    {
        $count = Product::where('business_id', $businessId)->count() + 1;
        return 'SKU-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function getTemplatesForDropdown()
    {
        return BusinessTemplate::active()
            ->orderBy('sort_order')
            ->pluck('name', 'id')
            ->prepend(__('messages.please_select'), '');
    }
}
