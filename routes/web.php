<?php

use App\Http\Controllers\Frontend\ArticleController;
use App\Http\Controllers\Frontend\GuidelinesController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Admin\SubmitArticleController;
use App\Http\Controllers\Frontend\MenuController as FrontendMenuController;
use App\Http\Controllers\Frontend\EditorialBoardController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\ForgotPasswordOtpController;
use App\Http\Controllers\Frontend\ArchiveController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\CurrentIssuesController;
use App\Http\Controllers\Frontend\JournalDetailController;
use Illuminate\Support\Facades\Route;

// ── Frontend Routes ───────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/guidelines', [GuidelinesController::class, 'index'])->name('guidelines');
Route::get('/contact', [ContactController::class, 'index'])->name('contacts');
Route::get('/submit-article', [SubmitArticleController::class, 'index'])->name('submitarticles');
Route::get('/menus/{location}', [FrontendMenuController::class, 'byLocation'])->name('menus.byLocation');
Route::get('/editorial-board', [EditorialBoardController::class, 'index'])->name('editorial-board');


Route::post('/password/send-otp', [ForgotPasswordOtpController::class, 'sendOtp'])->name('password.send-otp');
Route::post('/password/reset-otp', [ForgotPasswordOtpController::class, 'resetWithOtp'])->name('password.reset-otp');
Route::post('/password/verify-otp', [ForgotPasswordOtpController::class, 'verifyOtp'])->name('password.verify-otp');

// ── Auth Routes ───────────────────────────────────────────────────
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/{journal}', [JournalDetailController::class, 'show'])->name('journal-details');
Route::get('/archives/{journal}', [ArchiveController::class, 'show'])->name('archives');
Route::get('/article/{article}', [ArticleController::class, 'show'])->name('articles');
Route::get('/article/{uuid}/download-manuscript', [ArticleController::class, 'downloadManuscript'])->name('article.download-manuscript');
Route::get('/current-issues/{issue?}', [CurrentIssuesController::class, 'index'])->name('current-issues');
Route::get('/editorial-board/{journal}', [EditorialBoardController::class, 'index'])->name('editorial-board');


Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// ── Protected Admin Routes ────────────────────────────────────────
Route::middleware('jwt.web')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');

    Route::get('/users', fn() => view('admin.users'))->middleware('permission:view users')->name('users');
    Route::get('/roles', fn() => view('admin.roles'))->middleware('permission:view roles')->name('roles');
    Route::get('/permissions', fn() => view('admin.permissions'))->middleware('permission:view permissions')->name('permissions');
    Route::get('/journals', fn() => view('admin.journals'))->middleware('permission:view journals')->name('journals');
    Route::get('/volumes', fn() => view('admin.volume'))->middleware('permission:view volumes')->name('volume');
    Route::get('/issues', fn() => view('admin.issue'))->middleware('permission:view issues')->name('issue');
    Route::get('/menus', fn() => view('admin.menus'))->middleware('permission:view menus')->name('menus');
    Route::get('/medias', fn() => view('admin.medias'))->middleware('permission:view medias')->name('medias');
    Route::get('/settings', fn() => view('admin.settings'))->middleware('permission:view settings')->name('settings');
    Route::get('/announcements', fn() => view('admin.announcements'))->middleware('permission:view announcements')->name('announcements');
    Route::get('/contacts', fn() => view('admin.contact'))->middleware('permission:view contacts')->name('contact');
    Route::get('/aboutcontent', fn() => view('admin.aboutcontent'))->middleware('permission:view about')->name('aboutcontent');
    Route::get('/guidelines', fn() => view('admin.guidelines'))->middleware('permission:view guidelines')->name('guidelines');
    Route::get('/homebasiccontent', fn() => view('admin.homebasiccontent'))->middleware('permission:view home content')->name('homebasiccontent');
    Route::get('/editorial-board', fn() => view('admin.editorialboard'))->middleware('permission:view editorial board')->name('editorial-board');
    Route::get('/all-article-lists', fn() => view('admin.submitarticle'))->middleware('permission:view submit article')->name('submit-article');


    Route::prefix('submit-articles')->name('submit-articles.')->group(function () {

        Route::get('/', function () {
            return view('admin.submitarticle');
        })->name('index');


        Route::get('/create', function () {
            return view('admin.addarticles');
        })->name('create');

        Route::get('/{uuid}', function ($id) {
            return view('admin.showarticles', ['id' => $id]);
        })->name('showarticles');

        Route::get('/{uuid}/edit', function ($id) {
            return view('admin.editarticles', ['id' => $id]);
        })->name('editarticles');

        Route::get('/{uuid}/reject', function ($id) {
            return view('admin.rejectarticles', ['id' => $id]);
        })->name('rejectarticles');

        Route::get('/{uuid}/review-decision', function ($id) {
            return view('admin.reviewarticles', ['id' => $id]);
        })->name('review-decision');

        Route::get('/{uuid}/final-decision', function ($id) {
            return view('admin.finaldecisionarticles', ['id' => $id]);
        })->name('final-decision');

        Route::get('/{uuid}/forward-revision', function ($id) {
            return view('admin.forwardrevisionarticles', ['id' => $id]);
        })->name('forward-revision');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});