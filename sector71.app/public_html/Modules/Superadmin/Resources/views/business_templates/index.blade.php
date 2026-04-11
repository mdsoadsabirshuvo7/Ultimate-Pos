@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.business_templates'))

@section('content')
@include('superadmin::layouts.nav')
<section class="content-header">
    <h1>@lang('superadmin::lang.business_templates') <small>@lang('superadmin::lang.manage_business_templates')</small></h1>
</section>

<section class="content">
    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">&nbsp;</h3>
            <div class="box-tools">
                <a href="{{action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, 'create'])}}" 
                    class="btn btn-block btn-primary">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </a>
            </div>
        </div>

        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="business_templates_table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.preview')</th>
                            <th>@lang('lang_v1.name')</th>
                            <th>@lang('lang_v1.description')</th>
                            <th>@lang('category.categories')</th>
                            <th>@lang('unit.units')</th>
                            <th>@lang('tax_rate.tax_rates')</th>
                            <th>@lang('sale.products')</th>
                            <th>@lang('lang_v1.status')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title">@lang('superadmin::lang.template_preview')</h3>
        </div>
        <div class="box-body">
            <div class="row" id="template_cards">
                @php
                    $templates = \Modules\Superadmin\Entities\BusinessTemplate::active()->orderBy('sort_order')->get();
                @endphp
                @forelse($templates as $template)
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="template-card hvr-grow-shadow" 
                         style="background-color: {{$template->color}}; color: white; padding: 20px; margin-bottom: 15px; border-radius: 8px; text-align: center; cursor: pointer;">
                        @if($template->icon)
                            <i class="{{$template->icon}}" style="font-size: 30px; margin-bottom: 10px;"></i><br>
                        @endif
                        <strong style="font-size: 14px;">{{$template->name}}</strong>
                        <div style="font-size: 11px; margin-top: 5px; opacity: 0.9;">
                            {{$template->categories_count}} Categories | {{$template->products_count}} Products
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-md-12">
                    <p class="text-center text-muted">@lang('superadmin::lang.no_templates_found')</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('messages.delete')</h4>
            </div>
            <div class="modal-body">
                <p>@lang('messages.delete_confirm')</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
                <button type="button" class="btn btn-danger" id="confirm_delete">@lang('messages.delete')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var templates_table = $('#business_templates_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{action([\Modules\Superadmin\Http\Controllers\BusinessTemplateController::class, "index"])}}',
        columns: [
            {data: 'preview', name: 'preview', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'description', name: 'description'},
            {data: 'categories_count', name: 'categories_count', orderable: false, searchable: false},
            {data: 'units_count', name: 'units_count', orderable: false, searchable: false},
            {data: 'tax_rates_count', name: 'tax_rates_count', orderable: false, searchable: false},
            {data: 'products_count', name: 'products_count', orderable: false, searchable: false},
            {data: 'is_active', name: 'is_active'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    var delete_url = '';
    $(document).on('click', '.delete_template', function() {
        delete_url = $(this).data('href');
        $('#delete_modal').modal('show');
    });

    $('#confirm_delete').click(function() {
        $.ajax({
            url: delete_url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    templates_table.ajax.reload();
                    location.reload();
                } else {
                    toastr.error(result.msg);
                }
                $('#delete_modal').modal('hide');
            },
            error: function() {
                toastr.error('Unable to delete template right now. Please try again.');
                $('#delete_modal').modal('hide');
            }
        });
    });
});
</script>
@endsection
