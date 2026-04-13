<?php

use App\Http\Controllers\Admin\CatalogRequestController as AdminCatalogRequestController;
use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\CatalogPageController;
use App\Http\Controllers\Admin\ClientCategoryController as AdminClientCategoryController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\ClientsPageController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\ContactPageSettingController;
use App\Http\Controllers\Admin\ContactRequestController as AdminContactRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FooterSettingController;
use App\Http\Controllers\Admin\HomeAboutPageController;
use App\Http\Controllers\Admin\HomeHeroSlidePageController;
use App\Http\Controllers\Admin\HomeRepresentativesPageController;
use App\Http\Controllers\Admin\NovedadesPageController;
use App\Http\Controllers\Admin\RepresentadasPageController;
use App\Http\Controllers\Admin\HomeHeroSlideController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\NewsletterCampaignController;
use App\Http\Controllers\Admin\NewsletterPageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Admin\WhatsAppPageController;
use App\Http\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Admin\PostCategoryController as AdminPostCategoryController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\ProductPageController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductFamilyController;
use App\Http\Controllers\Admin\ProductSubfamilyController;
use App\Http\Controllers\Admin\QualityPageController;
use App\Http\Controllers\Admin\QuotePageController;
use App\Http\Controllers\Admin\QuoteRequestController as AdminQuoteRequestController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SiteSectionController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SocialLinksPageController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProtectedMediaController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Controllers\Web\NewsletterController;
use App\Http\Controllers\Web\PartnerController;
use App\Http\Controllers\Web\ProductCatalogController;
use App\Http\Controllers\Web\QualityController;
use App\Http\Controllers\Web\QuoteController;
use App\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('web.home');
Route::get('/inicio', function () {
    return redirect('/');
});

Route::get('/dashboard', function () {
    return redirect('/admin/dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/media/{path}', [ProtectedMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/nosotros', [AboutController::class, 'show'])->name('web.about');
Route::get('/productos', [ProductCatalogController::class, 'index'])->name('web.products.index');
Route::get('/productos/todos-los-productos', [ProductCatalogController::class, 'all'])->name('web.products.all');
Route::get('/productos/{familySlug}', [ProductCatalogController::class, 'family'])->name('web.products.family');
Route::get('/productos/{familySlug}/{productSlug}/ficha-tecnica', [ProductCatalogController::class, 'technicalSheet'])->name('web.products.technical-sheet');
Route::get('/productos/{familySlug}/{productSlug}', [ProductCatalogController::class, 'show'])->name('web.products.show');
Route::get('/calidad', [QualityController::class, 'show'])->name('web.quality');
Route::get('/novedades', [NewsController::class, 'index'])->name('web.news.index');
Route::get('/novedades/{slug}', [NewsController::class, 'show'])->name('web.news.show');
Route::get('/catalogo', [CatalogController::class, 'show'])->name('web.catalog.show');
Route::post('/catalogo', [CatalogController::class, 'store'])
    ->middleware(['throttle:5,1', 'public.form.protection'])
    ->name('web.catalog.store');
Route::get('/representadas', [PartnerController::class, 'index'])->name('web.partners.index');
Route::get('/clientes', [ClientController::class, 'index'])->name('web.clients.index');
Route::get('/presupuesto', [QuoteController::class, 'show'])->name('web.quote.show');
Route::post('/presupuesto', [QuoteController::class, 'store'])
    ->middleware(['throttle:5,1', 'public.form.protection'])
    ->name('web.quote.store');
Route::get('/contacto', [ContactController::class, 'show'])->name('web.contact.show');
Route::post('/contacto', [ContactController::class, 'store'])
    ->middleware(['throttle:5,1', 'public.form.protection'])
    ->name('web.contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->middleware(['throttle:10,1', 'public.form.protection'])
    ->name('web.newsletter.store');
Route::get('/buscar', [SearchController::class, 'index'])->name('web.search.index');
Route::get('/buscar/sugerencias', [SearchController::class, 'suggest'])->name('web.search.suggest');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['auth', 'admin.access'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/about', [AboutPageController::class, 'index'])->name('about.index');
        Route::get('/catalog', [CatalogPageController::class, 'index'])->name('catalog.index');
        Route::get('/clients', [ClientsPageController::class, 'index'])->name('clients.index');
        Route::get('/products', [ProductPageController::class, 'index'])->name('products.index');
        Route::get('/quality', [QualityPageController::class, 'index'])->name('quality.index');
        Route::get('/quote', [QuotePageController::class, 'index'])->name('quote.index');
        Route::get('/products/template', [ProductImportController::class, 'template'])->name('products.template');
        Route::get('/home/sliders', [HomeHeroSlidePageController::class, 'index'])->name('home-sliders.index');
        Route::get('/home/about', [HomeAboutPageController::class, 'index'])->name('home-about.index');
        Route::get('/home/representatives', [HomeRepresentativesPageController::class, 'index'])->name('home-representatives.index');
        Route::get('/representadas', [RepresentadasPageController::class, 'index'])->name('representadas.index');
        Route::get('/novedades', [NovedadesPageController::class, 'index'])->name('novedades.index');
        Route::get('/novedades/create', [NovedadesPageController::class, 'create'])->name('novedades.create');
        Route::get('/novedades/{id}/edit', [NovedadesPageController::class, 'edit'])->name('novedades.edit');
        Route::get('/contact', [AdminContactController::class, 'index'])->name('contact.index');
        Route::get('/whatsapp', [WhatsAppPageController::class, 'index'])->name('whatsapp.index');
        Route::get('/social-links', [SocialLinksPageController::class, 'index'])->name('social-links.index');
        Route::get('/newsletter', [NewsletterPageController::class, 'index'])->name('newsletter.index');
        Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
        Route::post('/seo', [SeoController::class, 'upsert'])->name('seo.upsert');
        Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
        Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
        Route::get('/contacto/settings', [AdminContactController::class, 'settingsPage'])->name('contact.settings');
        Route::get('/contacto/requests', [AdminContactController::class, 'requestsPage'])->name('contact.requests');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/footer-settings', [FooterSettingController::class, 'show'])->name('footer-settings.show');
            Route::put('/footer-settings', [FooterSettingController::class, 'update'])->name('footer-settings.update');

            Route::get('/contact-page-settings', [ContactPageSettingController::class, 'show'])->name('contact-page-settings.show');
            Route::put('/contact-page-settings', [ContactPageSettingController::class, 'update'])->name('contact-page-settings.update');

            Route::apiResource('media-assets', MediaAssetController::class);
            Route::apiResource('site-sections', SiteSectionController::class);
            Route::apiResource('home-hero-slides', HomeHeroSlideController::class);
            Route::apiResource('product-families', ProductFamilyController::class);
            Route::apiResource('product-subfamilies', ProductSubfamilyController::class);
            Route::apiResource('products', AdminProductController::class);
            Route::post('/product-import', [ProductImportController::class, 'import'])->name('product-import.store');
            Route::apiResource('partners', AdminPartnerController::class);
            Route::apiResource('client-categories', AdminClientCategoryController::class);
            Route::apiResource('clients', AdminClientController::class);
            Route::apiResource('post-categories', AdminPostCategoryController::class);
            Route::apiResource('posts', AdminPostController::class);
            Route::apiResource('social-links', SocialLinkController::class);
            Route::apiResource('newsletter-subscribers', AdminNewsletterSubscriberController::class)->except(['create', 'edit']);
            Route::post('/newsletter/send', [NewsletterCampaignController::class, 'send'])->name('newsletter.send');
            Route::apiResource('catalog-requests', AdminCatalogRequestController::class)->except(['create', 'edit']);
            Route::apiResource('contact-requests', AdminContactRequestController::class)->except(['create', 'edit']);
            Route::get('/quote-requests', [AdminQuoteRequestController::class, 'index'])->name('quote-requests.index');
            Route::get('/quote-requests/{id}', [AdminQuoteRequestController::class, 'show'])->name('quote-requests.show');
            Route::put('/quote-requests/{id}', [AdminQuoteRequestController::class, 'update'])->name('quote-requests.update');
            Route::delete('/quote-requests/{id}', [AdminQuoteRequestController::class, 'destroy'])->name('quote-requests.destroy');
        });
    });
});

require __DIR__.'/auth.php';
