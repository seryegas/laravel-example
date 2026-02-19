<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Core\Providers\CoreServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Service\Providers\ServiceModuleProvider::class,
    App\Modules\Booking\Providers\BookingServiceProvider::class,
    App\Modules\Payment\Providers\PaymentServiceProvider::class,
    App\Modules\Notification\Providers\NotificationServiceProvider::class,
];
