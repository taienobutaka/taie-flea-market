<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\ImageHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ImageHelperをビューで使用できるように登録
        Blade::directive('imageUrl', function ($expression) {
            return "<?php echo App\Helpers\ImageHelper::getImageUrl($expression); ?>";
        });
    }
}
