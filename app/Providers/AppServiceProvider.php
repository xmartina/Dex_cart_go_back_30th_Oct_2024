<?php

namespace App\Providers;

use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use App\Contracts\PaymentServiceContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Cookie\Middleware\EncryptCookies;
// use Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (
            isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        ) {
            URL::forceScheme('https');
        }

        // Disable lazy loading to avoid n+1 problem (except on production server)
        // Model::preventLazyLoading(!$this->app->isProduction());

        Blade::withoutDoubleEncoding();
        Paginator::useBootstrapThree();
        // Artisan::call('dump-autoload');

        // Add Google recaptcha validation rule
        Validator::extend('recaptcha', 'App\\Helpers\\ReCaptcha@validate');

        // Disable encryption for gdpr cookie
        $this->app->resolving(EncryptCookies::class, function (EncryptCookies $encryptCookies) {
            $encryptCookies->disableFor(config('gdpr.cookie.name'));
        });

        // Add pagination on collections
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
                $q = url()->full();
                // Remove unwanted page parameter from the url if exist
                if (Request::has('page')) {
                    $q = remove_url_parameter($q, 'page');
                }

                $page = $page ?? Paginator::resolveCurrentPage() ?? 1;

                $paginator = new LengthAwarePaginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, $options);

                return $paginator->withPath($q);
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Need for cashier
        Cashier::ignoreMigrations();
        Cashier::useCustomerModel('App\\Models\\Shop');

        //Payment method binding for wallet deposit
        if (Request::has('payment_method')) {
            $className = $this->resolvePaymentDependency(Request::get('payment_method'));
            $this->app->bind(PaymentServiceContract::class, $className);
        }

        // Ondemand Img manupulation
        $this->app->singleton(
            \League\Glide\Server::class,
            function ($app) {
                $filesystem = $app->make(Filesystem::class);

                return \League\Glide\ServerFactory::create([
                    'response' => new \League\Glide\Responses\LaravelResponseFactory(app('request')),
                    'driver' => config('image.driver'),
                    'presets' => config('image.sizes'),
                    'source' => $filesystem->getDriver(),
                    'cache' => $filesystem->getDriver(),
                    'cache_path_prefix' => config('image.cache_dir'),
                    'base_url' => 'image', //Don't change this value
                ]);
            }
        );
    }

    private function resolvePaymentDependency($class_name)
    {
        switch ($class_name) {
            case 'stripe':
            case 'saved_card':
                return \App\Services\Payments\StripePaymentService::class;

            case 'instamojo':
                return \Incevio\Package\Instamojo\Services\InstamojoPaymentService::class;

            case 'authorizenet':
                return \Incevio\Package\AuthorizeNet\Services\AuthorizeNetPaymentService::class;

            case 'cybersource':
                return \App\Services\Payments\CybersourcePaymentService::class;

            case 'paystack':
                return \Incevio\Package\Paystack\Services\PaystackPaymentService::class;

            case 'paypal':
                return \App\Services\Payments\PaypalPaymentService::class;

            case 'iyzico':
                return \Incevio\Package\Iyzico\Services\IyzicoPaymentService::class;

            case 'paypal-marketplace':
                return \Incevio\Package\PaypalMarketplace\Services\PaypalMarketplacePaymentService::class;

            case 'wire':
                return \App\Services\Payments\WirePaymentService::class;

            case 'cod':
                return \App\Services\Payments\CodPaymentService::class;

            case 'pip':
                return \App\Services\Payments\PipPaymentService::class;

            case 'zcart-wallet':
                return \Incevio\Package\Wallet\Services\WalletPaymentService::class;

            case 'razorpay':
                return \Incevio\Package\Razorpay\Services\RazorpayPaymentService::class;

            case 'sslcommerz':
                return \Incevio\Package\SslCommerz\Services\SslCommerzPaymentService::class;

            case 'flutterwave':
                return \Incevio\Package\FlutterWave\Services\FlutterWavePaymentService::class;

            case 'mpesa':
                return \Incevio\Package\MPesa\Services\MPesaPaymentService::class;

            case 'payfast':
                return \Incevio\Package\Payfast\Services\PayfastPaymentService::class;

            case 'mercado-pago':
                return \Incevio\Package\MercadoPago\Services\MercadoPagoPaymentService::class;

            case 'orangemoney':
                return \Incevio\Package\OrangeMoney\Services\OrangeMoneyPaymentService::class;

            case 'mollie':
                return \Incevio\Package\Mollie\Services\MolliePaymentService::class;

            case 'bkash':
                return \Incevio\Package\Bkash\Services\BkashPaymentService::class;

            case 'paytm':
                return \Incevio\Package\Paytm\Services\PaytmPaymentService::class;
        }

        throw new \ErrorException("Error: Payment Method {$class_name} Not Found.");
    }
}
