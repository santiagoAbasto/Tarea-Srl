<?php

use Illuminate\Support\Facades\Route;

// ─── Web Controllers ──────────────────────────────────────────────────────────
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NosotrosController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\EquipmentController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\NormasController;
use App\Http\Controllers\Web\ClientsController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\QuoteController;
use App\Http\Controllers\ProtectedMediaController;

// ─── Admin Controllers ────────────────────────────────────────────────────────
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CmsPageEditorController;
use App\Http\Controllers\Admin\CmsSectionEditorController;
use App\Http\Controllers\Admin\ServiceController       as AdminServiceController;
use App\Http\Controllers\Admin\EquipmentCategoryController;
use App\Http\Controllers\Admin\EquipmentController     as AdminEquipmentController;
use App\Http\Controllers\Admin\EquipmentImageController;
use App\Http\Controllers\Admin\ProjectController       as AdminProjectController;
use App\Http\Controllers\Admin\ProjectImageController;
use App\Http\Controllers\Admin\ClientTypeController;
use App\Http\Controllers\Admin\ContactAdminController;
use App\Http\Controllers\Admin\QuoteAdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\PageSectionMediaController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| WEB PÚBLICA (BLADE)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/nosotros', [NosotrosController::class, 'index'])->name('nosotros');

Route::get('/servicios', [ServiceController::class, 'index'])->name('servicios.index');
Route::get('/servicios/{slug}', [ServiceController::class, 'show'])->name('servicios.show');

Route::get('/equipos', [EquipmentController::class, 'index'])->name('equipos.index');
Route::get('/equipos/{slug}', [EquipmentController::class, 'show'])->name('equipos.show');

Route::get('/trabajos-realizados', [ProjectController::class, 'index'])->name('proyectos.index');
Route::get('/trabajos-realizados/{id}', [ProjectController::class, 'show'])->name('proyectos.show');
Route::get('/buscar', [SearchController::class, 'index'])->name('search');
Route::get('/media/{path}', [ProtectedMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::get('/normas', [NormasController::class, 'index'])->name('normas');

Route::get('/clientes', [ClientsController::class, 'index'])->name('clientes');

Route::get('/contacto', [ContactController::class, 'index'])->name('contacto');
Route::post('/contacto', [ContactController::class, 'store'])
    ->middleware(['throttle:5,1', 'public.form.protection'])
    ->name('contacto.store');

Route::get('/presupuesto', [QuoteController::class, 'index'])->name('presupuesto');
Route::post('/presupuesto', [QuoteController::class, 'store'])
    ->middleware(['throttle:5,1', 'public.form.protection'])
    ->name('presupuesto.store');

/*
|--------------------------------------------------------------------------
| ADMIN PANEL (INERTIA + REACT)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware(['auth', 'verified', 'admin.access'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/cms/{slug}/{editor}', [CmsSectionEditorController::class, 'edit'])->name('cms.section');
        Route::get('/cms/{slug}', [CmsPageEditorController::class, 'edit'])->name('cms.edit');

        // Profile
        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Services
        Route::get('/services',                          [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create',                   [AdminServiceController::class, 'create'])->name('services.create');
        Route::post('/services',                         [AdminServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit',           [AdminServiceController::class, 'edit'])->name('services.edit');
        Route::post('/services/{service}',               [AdminServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}',             [AdminServiceController::class, 'destroy'])->name('services.destroy');
        Route::patch('/services/{service}/toggle-home',  [AdminServiceController::class, 'toggleHome'])->name('services.toggle-home');

        // Equipment Categories
        Route::get('/equipment-categories',                        [EquipmentCategoryController::class, 'index'])->name('equipment-categories.index');
        Route::get('/equipment-categories/{equipmentCategory}/edit', [EquipmentCategoryController::class, 'edit'])->name('equipment-categories.edit');
        Route::post('/equipment-categories',                       [EquipmentCategoryController::class, 'store'])->name('equipment-categories.store');
        Route::post('/equipment-categories/{equipmentCategory}',   [EquipmentCategoryController::class, 'update'])->name('equipment-categories.update');
        Route::delete('/equipment-categories/{equipmentCategory}', [EquipmentCategoryController::class, 'destroy'])->name('equipment-categories.destroy');

        // Equipments
        Route::get('/equipments',                           [AdminEquipmentController::class, 'index'])->name('equipments.index');
        Route::get('/equipments/create',                    [AdminEquipmentController::class, 'create'])->name('equipments.create');
        Route::post('/equipments',                          [AdminEquipmentController::class, 'store'])->name('equipments.store');
        Route::get('/equipments/{equipment}/edit',          [AdminEquipmentController::class, 'edit'])->name('equipments.edit');
        Route::post('/equipments/{equipment}',              [AdminEquipmentController::class, 'update'])->name('equipments.update');
        Route::delete('/equipments/{equipment}',            [AdminEquipmentController::class, 'destroy'])->name('equipments.destroy');
        Route::patch('/equipments/{equipment}/toggle-home', [AdminEquipmentController::class, 'toggleHome'])->name('equipments.toggle-home');
        Route::post('/equipments/{equipment}/images',       [EquipmentImageController::class, 'store'])->name('equipment-images.store');
        Route::delete('/equipment-images/{equipmentImage}', [EquipmentImageController::class, 'destroy'])->name('equipment-images.destroy');

        // Projects
        Route::get('/projects',                          [AdminProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create',                   [AdminProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects',                         [AdminProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}/edit',           [AdminProjectController::class, 'edit'])->name('projects.edit');
        Route::post('/projects/{project}',               [AdminProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}',             [AdminProjectController::class, 'destroy'])->name('projects.destroy');
        Route::patch('/projects/{project}/toggle-home',  [AdminProjectController::class, 'toggleHome'])->name('projects.toggle-home');
        Route::post('/projects/{project}/images',        [ProjectImageController::class, 'store'])->name('project-images.store');
        Route::delete('/project-images/{projectImage}',  [ProjectImageController::class, 'destroy'])->name('project-images.destroy');

        // Client Types
        Route::get('/client-types',                      [ClientTypeController::class, 'index'])->name('client-types.index');
        Route::post('/client-types',                     [ClientTypeController::class, 'store'])->name('client-types.store');
        Route::post('/client-types/{clientType}',        [ClientTypeController::class, 'update'])->name('client-types.update');
        Route::delete('/client-types/{clientType}',      [ClientTypeController::class, 'destroy'])->name('client-types.destroy');

        // Contacts
        Route::get('/contacts',              [ContactAdminController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}',    [ContactAdminController::class, 'show'])->name('contacts.show');
        Route::delete('/contacts/{contact}', [ContactAdminController::class, 'destroy'])->name('contacts.destroy');

        // Quotes
        Route::get('/quotes',            [QuoteAdminController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/{quote}',    [QuoteAdminController::class, 'show'])->name('quotes.show');
        Route::delete('/quotes/{quote}', [QuoteAdminController::class, 'destroy'])->name('quotes.destroy');

        // Unified submissions CRM
        Route::get('/submissions', [FormSubmissionController::class, 'all'])->name('submissions.index');
        Route::get('/submissions/contacto', [FormSubmissionController::class, 'contact'])->name('submissions.contact');
        Route::get('/submissions/presupuesto', [FormSubmissionController::class, 'quote'])->name('submissions.quote');
        Route::get('/submissions/{submission}', [FormSubmissionController::class, 'show'])->name('submissions.show');
        Route::patch('/submissions/{submission}/read', [FormSubmissionController::class, 'markAsRead'])->name('submissions.read');
        Route::delete('/submissions/{submission}', [FormSubmissionController::class, 'destroy'])->name('submissions.destroy');

        // Pages / Sections / Media (CMS)
        Route::get('/pages',           [PageController::class, 'index'])->name('pages.index');
        Route::get('/pages/{page}',    [PageController::class, 'show'])->name('pages.show');
        Route::post('/pages',          [PageController::class, 'store'])->name('pages.store');
        Route::post('/pages/{page}',   [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

        Route::post('/pages/{page}/sections',    [PageSectionController::class, 'store'])->name('sections.store');
        Route::post('/sections/{section}',       [PageSectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{section}',     [PageSectionController::class, 'destroy'])->name('sections.destroy');

        Route::post('/sections/{section}/media', [PageSectionMediaController::class, 'store'])->name('section-media.store');
        Route::post('/section-media/{media}',    [PageSectionMediaController::class, 'update'])->name('section-media.update');
        Route::delete('/section-media/{media}',  [PageSectionMediaController::class, 'destroy'])->name('section-media.destroy');

        // SEO
        Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
        Route::post('/seo/{page}', [SeoController::class, 'update'])->name('seo.update');

        // Settings
        Route::get('/settings',  [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'upsert'])->name('settings.upsert');

        // Users
        Route::get('/users',           [UserAdminController::class, 'index'])->name('users.index');
        Route::post('/users',          [UserAdminController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',    [UserAdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');

    });
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (BREEZE)
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
