<?php

namespace App\Services;

use App\Models\SubmitArticle;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    /**
     * Link all previously-submitted articles (matched by email)
     * to the newly registered user and assign the author role.
     */
    public function linkSubmissionsAndAssignAuthorRole(User $user): void
    {
        $this->linkSubmissionsToUser($user);
        $this->assignAuthorRole($user);
    }

    /**
     * Link existing submissions with the registered user.
     */
    public function linkSubmissionsToUser(User $user): int
    {
        try {
            $updated = SubmitArticle::where('email', $user->email)
                ->whereNull('user_id')
                ->update([
                    'user_id' => $user->id,
                ]);

            if ($updated > 0) {
                Log::info('Linked existing submissions to new user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'articles_linked' => $updated,
                ]);
            }

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to link submissions to user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Assign Author role if user doesn't already have it.
     */
public function assignAuthorRole(User $user): void
{
    try {
        if (
            !$user->hasAnyRole(['super-admin', 'admin', 'editor']) &&
            !$user->hasRole('author')
        ) {
            $user->assignRole('author');
        }
    } catch (\Exception $e) {
        Log::error('Failed to assign author role', [
            'user_id' => $user->id,
            'error'   => $e->getMessage(),
        ]);
    }
}
    /**
     * Get articles according to the user's role.
     *
     * Super Admin/Admin/Editor -> All Articles
     * Author -> Only Own Articles
     */
    public function getArticles(User $user): Collection
    {
        if ($user->hasAnyRole(['super-admin', 'admin', 'editor'])) {
            return SubmitArticle::latest()->get();
        }

        return SubmitArticle::where('user_id', $user->id)
            ->latest()
            ->get();
    }
}