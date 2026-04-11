@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.add_business_template'))

@section('content')
@include('superadmin::layouts.nav')
<section class="content-header">
    <h1>@lang('superadmin::lang.add_business_template')</h1>
</section>

<section class="content">
    {!! Form::open(['action' => '\Modules\Superadmin\Http\Controllers\BusinessTemplateController@store', 'method' => 'post', 'id' => 'template_form']) !!}
    
    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title">@lang('superadmin::lang.basic_info')</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('name', __('lang_v1.name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('superadmin::lang.eg_restaurant')]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('icon', __('superadmin::lang.icon') . ':') !!}
                        {!! Form::select('icon', $icons, null, ['class' => 'form-control select2']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('color', __('superadmin::lang.color') . ':') !!}
                        {!! Form::select('color', $colors, '#3c8dbc', ['class' => 'form-control select2']) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('description', __('lang_v1.description') . ':') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('sort_order', __('superadmin::lang.sort_order') . ':') !!}
                        {!! Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0]) !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <br>
                        <div class="checkbox">
                            <label>
                                {!! Form::checkbox('is_active', 1, true, ['class' => 'input-icheck']) !!}
                                @lang('superadmin::lang.is_active')
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label>@lang('lang_v1.preview'):</label>
                    <div id="template_preview" style="display: inline-block; padding: 15px 25px; border-radius: 5px; background-color: #3c8dbc; color: white; text-align: center;">
                        <i id="preview_icon" class=""></i>
                        <span id="preview_name">Template Name</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-folder"></i> @lang('category.categories')</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" id="add_category">
                    <i class="fa fa-plus"></i> @lang('category.add_category')
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="categories_container">
                <p class="text-muted no-data-msg">No categories added</p>
            </div>
            <input type="hidden" name="categories_data" id="categories_data">
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-balance-scale"></i> @lang('unit.units')</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" id="add_unit">
                    <i class="fa fa-plus"></i> @lang('unit.add_unit')
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="units_container">
                <p class="text-muted no-data-msg">No units added</p>
            </div>
            <input type="hidden" name="units_data" id="units_data">
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-percent"></i> @lang('tax_rate.tax_rates')</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" id="add_tax">
                    <i class="fa fa-plus"></i> @lang('tax_rate.add_tax_rate')
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="tax_rates_container">
                <p class="text-muted no-data-msg">No tax rates added</p>
            </div>
            <input type="hidden" name="tax_rates_data" id="tax_rates_data">
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-cube"></i> @lang('superadmin::lang.sample_products')</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" id="add_product">
                    <i class="fa fa-plus"></i> @lang('product.add_product')
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="products_container">
                <p class="text-muted no-data-msg">No products added</p>
            </div>
            <input type="hidden" name="products_data" id="products_data">
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-tags"></i> @lang('brand.brands')</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-sm" id="add_brand">
                    <i class="fa fa-plus"></i> @lang('brand.add_brand')
                </button>
            </div>
        </div>
        <div class="box-body">
            <div id="brands_container">
                <p class="text-muted no-data-msg">No brands added</p>
            </div>
            <input type="hidden" name="brands_data" id="brands_data">
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> @lang('messages.save')
                    </button>
                </div>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
</section>

<div class="modal fade" id="category_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('category.add_category')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('category.category_name'):*</label>
                    <input type="text" class="form-control" id="category_name">
                </div>
                <div class="form-group">
                    <label>@lang('category.description'):</label>
                    <textarea class="form-control" id="category_description" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="btn btn-primary" id="save_category">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="unit_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('unit.add_unit')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('unit.name'):*</label>
                    <input type="text" class="form-control" id="unit_name">
                </div>
                <div class="form-group">
                    <label>@lang('unit.short_name'):*</label>
                    <input type="text" class="form-control" id="unit_short_name">
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="unit_allow_decimal"> @lang('unit.allow_decimal')
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="btn btn-primary" id="save_unit">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tax_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('tax_rate.add_tax_rate')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('tax_rate.name'):*</label>
                    <input type="text" class="form-control" id="tax_name">
                </div>
                <div class="form-group">
                    <label>@lang('tax_rate.rate'):* (%)</label>
                    <input type="number" step="0.01" class="form-control" id="tax_rate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="btn btn-primary" id="save_tax">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="product_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('product.add_product')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('product.product_name'):*</label>
                    <input type="text" class="form-control" id="product_name">
                </div>
                <div class="form-group">
                    <label>@lang('product.sku'):</label>
                    <input type="text" class="form-control" id="product_sku">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('product.default_purchase_price'):</label>
                            <input type="number" step="0.01" class="form-control" id="product_purchase_price" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('product.default_selling_price'):</label>
                            <input type="number" step="0.01" class="form-control" id="product_selling_price" value="0">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="btn btn-primary" id="save_product">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="brand_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('brand.add_brand')</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>@lang('brand.brand_name'):*</label>
                    <input type="text" class="form-control" id="brand_name">
                </div>
                <div class="form-group">
                    <label>@lang('brand.short_description'):</label>
                    <textarea class="form-control" id="brand_description" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="btn btn-primary" id="save_brand">@lang('messages.save')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var categories = [];
    var units = [];
    var tax_rates = [];
    var products = [];
    var brands = [];

    function updatePreview() {
        var name = $('#name').val() || 'Template Name';
        var icon = $('#icon').val();
        var color = $('#color').val() || '#3c8dbc';
        $('#preview_name').text(name);
        $('#preview_icon').attr('class', icon ? icon + ' ' : '');
        $('#template_preview').css('background-color', color);
    }
    $('#name, #icon, #color').on('change keyup', updatePreview);

    function renderCategories() {
        var container = $('#categories_container');
        container.empty();
        if (categories.length === 0) {
            container.html('<p class="text-muted">No categories added</p>');
        } else {
            var html = '<table class="table table-condensed table-bordered"><thead><tr><th>Name</th><th>Description</th><th>Action</th></tr></thead><tbody>';
            categories.forEach(function(cat, index) {
                html += '<tr><td>' + cat.name + '</td><td>' + (cat.description || '-') + '</td><td><button type="button" class="btn btn-danger btn-xs remove-category" data-index="' + index + '"><i class="fa fa-trash"></i></button></td></tr>';
            });
            html += '</tbody></table>';
            container.html(html);
        }
        $('#categories_data').val(JSON.stringify(categories));
    }

    $('#add_category').click(function() {
        $('#category_name').val('');
        $('#category_description').val('');
        $('#category_modal').modal('show');
    });

    $('#save_category').click(function() {
        var name = $('#category_name').val().trim();
        if (!name) { toastr.error('Name is required'); return; }
        categories.push({ name: name, description: $('#category_description').val().trim() });
        renderCategories();
        $('#category_modal').modal('hide');
    });

    $(document).on('click', '.remove-category', function() {
        categories.splice($(this).data('index'), 1);
        renderCategories();
    });

    function renderUnits() {
        var container = $('#units_container');
        container.empty();
        if (units.length === 0) {
            container.html('<p class="text-muted">No units added</p>');
        } else {
            var html = '<table class="table table-condensed table-bordered"><thead><tr><th>Name</th><th>Short Name</th><th>Allow Decimal</th><th>Action</th></tr></thead><tbody>';
            units.forEach(function(unit, index) {
                html += '<tr><td>' + unit.name + '</td><td>' + unit.short_name + '</td><td>' + (unit.allow_decimal ? 'Yes' : 'No') + '</td><td><button type="button" class="btn btn-danger btn-xs remove-unit" data-index="' + index + '"><i class="fa fa-trash"></i></button></td></tr>';
            });
            html += '</tbody></table>';
            container.html(html);
        }
        $('#units_data').val(JSON.stringify(units));
    }

    $('#add_unit').click(function() {
        $('#unit_name').val('');
        $('#unit_short_name').val('');
        $('#unit_allow_decimal').prop('checked', false);
        $('#unit_modal').modal('show');
    });

    $('#save_unit').click(function() {
        var name = $('#unit_name').val().trim();
        var short_name = $('#unit_short_name').val().trim();
        if (!name || !short_name) { toastr.error('Name and Short Name are required'); return; }
        units.push({ name: name, short_name: short_name, allow_decimal: $('#unit_allow_decimal').is(':checked') ? 1 : 0 });
        renderUnits();
        $('#unit_modal').modal('hide');
    });

    $(document).on('click', '.remove-unit', function() {
        units.splice($(this).data('index'), 1);
        renderUnits();
    });

    function renderTaxRates() {
        var container = $('#tax_rates_container');
        container.empty();
        if (tax_rates.length === 0) {
            container.html('<p class="text-muted">No tax rates added</p>');
        } else {
            var html = '<table class="table table-condensed table-bordered"><thead><tr><th>Name</th><th>Rate (%)</th><th>Action</th></tr></thead><tbody>';
            tax_rates.forEach(function(tax, index) {
                html += '<tr><td>' + tax.name + '</td><td>' + tax.rate + '%</td><td><button type="button" class="btn btn-danger btn-xs remove-tax" data-index="' + index + '"><i class="fa fa-trash"></i></button></td></tr>';
            });
            html += '</tbody></table>';
            container.html(html);
        }
        $('#tax_rates_data').val(JSON.stringify(tax_rates));
    }

    $('#add_tax').click(function() {
        $('#tax_name').val('');
        $('#tax_rate').val('');
        $('#tax_modal').modal('show');
    });

    $('#save_tax').click(function() {
        var name = $('#tax_name').val().trim();
        var rate = $('#tax_rate').val();
        if (!name || rate === '') { toastr.error('Name and Rate are required'); return; }
        tax_rates.push({ name: name, rate: parseFloat(rate) });
        renderTaxRates();
        $('#tax_modal').modal('hide');
    });

    $(document).on('click', '.remove-tax', function() {
        tax_rates.splice($(this).data('index'), 1);
        renderTaxRates();
    });

    function renderProducts() {
        var container = $('#products_container');
        container.empty();
        if (products.length === 0) {
            container.html('<p class="text-muted">No products added</p>');
        } else {
            var html = '<table class="table table-condensed table-bordered"><thead><tr><th>Name</th><th>SKU</th><th>Purchase Price</th><th>Selling Price</th><th>Action</th></tr></thead><tbody>';
            products.forEach(function(prod, index) {
                html += '<tr><td>' + prod.name + '</td><td>' + (prod.sku || '-') + '</td><td>' + prod.purchase_price + '</td><td>' + prod.selling_price + '</td><td><button type="button" class="btn btn-danger btn-xs remove-product" data-index="' + index + '"><i class="fa fa-trash"></i></button></td></tr>';
            });
            html += '</tbody></table>';
            container.html(html);
        }
        $('#products_data').val(JSON.stringify(products));
    }

    $('#add_product').click(function() {
        $('#product_name').val('');
        $('#product_sku').val('');
        $('#product_purchase_price').val('0');
        $('#product_selling_price').val('0');
        $('#product_modal').modal('show');
    });

    $('#save_product').click(function() {
        var name = $('#product_name').val().trim();
        if (!name) { toastr.error('Name is required'); return; }
        products.push({
            name: name,
            sku: $('#product_sku').val().trim(),
            purchase_price: parseFloat($('#product_purchase_price').val()) || 0,
            selling_price: parseFloat($('#product_selling_price').val()) || 0
        });
        renderProducts();
        $('#product_modal').modal('hide');
    });

    $(document).on('click', '.remove-product', function() {
        products.splice($(this).data('index'), 1);
        renderProducts();
    });

    function renderBrands() {
        var container = $('#brands_container');
        container.empty();
        if (brands.length === 0) {
            container.html('<p class="text-muted">No brands added</p>');
        } else {
            var html = '<table class="table table-condensed table-bordered"><thead><tr><th>Name</th><th>Description</th><th>Action</th></tr></thead><tbody>';
            brands.forEach(function(brand, index) {
                html += '<tr><td>' + brand.name + '</td><td>' + (brand.description || '-') + '</td><td><button type="button" class="btn btn-danger btn-xs remove-brand" data-index="' + index + '"><i class="fa fa-trash"></i></button></td></tr>';
            });
            html += '</tbody></table>';
            container.html(html);
        }
        $('#brands_data').val(JSON.stringify(brands));
    }

    $('#add_brand').click(function() {
        $('#brand_name').val('');
        $('#brand_description').val('');
        $('#brand_modal').modal('show');
    });

    $('#save_brand').click(function() {
        var name = $('#brand_name').val().trim();
        if (!name) { toastr.error('Name is required'); return; }
        brands.push({ name: name, description: $('#brand_description').val().trim() });
        renderBrands();
        $('#brand_modal').modal('hide');
    });

    $(document).on('click', '.remove-brand', function() {
        brands.splice($(this).data('index'), 1);
        renderBrands();
    });

    updatePreview();
});
</script>
@endsection
