        <?php

        use App\Http\Controllers\Admin\AboutBasicContentController;
        use App\Http\Controllers\Admin\AnnouncementController;
        use App\Http\Controllers\Admin\ContactController;
        use App\Http\Controllers\Admin\DashboardController;
        use App\Http\Controllers\Admin\EditorialBoardRoleController;
        use App\Http\Controllers\Frontend\EditorialBoardController as FrontendEditorialBoardController;
        use App\Http\Controllers\Admin\EditorialBoardController as AdminEditorialBoardController;
        use App\Http\Controllers\Admin\GuidelinesController;
        use App\Http\Controllers\Admin\HomeBasicContentController;
        use App\Http\Controllers\Admin\IssueController;
        use App\Http\Controllers\Admin\JournalsController;
        use App\Http\Controllers\Admin\MediasController;
        use App\Http\Controllers\Admin\MenuController as AdminMenuController;
        use App\Http\Controllers\Admin\PageController as AdminPageController;
        use App\Http\Controllers\Frontend\PageController as FrontendPageController;
        use App\Http\Controllers\Admin\PermissionController;
        use App\Http\Controllers\Admin\PrpController;
        use App\Http\Controllers\Admin\RoleController;
        use App\Http\Controllers\Frontend\MenuController as FrontendMenuController;
        use App\Http\Controllers\Admin\SettingsController;
        use App\Http\Controllers\Admin\SubmitArticleController;
        use App\Http\Controllers\Admin\UserController;
        use App\Http\Controllers\Admin\VolumeController;
        use App\Http\Controllers\Admin\WebsiteVisitorController;
        use App\Http\Controllers\Frontend\SettingController;
        use App\Http\Controllers\Frontend\AboutController;
        use App\Http\Controllers\Frontend\ArchiveController;
        use App\Http\Controllers\Frontend\ArticleController;
        use App\Http\Controllers\Frontend\ContactController as FrontendContactController;
        use App\Http\Controllers\Frontend\CurrentIssuesController;
        use App\Http\Controllers\Frontend\FooterController;
        use App\Http\Controllers\Frontend\GuidelinesController as FrontendGuidelinesController;
        use App\Http\Controllers\Frontend\HomeController as FrontendHomeController;
        use App\Http\Controllers\Frontend\JournalDetailController;
        use App\Http\Controllers\Frontend\MenuController;
        use App\Http\Controllers\Frontend\PrpController as FrontendPrpController;
        use Illuminate\Support\Facades\Route;


        // ── Public routes (no auth) ───────────────────────────────────────
        Route::get('/submit-article/journals', [SubmitArticleController::class, 'journals']);
        Route::post('/submit-article',         [SubmitArticleController::class, 'store']);


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
            Route::get('/guidelines',           [GuidelinesController::class, 'adminIndex'])->name('guidelines.data');
            Route::post('/guidelines',          [GuidelinesController::class, 'store'])->name('guidelines.store');
            Route::get('/guidelines/{id}',      [GuidelinesController::class, 'show'])->name('guidelines.show');
            Route::post('/guidelines/{id}',     [GuidelinesController::class, 'update'])->name('guidelines.update.multipart');
            Route::put('/guidelines/{id}',      [GuidelinesController::class, 'update'])->name('guidelines.update');
            Route::delete('/guidelines/{id}',   [GuidelinesController::class, 'destroy'])->name('guidelines.destroy');

            //prp
            Route::get('/prp',           [PrpController::class, 'adminIndex'])->name('prp.data');
            Route::post('/prp',          [PrpController::class, 'store'])->name('prp.store');
            Route::get('/prp/{id}',      [PrpController::class, 'show'])->name('prp.show');
            Route::post('/prp/{id}',     [PrpController::class, 'update'])->name('prp.update.multipart');
            Route::put('/prp/{id}',      [PrpController::class, 'update'])->name('prp.update');
            Route::delete('/prp/{id}',   [PrpController::class, 'destroy'])->name('prp.destroy');


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
            Route::get('/users/me', [UserController::class, 'me']);

            Route::get('/editorial-board',                   [AdminEditorialBoardController::class, 'adminIndex']);
            Route::post('/editorial-board',                  [AdminEditorialBoardController::class, 'store']);
            Route::get('/editorial-board/{id}',              [AdminEditorialBoardController::class, 'show']);
            Route::post('/editorial-board/{id}',             [AdminEditorialBoardController::class, 'update']);
            Route::put('/editorial-board/{id}',              [AdminEditorialBoardController::class, 'update']);
            Route::delete('/editorial-board/{id}',           [AdminEditorialBoardController::class, 'destroy']);
            Route::patch('/editorial-board/{id}/toggle',     [AdminEditorialBoardController::class, 'toggleStatus']);
            Route::post('/editorial-board/update-sequence',  [AdminEditorialBoardController::class, 'updateSequence']);

            //Editorial Board Role
            Route::get('/editorial-board-roles',                   [EditorialBoardRoleController::class, 'adminIndex'])->name('editorial-board-roles.data');
            Route::post('/editorial-board-roles',                  [EditorialBoardRoleController::class, 'store'])->name('editorial-board-roles.store');
            Route::get('/editorial-board-roles/{id}',               [EditorialBoardRoleController::class, 'show'])->name('editorial-board-roles.show');
            Route::put('/editorial-board-roles/{id}',              [EditorialBoardRoleController::class, 'update'])->name('editorial-board-roles.update');
            Route::delete('/editorial-board-roles/{id}',           [EditorialBoardRoleController::class, 'destroy'])->name('editorial-board-roles.destroy');
            Route::patch('/editorial-board-roles/{id}/toggle',     [EditorialBoardRoleController::class, 'toggleStatus'])->name('editorial-board-roles.toggle');

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
            Route::delete('submit-articles/{id}', [SubmitArticleController::class, 'destroy']);
            Route::post('submit-articles/{id}/toggle-hide', [SubmitArticleController::class, 'toggleHide']);

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


            //website visitor
            Route::get('/website-visitors',      [WebsiteVisitorController::class, 'adminIndex'])->name('website-visitors.data');
            Route::get('/website-visitors/{id}', [WebsiteVisitorController::class, 'show'])->name('website-visitors.show');
            Route::delete('/website-visitors/{id}', [WebsiteVisitorController::class, 'destroy'])->name('website-visitors.destroy');

            //Pages Module
            Route::get('/pages',               [AdminPageController::class, 'adminIndex'])->name('pages.data');
            Route::post('/pages',              [AdminPageController::class, 'store'])->name('pages.store');
            Route::get('/pages/{id}',          [AdminPageController::class, 'show'])->name('pages.show');
            Route::post('/pages/{id}',         [AdminPageController::class, 'update'])->name('pages.update.multipart');
            Route::put('/pages/{id}',          [AdminPageController::class, 'update'])->name('pages.update');
            Route::delete('/pages/{id}',       [AdminPageController::class, 'destroy'])->name('pages.destroy');
            Route::patch('/pages/{id}/toggle', [AdminPageController::class, 'toggleStatus'])->name('pages.toggle');
        });


        Route::group(['prefix' => 'public'], function () {

            Route::get('/home-content',    [FrontendHomeController::class, 'content']);
            Route::get('/journals',        [FrontendHomeController::class, 'journalsList']);
            Route::get('/announcements',   [FrontendHomeController::class, 'announcementsList']);
            Route::get('/latest-articles', [FrontendHomeController::class, 'latestArticles']);
            Route::get('/about', [AboutController::class, 'content']);
            Route::get('/guidelines/{journalParam}', [FrontendGuidelinesController::class, 'content']);
            Route::get('/prp', [FrontendPrpController::class, 'content']);
            Route::get('/contact', [FrontendContactController::class, 'content']);
            Route::get('/journals/{identifier}/detail', [JournalDetailController::class, 'detail']);
            Route::get('/journals/{id}/archives', [ArchiveController::class, 'archivesData']);
            Route::get('/issues/{uuid}/articles', [CurrentIssuesController::class, 'articlesData']);
            Route::get('/articles/{uuid}', [ArticleController::class, 'data']);
            Route::get('/editorial-board/{journalParam}', [FrontendEditorialBoardController::class, 'boardData']);
            Route::get('/menus', [MenuController::class, 'index']);
            Route::get('/menus/location/{location}', [MenuController::class, 'byLocation']);
            Route::get('/settings/logo', [SettingController::class, 'logo']);
            Route::get('/footer', [FooterController::class, 'index']);
            Route::get('/visitor-count', [FrontendHomeController::class, 'visitorCount']);

            //page module
            Route::get('/pages/{slug}', [FrontendPageController::class, 'content']);
            Route::get('/pages/homepage/current', [FrontendPageController::class, 'homepage']);
        });
