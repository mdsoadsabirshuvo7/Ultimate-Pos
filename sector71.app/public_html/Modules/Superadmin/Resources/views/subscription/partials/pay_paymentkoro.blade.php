<div class="col-md-12">
    <a href="{{ action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'paymentKoroCheckout'], [$package->id]) }}"
       class="btn btn-sm text-white"
       style="background: #1A9C5C; border-color: #1A9C5C;">
        <i class="fas fa-credit-card text-white"></i>
        {{$v}}
    </a>
</div>
