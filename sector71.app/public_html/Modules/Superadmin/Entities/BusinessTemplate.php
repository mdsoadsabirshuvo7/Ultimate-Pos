<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BusinessTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'business_templates';

    protected $guarded = ['id'];

    protected $casts = [
        'categories' => 'array',
        'units' => 'array',
        'tax_rates' => 'array',
        'products' => 'array',
        'brands' => 'array',
        'expense_categories' => 'array',
        'business_settings' => 'array',
        'enabled_modules' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public static function listTemplates()
    {
        return self::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public static function forDropdown()
    {
        return self::active()
            ->orderBy('sort_order')
            ->pluck('name', 'id')
            ->prepend(__('messages.please_select'), '');
    }

    public function getCategoriesCountAttribute()
    {
        return is_array($this->categories) ? count($this->categories) : 0;
    }

    public function getUnitsCountAttribute()
    {
        return is_array($this->units) ? count($this->units) : 0;
    }

    public function getProductsCountAttribute()
    {
        return is_array($this->products) ? count($this->products) : 0;
    }

    public function getTaxRatesCountAttribute()
    {
        return is_array($this->tax_rates) ? count($this->tax_rates) : 0;
    }
}
