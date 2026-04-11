@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Business')

@section('content')
@include('superadmin::layouts.nav')
<!-- Main content -->
<section class="content">

    <!-- Business Template Selector -->
    <div class="box box-solid box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-magic"></i> @lang('superadmin::lang.select_business_template')</h3>
        </div>
        <div class="box-body">
            <p class="text-muted">@lang('superadmin::lang.select_template_help')</p>
            <div class="row" id="template_selector">
                @php
                    $templates = \Modules\Superadmin\Entities\BusinessTemplate::active()->orderBy('sort_order')->get();
                @endphp
                <div class="col-md-2 col-sm-3 col-xs-4">
                    <div class="template-option selected" data-template-id="" style="padding: 15px; margin-bottom: 10px; border: 2px solid #3c8dbc; border-radius: 8px; text-align: center; cursor: pointer; background-color: #f4f4f4;">
                        <i class="fa fa-ban" style="font-size: 24px; color: #999; margin-bottom: 8px;"></i><br>
                        <strong>None</strong>
                        <div style="font-size: 10px; color: #999;">Start Fresh</div>
                    </div>
                </div>
                @foreach($templates as $template)
                <div class="col-md-2 col-sm-3 col-xs-4">
                    <div class="template-option" data-template-id="{{$template->id}}" 
                         style="padding: 15px; margin-bottom: 10px; border: 2px solid #ddd; border-radius: 8px; text-align: center; cursor: pointer; background-color: {{$template->color}}; color: white;">
                        @if($template->icon)
                            <i class="{{$template->icon}}" style="font-size: 24px; margin-bottom: 8px;"></i><br>
                        @endif
                        <strong>{{$template->name}}</strong>
                        <div style="font-size: 10px; opacity: 0.9;">{{$template->categories_count}} Categories</div>
                    </div>
                </div>
                @endforeach
            </div>
            <input type="hidden" name="business_template_id" id="business_template_id" value="">
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">@lang( 'superadmin::lang.add_new_business' ) <small>(@lang( 'superadmin::lang.add_business_help' ))</small></h3>
        </div>

        <div class="box-body">
            {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\BusinessController::class, 'store']), 'method' => 'post', 'id' => 'business_register_form','files' => true ]) !!}
                <input type="hidden" name="business_template_id" id="template_hidden_input" value="">
                @include('business.partials.register_form')
                <div class="clearfix"></div>
                <div class="col-md-12"><hr></div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('package_id', __( 'superadmin::lang.subscription_packages' ) . ':') !!}
                        {!! Form::select('package_id', $packages, null, ['class' => 'form-control', 'placeholder' => __( 'messages.please_select' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('paid_via', __( 'superadmin::lang.paid_via' ) . ':') !!}
                        {!! Form::select('paid_via', $gateways, null, ['class' => 'form-control', 'placeholder' => __( 'messages.please_select' ) ]); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('payment_transaction_id', __( 'superadmin::lang.payment_transaction_id' ) . ':') !!}
                        {!! Form::text('payment_transaction_id', null, ['class' => 'form-control', 'placeholder' => __( 'superadmin::lang.payment_transaction_id' ) ]); !!}
                    </div>
                </div>

                {!! Form::submit(__('messages.submit'), ['class' => 'btn btn-success pull-right']) !!}
            {!! Form::close() !!}
        </div>
    </div>

    <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@endsection


@section('javascript')
<script type="text/javascript">
    $(document).ready(function(){
        // Template Selection
        $('.template-option').click(function(){
            $('.template-option').removeClass('selected').css('border-color', '#ddd');
            $(this).addClass('selected').css('border-color', '#3c8dbc');
            var templateId = $(this).data('template-id');
            $('#template_hidden_input').val(templateId);
            
            // Update first option styling  
            if(templateId === '') {
                $(this).css('border-color', '#3c8dbc');
            }
        });
        
        $('.select2_register').select2();
        $("form#business_register_form").validate({
            errorPlacement: function(error, element) {
                if(element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                name: "required",
                email: {
                    email: true,
                    remote: {
                        url: "/business/register/check-email",
                        type: "post",
                        data: {
                            email: function() {
                                return $( "#email" ).val();
                            }
                        }
                    }
                },
                password: {
                    required: true,
                    minlength: 5
                },
                confirm_password: {
                    equalTo: "#password"
                },
                paid_via: {
                    required: function(element){
                        return $('#package_id').val() != '';
                    }
                },
                username: {
                    required: true,
                    minlength: 4,
                    remote: {
                        url: "/business/register/check-username",
                        type: "post",
                        data: {
                            username: function() {
                                return $( "#username" ).val();
                            }
                        }
                    }
                }
            },
            messages: {
                name: LANG.specify_business_name,
                password: {
                    minlength: LANG.password_min_length,
                },
                confirm_password: {
                    equalTo: LANG.password_mismatch
                },
                username: {
                    remote: LANG.invalid_username
                },
                email: {
                    remote: '{{ __("validation.unique", ["attribute" => __("business.email")]) }}'
                }
            }
        });

        $("#business_logo").fileinput({'showUpload':false, 'showPreview':false, 'browseLabel': LANG.file_browse_label, 'removeLabel': LANG.remove});
    });
</script>
<style>
    .template-option:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .template-option.selected {
        border-color: #3c8dbc !important;
        box-shadow: 0 0 10px rgba(60,141,188,0.5);
    }
</style>
@endsection
