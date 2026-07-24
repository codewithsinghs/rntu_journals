        <?php

        use App\Http\Controllers\Admin\AboutBasicContentController;
        use App\Http\Controllers\Admin\AnnouncementController;
        use App\Http\Controllers\Admin\ContactController;
        use App\Http\Controllers\Admin\DashboardController;
        use App\Http\Controllers\Frontend\EditorialBoardController as FrontendEditorialBoardController;
        use App\Http\Controllers\Admin\EditorialBoardController as AdminEditorialBoardController;
        use App\Http\Controllers\Admin\GuidelinesController;
        use App\Http\Controllers\Frontend\AnnoncementsController;
        use App\Http\Controllers\Admin\HomeBasicContentController;
        use App\Http\Controllers\admin\IssueController;
        use App\Http\Controllers\Admin\JournalsController;
        use App\Http\Controllers\Admin\MediasController;
        use App\Http\Controllers\Admin\MenuController as AdminMenuController;
        use App\Http\Controllers\Admin\PermissionController;
        use App\Http\Controllers\Admin\RoleController;
        use App\Http\Controllers\Frontend\MenuController as FrontendMenuController;
        use App\Http\Controllers\Admin\SettingsController;
        use App\Http\Controllers\Admin\SubmitArticleController;
        use App\Http\Controllers\Admin\UserController;
        use App\Http\Controllers\admin\VolumeController;
        use App\Http\Controllers\Frontend\AboutController;
        use App\Http\Controllers\Frontend\HomeController;
        use Illuminate\Support\Facades\Route;


        // ── Public routes (no auth) ───────────────────────────────────────
        Route::get('/public/menus', [FrontendMenuController::class, 'index']);
        Route::get('/public/announcements', [AnnoncementsController::class, 'PublicIndex']);
        Route::get('/public/home-content', [HomeController::class, 'index']);
        Route::get('/public/about-content', [AboutController::class, 'index']);
        Route::get('/submit-article/journals', [SubmitArticleController::class, 'journals']);
        Route::post('/submit-article',         [SubmitArticleController::class, 'store']);
        Route::get('/public/editorial-board', [FrontendEditorialBoardController::class, 'index']);


        // ── Admin routes (jwt protected) ──────────────────────────────────
        Route::middleware('jwt')->prefix('admin')->name('admin.')->group(function () {
            // Menus
            Route::get('/menus',         [AdminMenuController::class, 'adminIndex'])->name('menus.data');
            Route::post('/menus',        [AdminMenuController::class, 'store'])->name('menus.store');
            Route::put('/menus/{id}',    [AdminMenuController::class, 'update'])->name('menus.update');
            Route::delete('/menus/{id}', [AdminMenuController::class, 'destroy'])->name('menus.destroy');

            // Medias
            Route::get('/medias',            [MediasController::class, 'index'])->name('medias.index');
            Route::post('/medias',           [MediasController::class, 'store'])->name('medias.store');
            Route::post('/medias/{media}',   [MediasController::class, 'update'])->name('medias.update');
            Route::delete('/medias/{media}', [MediasController::class, 'destroy'])->name('medias.destroy');

            // Settings
            Route::get('/settings',                    [SettingsController::class, 'show']);
            Route::put('/settings',                    [SettingsController::class, 'update']);
            Route::post('/settings/media/{key}',       [SettingsController::class, 'uploadMedia']);
            Route::delete('/settings/media/{key}',     [SettingsController::class, 'removeMedia']);

            // ─── Journal ────────────────────────────────────────────────────────────
            Route::get('/journals',                    [JournalsController::class, 'adminIndex'])->name('journals.data');
            Route::post('/journals',                   [JournalsController::class, 'store'])->name('journals.store');
            Route::get('/journals/{id}',                [JournalsController::class, 'show'])->name('journals.show');
            Route::post('/journals/{id}',              [JournalsController::class, 'update'])->name('journals.update.multipart');
            Route::put('/journals/{id}',               [JournalsController::class, 'update'])->name('journals.update');
            Route::delete('/journals/{id}',            [JournalsController::class, 'destroy'])->name('journals.destroy');
            Route::patch('/journals/{id}/toggle',      [JournalsController::class, 'toggleStatus'])->name('journals.toggle');

            // Announcements
            Route::get('/announcements',               [AnnouncementController::class, 'adminIndex']);
            Route::post('/announcements',              [AnnouncementController::class, 'store']);
            Route::post('/announcements/{id}',         [AnnouncementController::class, 'update']);
            Route::delete('/announcements/{id}',       [AnnouncementController::class, 'destroy']);

            // Home Content
            Route::get('/home-content',                [HomeBasicContentController::class, 'adminIndex']);
            Route::post('/home-content',               [HomeBasicContentController::class, 'store']);
            Route::get('/home-content/{id}',           [HomeBasicContentController::class, 'show']);
            Route::post('/home-content/{id}',          [HomeBasicContentController::class, 'update']);
            Route::put('/home-content/{id}',           [HomeBasicContentController::class, 'update']);
            Route::delete('/home-content/{id}',        [HomeBasicContentController::class, 'destroy']);

            // About Content
            Route::get('/about-content',               [AboutBasicContentController::class, 'adminIndex']);
            Route::post('/about-content',              [AboutBasicContentController::class, 'store']);
            Route::get('/about-content/{id}',          [AboutBasicContentController::class, 'show']);
            Route::post('/about-content/{id}',         [AboutBasicContentController::class, 'update']);
            Route::put('/about-content/{id}',          [AboutBasicContentController::class, 'update']);

            //guidelines    
            Route::get('/guidelines',               [GuidelinesController::class, 'adminIndex']);
            Route::post('/guidelines',              [GuidelinesController::class, 'store']);
            Route::get('/guidelines/{id}',          [GuidelinesController::class, 'show']);
            Route::post('/guidelines/{id}',         [GuidelinesController::class, 'update']);
            Route::put('/guidelines/{id}',          [GuidelinesController::class, 'update']);

            //contacts    
            Route::get('/contacts',               [ContactController::class, 'adminIndex']);
            Route::post('/contacts',              [ContactController::class, 'store']);
            Route::get('/contacts/{id}',          [ContactController::class, 'show']);
            Route::post('/contacts/{id}',         [ContactController::class, 'update']);
            Route::put('/contacts/{id}',          [ContactController::class, 'update']);

            // Permissions
            Route::get('/permissions',         [PermissionController::class, 'adminIndex'])->name('permissions.data');
            Route::post('/permissions',        [PermissionController::class, 'store'])->name('permissions.store');
            Route::put('/permissions/{id}',    [PermissionController::class, 'update'])->name('permissions.update');
            Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

            // roles
            Route::get('/roles/permissions',  [RoleController::class, 'permissions']);
            Route::get('/roles',              [RoleController::class, 'adminIndex']);
            Route::post('/roles',             [RoleController::class, 'store']);
            Route::put('/roles/{id}',         [RoleController::class, 'update']);
            Route::delete('/roles/{id}',      [RoleController::class, 'destroy']);

            // Users
            Route::get('/users/meta',    [UserController::class, 'rolesAndPermissions']);
            Route::get('/users',         [UserController::class, 'adminIndex']);
            Route::post('/users',        [UserController::class, 'store']);
            Route::put('/users/{id}',    [UserController::class, 'updateRoles']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);

            Route::get('/editorial-board',                  [AdminEditorialBoardController::class, 'adminIndex']);
            Route::post('/editorial-board',                 [AdminEditorialBoardController::class, 'store']);
            Route::get('/editorial-board/{id}',              [AdminEditorialBoardController::class, 'show']);
            Route::post('/editorial-board/{id}',             [AdminEditorialBoardController::class, 'update']);
            Route::put('/editorial-board/{id}',              [AdminEditorialBoardController::class, 'update']);
            Route::delete('/editorial-board/{id}',           [AdminEditorialBoardController::class, 'destroy']);
            Route::patch('/editorial-board/{id}/toggle',     [AdminEditorialBoardController::class, 'toggleStatus']);
            Route::post('/editorial-board/update-sequence',  [AdminEditorialBoardController::class, 'updateSequence']);


            // Submit Articles
            Route::get('submit-articles', [SubmitArticleController::class, 'adminIndex']);
            Route::get('submit-articles/{id}', [SubmitArticleController::class, 'show']);
            Route::put('submit-articles/{id}', [SubmitArticleController::class, 'update']);
            Route::post('submit-articles/{id}/approve', [SubmitArticleController::class, 'approve']);
            Route::post('submit-articles/{id}/reject', [SubmitArticleController::class, 'reject']);
            Route::post('submit-articles/{id}/forward-to-reviewer', [SubmitArticleController::class, 'forwardToReviewer']);
            Route::post('submit-articles/{id}/review-decision', [SubmitArticleController::class, 'reviewerDecision']);
            Route::post('submit-articles/{id}/editor-final-decision', [SubmitArticleController::class, 'editorFinalDecision']);
            Route::post('submit-articles/{id}/forward-to-author-revision', [SubmitArticleController::class, 'forwardToAuthorRevision']);
            Route::post('submit-articles/{id}/resubmit', [SubmitArticleController::class, 'resubmit']);
            Route::post('submit-articles/{id}/publish', [SubmitArticleController::class, 'publish']);
            Route::get('reviewers', [SubmitArticleController::class, 'reviewers']);
            Route::get('submit-articles-issues', [SubmitArticleController::class, 'issuesForJournal']); 

            
            // Volumes
            Route::get('/volumes',                       [VolumeController::class, 'adminIndex'])->name('volumes.data');
            Route::post('/volumes',                      [VolumeController::class, 'store'])->name('volumes.store');
            Route::get('/volumes/{id}',                  [VolumeController::class, 'show'])->name('volumes.show');
            Route::put('/volumes/{id}',                  [VolumeController::class, 'update'])->name('volumes.update');
            Route::delete('/volumes/{id}',               [VolumeController::class, 'destroy'])->name('volumes.destroy');
            Route::patch('/volumes/{id}/toggle-current', [VolumeController::class, 'toggleCurrent'])->name('volumes.toggle-current');

            // Issues
            Route::get('/issues',                        [IssueController::class, 'adminIndex'])->name('issues.data');
            Route::post('/issues',                       [IssueController::class, 'store'])->name('issues.store');
            Route::get('/issues/{id}',                   [IssueController::class, 'show'])->name('issues.show');
            Route::put('/issues/{id}',                   [IssueController::class, 'update'])->name('issues.update');
            Route::delete('/issues/{id}',                [IssueController::class, 'destroy'])->name('issues.destroy');
            Route::patch('/issues/{id}/toggle-current',  [IssueController::class, 'toggleCurrent'])->name('issues.toggle-current');


            //dashboard
            Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
            Route::get('/dashboard/monthly-submissions', [DashboardController::class, 'monthlySubmissions']);
            Route::get('/dashboard/monthly-published', [DashboardController::class, 'monthlyPublished']);
            Route::get('/dashboard/article-downloads', [DashboardController::class, 'articleDownloads']);
            Route::get('/dashboard/recent-submissions', [DashboardController::class, 'recentSubmissions']);
            Route::get('/dashboard/latest-publications', [DashboardController::class, 'latestPublications']);
        });
