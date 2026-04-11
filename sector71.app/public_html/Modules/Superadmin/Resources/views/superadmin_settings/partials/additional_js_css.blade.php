<div class="pos-tab-content">
    <div class="row">
    	<div class="col-xs-12">
            <div class="form-group">
            	{!! Form::label('additional_js', __('superadmin::lang.additional_js') . ':') !!} @show_tooltip(__('superadmin::lang.additional_js_instructions'))
            	{!! Form::textarea('additional_js', !empty($settings['additional_js']) ? $settings['additional_js'] : '', ['class' => 'form-control','placeholder' => __('superadmin::lang.additional_js')]); !!}
            </div>
        </div>
        <div class="col-xs-12">
            <div class="form-group">
            	{!! Form::label('additional_css', __('superadmin::lang.additional_css') . ':') !!} @show_tooltip(__('superadmin::lang.additional_css_instructions'))
            	{!! Form::textarea('additional_css', !empty($settings['additional_css']) ? $settings['additional_css'] : '', ['class' => 'form-control','placeholder' => __('superadmin::lang.additional_css')]); !!}
            </div>
                <div class="col-xs-12">
            <div class="form-group">
            {!! Form::label('facebook_pixel', 'Facebook Pixel Code:') !!} @show_tooltip('Enter your Facebook Pixel code here. It will be added to the head section of all pages.')
            {!! Form::textarea('facebook_pixel', !empty($settings['facebook_pixel']) ? $settings['facebook_pixel'] : '', ['class' => 'form-control', 'placeholder' => '<!-- Meta Pixel Code -->']); !!}
            </div>
        </div>
            <div class="col-xs-12">
            <div class="form-group">
            {!! Form::label('google_analytics_id', 'Google Analytics 4 Measurement ID:') !!} @show_tooltip('Example: G-XXXXXXXXXX')
            {!! Form::text('google_analytics_id', !empty($settings['google_analytics_id']) ? $settings['google_analytics_id'] : '', ['class' => 'form-control', 'placeholder' => 'G-XXXXXXXXXX']); !!}
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group">
            {!! Form::label('whatsapp_number', 'Floating WhatsApp Number:') !!} @show_tooltip('Format: 8801XXXXXXXXX')
            {!! Form::text('whatsapp_number', !empty($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '', ['class' => 'form-control', 'placeholder' => '8801XXXXXXXXX']); !!}
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group">
            {!! Form::label('whatsapp_greeting', 'WhatsApp Greeting Message:') !!} @show_tooltip('Pre-filled message when they open WhatsApp')
            {!! Form::text('whatsapp_greeting', !empty($settings['whatsapp_greeting']) ? $settings['whatsapp_greeting'] : '', ['class' => 'form-control', 'placeholder' => 'Hello! I need help with...']); !!}
            </div>
        </div>
    </div>
</div>
</div>
