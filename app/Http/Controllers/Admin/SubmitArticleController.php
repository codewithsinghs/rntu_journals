<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Issue;
use App\Models\SubmitArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubmitArticleController extends Controller
{

    private const STAGE_ACTIONS = [
        'submitted'           => ['approve', 'reject', 'forward'],
        'approved'            => [],
        'with_reviewer'       => ['approve', 'reject'],
        'reviewer_approved'   => ['approve', 'reject', 'editor_final_decide'],
        'reviewer_correction' => ['approve', 'reject'],
        'reviewer_rejected'   => [],
        'with_author'         => ['resubmit'],
        'with_author_payment' => ['publish'],
        'rejected'            => [],
        'published'           => [],
    ];


    private const OWNER_EDITABLE_STAGES = ['submitted', 'with_author', 'reviewer_correction'];

    public function index()
    {
        return view('frontend.submit');
    }

    public function journals()
    {
        try {
            $journals = Journal::where('is_active', true)
                ->orderBy('title')
                ->get(['id', 'title']);

            return response()->json([
                'status' => true,
                'data'   => $journals,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load journals for submission form', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to load journals.',
            ], 500);
        }
    }


    public function issuesForJournal(Request $request)
    {
        try {
            $user = $request->user('api');

            if (!$user || !$user->can('view all articles')) {
                Log::warning('Unauthorized issues list attempt', [
                    'user_id' => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to view issues.',
                ], 403);
            }

            $journalId = $request->integer('journal_id');

            if (!$journalId) {
                return response()->json([
                    'status'  => false,
                    'message' => 'journal_id is required.',
                ], 422);
            }

            $issues = Issue::with('volume:id,volume,year')
                ->where('journal_id', $journalId)
                ->orderByDesc('year')
                ->orderByDesc('issue')
                ->get(['id', 'journal_id', 'volume_id', 'issue', 'year', 'is_current']);

            return response()->json([
                'status' => true,
                'data'   => $issues,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load issues for journal', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to load issues.',
            ], 500);
        }
    }

    private function withFileUrls(SubmitArticle $article): SubmitArticle
    {
        $article->signed_manuscript_pdf_url = $article->signed_manuscript_pdf
            ? Storage::disk('public')->url($article->signed_manuscript_pdf)
            : null;

        $article->abstract_file_url = $article->abstract_file
            ? Storage::disk('public')->url($article->abstract_file)
            : null;

        $article->signature_img_url = $article->signature_img
            ? Storage::disk('public')->url($article->signature_img)
            : null;

        return $article;
    }

    private function resolveUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }


    private function findByUuid(string $uuid, array $with = []): SubmitArticle
    {
        return SubmitArticle::with($with)->where('uuid', $uuid)->firstOrFail();
    }

    private function allowedActionsFor(SubmitArticle $article): array
    {
        $stage = $article->review->current_stage ?? 'submitted';

        return self::STAGE_ACTIONS[$stage] ?? [];
    }

    private function userHasAccess($user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        return $user->can('view all articles') || $user->can($permission);
    }


    private function attachPermissionFlags(SubmitArticle $article, $user): void
{
    $canViewAll = $user && $user->can('view all articles');
    $isOwner    = $user && $article->user_id === $user->id;

    $allowedActions = $this->allowedActionsFor($article);
    $stage = $article->review->current_stage ?? 'submitted';

    $isOwnerEditable = in_array($stage, self::OWNER_EDITABLE_STAGES, true);

    $hasApprove         = $canViewAll || ($user?->can('approve article') ?? false);
    $hasReject          = $canViewAll || ($user?->can('reject article') ?? false);
    $hasForward         = $canViewAll || ($user?->can('forward article') ?? false);
    $hasForwardToAuthor = $canViewAll || ($user?->can('forward article to author') ?? false);
    $hasPublish         = $canViewAll || ($user?->can('publish article') ?? false);
    $hasReview          = $canViewAll || ($user?->can('review article') ?? false);

    $isAssignedReviewer = $hasReview
        && $article->review
        && $article->review->reviewer_id === $user?->id;

    $isPostApproval = in_array($stage, ['approved', 'with_author_payment', 'published', 'rejected', 'reviewer_rejected'], true);

    // FIX: previously `!$isPostApproval` gated the WHOLE expression, so
    // editors ($canViewAll) lost edit access once a submission passed
    // approval. Now the post-approval restriction only applies to the
    // owner/author — editors can edit at any stage.
    $article->can_edit = ($isOwner && $isOwnerEditable && !$isPostApproval) || $canViewAll;

    $article->can_approve = !$isPostApproval && $hasApprove && in_array('approve', $allowedActions, true);
    $article->can_reject  = !$isPostApproval && $hasReject && in_array('reject', $allowedActions, true);
    $article->can_forward = !$isPostApproval && $hasForward && in_array('forward', $allowedActions, true);

    $article->can_review        = $hasReview;
    $article->can_review_decide = !$isPostApproval && $isAssignedReviewer && $stage === 'with_reviewer';

    $canFinalDecide = !$isPostApproval && $hasForwardToAuthor
        && in_array('editor_final_decide', $allowedActions, true);

    $article->can_approve_final = $canFinalDecide;
    $article->can_reject_final  = $canFinalDecide;

    $article->can_editor_final_decide = $canFinalDecide;

    $article->can_forward_to_author_revision = !$isPostApproval && $hasForwardToAuthor
        && in_array('forward_to_author_revision', $allowedActions, true);

    $article->can_resubmit = ($isOwner || $canViewAll) && in_array('resubmit', $allowedActions, true);

    $article->can_publish = $hasPublish && in_array('publish', $allowedActions, true);

    $article->can_pay = ($isOwner || $canViewAll) && $stage === 'with_author_payment';

    $article->can_edit_issue = $canViewAll;
    $article->can_delete = $canViewAll;
    $article->can_hide   = $canViewAll;
    $article->is_published = (bool) ($article->review->is_published ?? false);

    $article->reviewer_name = $canViewAll
        ? ($article->review?->reviewer?->name ?? null)
        : null;

    $editorPendingStages = [
        'submitted',
        'with_reviewer',
        'reviewer_approved',
        'reviewer_rejected',
        'reviewer_correction',
        'approved',
    ];

    if ($isOwner && !$canViewAll) {
        $article->display_stage = in_array($stage, $editorPendingStages, true)
            ? 'with_editor'
            : $stage;
    } else {
        $article->display_stage = $stage;
    }
}

    // private function attachPermissionFlags(SubmitArticle $article, $user): void
    // {
    //     $canViewAll = $user && $user->can('view all articles');
    //     $isOwner    = $user && $article->user_id === $user->id;

    //     $allowedActions = $this->allowedActionsFor($article);
    //     $stage = $article->review->current_stage ?? 'submitted';

    //     $isOwnerEditable = in_array($stage, self::OWNER_EDITABLE_STAGES, true);

    //     $hasApprove         = $canViewAll || ($user?->can('approve article') ?? false);
    //     $hasReject          = $canViewAll || ($user?->can('reject article') ?? false);
    //     $hasForward         = $canViewAll || ($user?->can('forward article') ?? false);
    //     $hasForwardToAuthor = $canViewAll || ($user?->can('forward article to author') ?? false);
    //     $hasPublish         = $canViewAll || ($user?->can('publish article') ?? false);
    //     $hasReview          = $canViewAll || ($user?->can('review article') ?? false);

    //     $isAssignedReviewer = $hasReview
    //         && $article->review
    //         && $article->review->reviewer_id === $user?->id;

    //     $isPostApproval = in_array($stage, ['approved', 'with_author_payment', 'published', 'rejected', 'reviewer_rejected'], true);

    //     $article->can_edit = !$isPostApproval && (($isOwner && $isOwnerEditable) || $canViewAll);

    //     $article->can_approve = !$isPostApproval && $hasApprove && in_array('approve', $allowedActions, true);
    //     $article->can_reject  = !$isPostApproval && $hasReject && in_array('reject', $allowedActions, true);
    //     $article->can_forward = !$isPostApproval && $hasForward && in_array('forward', $allowedActions, true);

    //     $article->can_review        = $hasReview;
    //     $article->can_review_decide = !$isPostApproval && $isAssignedReviewer && $stage === 'with_reviewer';

    //     $canFinalDecide = !$isPostApproval && $hasForwardToAuthor
    //         && in_array('editor_final_decide', $allowedActions, true);

    //     $article->can_approve_final = $canFinalDecide;
    //     $article->can_reject_final  = $canFinalDecide;

    //     $article->can_editor_final_decide = $canFinalDecide;

    //     $article->can_forward_to_author_revision = !$isPostApproval && $hasForwardToAuthor
    //         && in_array('forward_to_author_revision', $allowedActions, true);

    //     $article->can_resubmit = ($isOwner || $canViewAll) && in_array('resubmit', $allowedActions, true);

    //     $article->can_publish = $hasPublish && in_array('publish', $allowedActions, true);

    //     $article->can_pay = ($isOwner || $canViewAll) && $stage === 'with_author_payment';

    //     $article->can_edit_issue = $canViewAll;
    //     $article->can_delete = $canViewAll;
    //     $article->can_hide   = $canViewAll;
    //     $article->is_published = (bool) ($article->review->is_published ?? false);

    //     $article->reviewer_name = $canViewAll
    //         ? ($article->review?->reviewer?->name ?? null)
    //         : null;

    //     $editorPendingStages = [
    //         'submitted',
    //         'with_reviewer',
    //         'reviewer_approved',
    //         'reviewer_rejected',
    //         'reviewer_correction',
    //         'approved',
    //     ];

    //     if ($isOwner && !$canViewAll) {
    //         $article->display_stage = in_array($stage, $editorPendingStages, true)
    //             ? 'with_editor'
    //             : $stage;
    //     } else {
    //         $article->display_stage = $stage;
    //     }
    // }

    public function adminIndex(Request $request)
    {
        try {
            $user = $request->user('api');

            $query = SubmitArticle::with([
                'journal:id,title',
                'review:id,submit_article_id,editor_id,editor_status,editor_remarks,reviewer_id,forwarded_to_reviewer_date,reviewer_status,reviewer_approval_date,reviewer_remarks,final_status,current_stage,is_published,published_at,revision_count',
                'review.reviewer:id,name,email',
            ])
                ->select([
                    'id',
                    'uuid',
                    'user_id',
                    'full_name',
                    'email',
                    'mobile_no',
                    'journal_id',
                    'manuscript_title',
                    'submission_date',
                    'is_hidden',
                    'hidden_at',
                    'created_at',
                ]);

            $canViewAll = $user && $user->can('view all articles');

            if ($user && !$canViewAll) {
                if ($user->can('review article')) {
                    $query->whereHas('review', function ($q) use ($user) {
                        $q->where('reviewer_id', $user->id);
                    });
                } else {
                    $query->where('user_id', $user->id);
                }

                $query->where('is_hidden', false);
            }

            if ($request->filled('q')) {
                $term = $request->string('q');
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('manuscript_title', 'like', "%{$term}%");
                });
            }

            if ($request->filled('journal_id')) {
                $query->where('journal_id', $request->integer('journal_id'));
            }

            $submissions = $query->orderByDesc('created_at')
                ->paginate($request->integer('per_page', 15));

            $submissions->getCollection()->each(fn($a) => $this->attachPermissionFlags($a, $user));

            return response()->json([
                'status'  => true,
                'message' => 'Submissions fetched successfully.',
                'data'    => $submissions,
                'meta'    => [
                    'show_review_dates' => $canViewAll,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch submissions', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to load submissions.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, [
                'journal:id,title',
                'issue.volume',
                'coAuthors',
                'reviewers',
                'review',
                'review.reviewer:id,name,email',
            ]);
            $user = $request->user('api');

            $canViewAll = $user && $user->can('view all articles');

            $isAssignedReviewer = $user
                && $user->can('review article')
                && $article->review
                && $article->review->reviewer_id === $user->id;

            if (
                !$canViewAll
                && $user
                && $article->user_id !== $user->id
                && !$isAssignedReviewer
            ) {
                Log::warning('Unauthorized submission view attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to view this submission.',
                ], 403);
            }

            if (!$canViewAll && $article->is_hidden) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not available.',
                ], 404);
            }

            $article = $this->withFileUrls($article);
            $this->attachPermissionFlags($article, $user);

            Log::info('Submission fetched', ['submission_id' => $id]);

            return response()->json([
                'status' => true,
                'data'   => $article,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Submission not found', ['submission_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review', 'coAuthors', 'reviewers']);

            $user = $request->user('api');

            $canViewAll = $user && $user->can('view all articles');

            if ($user && !$canViewAll && $article->user_id !== $user->id) {
                Log::warning('Unauthorized submission update attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to update this submission.',
                ], 403);
            }

            $stage = $article->review->current_stage ?? 'submitted';

            if (!$canViewAll && !in_array($stage, self::OWNER_EDITABLE_STAGES, true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission has moved past the editable stage and can no longer be updated.',
                ], 403);
            }

            $validated = $request->validate([
                'full_name'                     => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'mobile_no'                     => ['sometimes', 'required', 'string', 'regex:/^[6-9]\d{9}$/'],
                'email'                         => 'sometimes|required|email:rfc,dns|max:255',
                'affiliating_institute'         => 'sometimes|required|string|max:255',
                'department'                    => 'sometimes|required|string|max:255',
                'orcid_id'                      => ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'],
                'affiliating_institute_address' => 'sometimes|required|string|max:1000',
                'journal_id'                    => 'sometimes|required|integer|exists:journals,id',
                'issue_id'                      => 'sometimes|nullable|integer|exists:issues,id',
                'manuscript_title'              => 'sometimes|required|string|min:10|max:500',
                'abstract_summary'              => 'sometimes|required|string|min:100|max:5000',
                'keywords'                      => 'sometimes|required|array|min:1|max:8',
                'keywords.*'                    => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-]+$/'],
                'references'                    => 'nullable|string|max:5000',
                'declarations'                  => 'sometimes|required|array|min:1',
                'declarations.*'                => 'required|string|in:original,not_under_review,all_approved,ethical_approval,data_accurate',
                'author_signature'              => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],

                'signed_manuscript_pdf'         => ['nullable', 'file', 'mimes:pdf', 'max:51200', 'min:1'],
                'abstract_file'                 => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:51200', 'min:1'],
                'signature_file'                => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],

                'co_authors'                    => 'nullable|array|max:10',
                'co_authors.*.name'             => ['required_with:co_authors', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'co_authors.*.email'            => 'required_with:co_authors|email:rfc|max:255',
                'co_authors.*.affiliation'      => 'required_with:co_authors|string|max:255',
                'co_authors.*.orcid_id'         => ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'],
                'reviewers'                     => 'nullable|array|max:5',
                'reviewers.*.name'              => ['required_with:reviewers', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'reviewers.*.email'             => 'required_with:reviewers|email:rfc|max:255',
                'reviewers.*.institution'       => 'required_with:reviewers|string|max:255',
                'reviewers.*.area_of_expertise' => 'required_with:reviewers|string|max:255',
            ], [
                'full_name.required'                    => 'Full name is required.',
                'full_name.regex'                       => 'Full name must contain only letters, spaces, dots, or hyphens.',
                'mobile_no.required'                    => 'Mobile number is required.',
                'mobile_no.regex'                       => 'Please enter a valid 10-digit Indian mobile number (starting with 6, 7, 8, or 9).',
                'email.required'                        => 'Email address is required.',
                'email.email'                           => 'Please enter a valid email address.',
                'orcid_id.regex'                        => 'ORCID ID must be in the format: 0000-0000-0000-0000.',
                'journal_id.required'                   => 'Please select a journal.',
                'journal_id.exists'                     => 'Selected journal is invalid.',
                'issue_id.exists'                       => 'Selected issue is invalid.',
                'manuscript_title.required'             => 'Manuscript title is required.',
                'manuscript_title.min'                  => 'Manuscript title must be at least 10 characters.',
                'abstract_summary.required'             => 'Abstract is required.',
                'abstract_summary.min'                  => 'Abstract must be at least 100 characters.',
                'abstract_summary.max'                  => 'Abstract must not exceed 5000 characters.',
                'keywords.required'                     => 'Please enter at least one keyword.',
                'keywords.min'                          => 'Please enter at least one keyword.',
                'keywords.*.regex'                      => 'Keywords must contain only letters, numbers, spaces, or hyphens.',
                'references.max'                        => 'References must not exceed 5000 characters.',
                'declarations.required'                 => 'Please check at least one declaration.',
                'declarations.min'                      => 'Please check at least one declaration.',
                'declarations.*.in'                     => 'Invalid declaration value.',
                'author_signature.required'             => 'Author signature name is required.',
                'author_signature.regex'                => 'Signature name must contain only letters, spaces, dots, or hyphens.',
                'signed_manuscript_pdf.mimes'           => 'Manuscript must be a PDF file.',
                'signed_manuscript_pdf.max'             => 'Manuscript PDF must not exceed 50MB.',
                'abstract_file.mimes'                   => 'Source file must be a PDF, DOC, or DOCX file.',
                'abstract_file.max'                     => 'Source file must not exceed 50MB.',
                'signature_file.mimes'                  => 'Signature must be a JPEG or PNG image.',
                'signature_file.max'                    => 'Signature image must not exceed 2MB.',
                'co_authors.max'                        => 'You can add a maximum of 10 co-authors.',
                'co_authors.*.name.required_with'       => 'Co-author full name is required.',
                'co_authors.*.name.regex'               => 'Co-author name must contain only letters, spaces, dots, or hyphens.',
                'co_authors.*.email.required_with'      => 'Co-author email is required.',
                'co_authors.*.email.email'              => 'Please enter a valid co-author email address.',
                'co_authors.*.affiliation.required_with' => 'Co-author affiliation is required.',
                'co_authors.*.orcid_id.regex'           => 'Co-author ORCID ID must be in the format: 0000-0000-0000-0000.',
                'reviewers.max'                         => 'You can add a maximum of 5 reviewers.',
                'reviewers.*.name.required_with'        => 'Reviewer full name is required.',
                'reviewers.*.name.regex'                => 'Reviewer name must contain only letters, spaces, dots, or hyphens.',
                'reviewers.*.email.required_with'       => 'Reviewer email is required.',
                'reviewers.*.email.email'               => 'Please enter a valid reviewer email address.',
                'reviewers.*.institution.required_with' => 'Reviewer institution is required.',
                'reviewers.*.area_of_expertise.required_with' => 'Reviewer area of expertise is required.',
            ]);

            if (!$canViewAll) {
                unset($validated['issue_id']);
            } elseif (array_key_exists('issue_id', $validated) && !empty($validated['issue_id'])) {
                $targetJournalId = $validated['journal_id'] ?? $article->journal_id;
                $selectedIssue = Issue::find($validated['issue_id']);

                if (!$selectedIssue || (int) $selectedIssue->journal_id !== (int) $targetJournalId) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Selected issue does not belong to the selected journal.',
                    ], 422);
                }
            }

            $oldManuscriptPath = $article->signed_manuscript_pdf;
            $oldAbstractPath   = $article->abstract_file;
            $oldSignaturePath  = $article->signature_img;

            $newManuscriptPath = null;
            $newAbstractPath   = null;
            $newSignaturePath  = null;

            DB::beginTransaction();

            try {
                if ($request->hasFile('signed_manuscript_pdf')) {
                    $newManuscriptPath = $request->file('signed_manuscript_pdf')
                        ->store('articles/manuscripts', 'public');
                    $article->signed_manuscript_pdf = $newManuscriptPath;
                }

                if ($request->hasFile('abstract_file')) {
                    $newAbstractPath = $request->file('abstract_file')
                        ->store('articles/abstracts', 'public');
                    $article->abstract_file = $newAbstractPath;
                }

                if ($request->hasFile('signature_file')) {
                    $newSignaturePath = $request->file('signature_file')
                        ->store('articles/signatures', 'public');
                    $article->signature_img = $newSignaturePath;
                }

                $article->fill(collect($validated)->except([
                    'co_authors',
                    'reviewers',
                    'signed_manuscript_pdf',
                    'abstract_file',
                    'signature_file',
                ])->toArray());

                $article->save();

                if ($request->has('co_authors')) {
                    $article->coAuthors()->delete();

                    foreach ($validated['co_authors'] ?? [] as $index => $coAuthor) {
                        if (empty($coAuthor['name']) && empty($coAuthor['email'])) continue;

                        $article->coAuthors()->create([
                            'name'        => $coAuthor['name'],
                            'email'       => $coAuthor['email'],
                            'affiliation' => $coAuthor['affiliation'],
                            'orcid_id'    => $coAuthor['orcid_id'] ?? null,
                            'order'       => $index + 1,
                        ]);
                    }
                }

                // ── Sync reviewers: wipe and recreate ──
                if ($request->has('reviewers')) {
                    $article->reviewers()->delete();

                    foreach ($validated['reviewers'] ?? [] as $index => $reviewer) {
                        if (empty($reviewer['name']) && empty($reviewer['email'])) continue;

                        $article->reviewers()->create([
                            'name'              => $reviewer['name'],
                            'email'             => $reviewer['email'],
                            'institution'       => $reviewer['institution'],
                            'area_of_expertise' => $reviewer['area_of_expertise'],
                            'order'             => $index + 1,
                        ]);
                    }
                }

                if ($stage === 'reviewer_correction') {
                    $article->review()->update([
                        'reviewer_status'  => 'pending',
                        'reviewer_remarks' => null,
                        'current_stage'    => 'with_reviewer',
                    ]);
                }

                DB::commit();

                if ($newManuscriptPath && $oldManuscriptPath) {
                    Storage::disk('public')->delete($oldManuscriptPath);
                }
                if ($newAbstractPath && $oldAbstractPath) {
                    Storage::disk('public')->delete($oldAbstractPath);
                }
                if ($newSignaturePath && $oldSignaturePath) {
                    Storage::disk('public')->delete($oldSignaturePath);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                if ($newManuscriptPath) Storage::disk('public')->delete($newManuscriptPath);
                if ($newAbstractPath)   Storage::disk('public')->delete($newAbstractPath);
                if ($newSignaturePath)  Storage::disk('public')->delete($newSignaturePath);
                throw $e;
            }

            $article->load(['journal:id,title', 'issue.volume', 'coAuthors', 'reviewers', 'review']);
            $article = $this->withFileUrls($article);
            $this->attachPermissionFlags($article, $user);

            Log::info('Submission updated', [
                'submission_id' => $id,
                'user_id'       => $user?->id,
                'stage_before'  => $stage,
                'sent_to_reviewer_again' => $stage === 'reviewer_correction',
                'issue_overridden' => $canViewAll && array_key_exists('issue_id', $validated),
            ]);

            return response()->json([
                'status'  => true,
                'message' => $stage === 'reviewer_correction'
                    ? 'Submission updated and sent back to the reviewer.'
                    : 'Submission updated successfully.',
                'data'    => $article,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Submission not found for update', ['submission_id' => $id]);

            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            if (!$this->userHasAccess($user, 'approve article')) {
                Log::warning('Unauthorized approve attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to approve this submission.',
                ], 403);
            }

            if (!in_array('approve', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not in a stage that can be approved.',
                ], 409);
            }
            if ($article->issue_id) {
                $currentIssue = Issue::find($article->issue_id);
            } else {
                $currentIssue = Issue::where('journal_id', $article->journal_id)
                    ->whereDate('published_date', '<=', now())
                    ->orderByDesc('published_date')
                    ->first();
            }

            if (!$currentIssue) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No current issue is available for this journal yet. Please create/publish an issue before approving.',
                ], 422);
            }

            $stage = $article->review->current_stage ?? 'submitted';

            if ($stage === 'with_reviewer') {
                DB::transaction(function () use ($article, $currentIssue, $user) {
                    $article->issue_id = $currentIssue->id;
                    $article->save();

                    $article->review()->updateOrCreate(
                        ['submit_article_id' => $article->id],
                        [
                            'editor_id'     => $user->id,
                            'editor_status' => 'approved_pending_payment',
                            'current_stage' => 'with_author_payment',
                            'approval_date' => now(), // NEW: editor's approval timestamp
                        ]
                    );
                });
                $message = 'Submission approved directly and author notified for payment.';
            } else {
                DB::transaction(function () use ($article, $currentIssue, $user) {
                    $article->issue_id = $currentIssue->id;
                    $article->save();

                    $article->review()->updateOrCreate(
                        ['submit_article_id' => $article->id],
                        [
                            'editor_id'     => $user->id,
                            'editor_status' => 'approved',
                            'current_stage' => 'approved',
                            'approval_date' => now(),
                        ]
                    );
                });
                $message = 'Submission approved successfully.';
            }

            Log::info('Submission approved by editor', [
                'submission_id' => $id,
                'user_id'       => $user->id,
                'stage_before'  => $stage,
                'issue_id'      => $currentIssue->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => $message,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to approve submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            if (!$this->userHasAccess($user, 'reject article')) {
                Log::warning('Unauthorized reject attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to reject this submission.',
                ], 403);
            }

            if (!in_array('reject', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not in a stage that can be rejected.',
                ], 409);
            }

            $validated = $request->validate([
                'remarks' => 'required|string|max:1000',
            ], [
                'remarks.required' => 'A reason for rejection is required.',
            ]);

            $article->review()->updateOrCreate(
                ['submit_article_id' => $article->id],
                [
                    'editor_id'      => $user->id,
                    'editor_status'  => 'rejected',
                    'editor_remarks' => $validated['remarks'],
                    'final_status'   => 'rejected',
                    'current_stage'  => 'rejected',
                ]
            );

            Log::info('Submission rejected by editor', [
                'submission_id' => $id,
                'user_id'       => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Submission rejected successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to reject submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function forwardToReviewer(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            if (!$this->userHasAccess($user, 'forward article')) {
                Log::warning('Unauthorized forward attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to forward this submission.',
                ], 403);
            }

            if (!in_array('forward', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not in a stage that can be forwarded to a reviewer.',
                ], 409);
            }

            $validated = $request->validate([
                'reviewer_id' => 'required|integer|exists:users,id',
                'remarks'     => 'nullable|string|max:1000',
            ], [
                'reviewer_id.required' => 'Please select a reviewer.',
                'reviewer_id.exists'   => 'Selected reviewer is invalid.',
            ]);

            $reviewer = User::find($validated['reviewer_id']);

            if (!$reviewer || !$reviewer->can('review article')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Selected user is not a valid reviewer.',
                ], 422);
            }

            $article->review()->updateOrCreate(
                ['submit_article_id' => $article->id],
                [
                    'editor_id'                  => $user->id,
                    'reviewer_id'                => $reviewer->id,
                    'reviewer_status'            => 'pending',
                    'reviewer_remarks'           => $validated['remarks'] ?? null,
                    'current_stage'              => 'with_reviewer',
                    'forwarded_to_reviewer_date' => now(),
                ]
            );

            Log::info('Submission forwarded to reviewer', [
                'submission_id' => $id,
                'reviewer_id'   => $reviewer->id,
                'user_id'       => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Submission forwarded to {$reviewer->name} successfully.",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to forward submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reviewerDecision(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            $hasReview = $user && $user->can('review article');
            $isAssignedReviewer = $hasReview
                && $article->review
                && $article->review->reviewer_id === $user->id;

            if (!$isAssignedReviewer) {
                Log::warning('Unauthorized reviewer decision attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to review this submission.',
                ], 403);
            }

            $stage = $article->review->current_stage ?? 'submitted';
            if ($stage !== 'with_reviewer') {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not currently awaiting your review.',
                ], 409);
            }

            $validated = $request->validate([
                'decision' => 'required|string|in:approved,correction_needed,rejected',
                'remarks'  => 'required|string|max:1000',
            ], [
                'decision.required' => 'Please select a decision.',
                'decision.in'       => 'Invalid decision value.',
                'remarks.required'  => 'Please add remarks for your decision.',
            ]);

            $stageMap = [
                'approved'          => 'reviewer_approved',
                'correction_needed' => 'reviewer_correction',
                'rejected'          => 'reviewer_rejected',
            ];

            $updateData = [
                'reviewer_status'  => $validated['decision'],
                'reviewer_remarks' => $validated['remarks'],
                'current_stage'    => $stageMap[$validated['decision']],
            ];

            // NEW: record the exact date the reviewer approved, only when
            // their decision is "approved".

            $updateData['reviewer_approval_date'] = now();

            $article->review()->updateOrCreate(
                ['submit_article_id' => $article->id],
                $updateData
            );

            Log::info('Reviewer submitted decision', [
                'submission_id' => $id,
                'user_id'       => $user->id,
                'decision'      => $validated['decision'],
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Review decision submitted successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to submit reviewer decision', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function editorFinalDecision(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            if (!$this->userHasAccess($user, 'forward article to author')) {
                Log::warning('Unauthorized editor final decision attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to finalize this submission.',
                ], 403);
            }

            if (!in_array('editor_final_decide', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not awaiting your final decision.',
                ], 409);
            }

            $validated = $request->validate([
                'decision' => 'required|string|in:approve,reject',
                'remarks'  => 'required|string|max:1000',
            ], [
                'decision.required' => 'Please select approve or reject.',
                'decision.in'       => 'Invalid decision value.',
                'remarks.required'  => 'Please add remarks for the author.',
            ]);

            if ($validated['decision'] === 'approve') {
                $updateData = [
                    'editor_id'      => $user->id,
                    'editor_status'  => 'approved_pending_payment',
                    'editor_remarks' => $validated['remarks'],
                    'current_stage'  => 'with_author_payment',
                    'approval_date'  => now(),
                ];
                $message = 'Author notified to complete payment.';
            } else {
                $updateData = [
                    'editor_id'      => $user->id,
                    'editor_status'  => 'rejected',
                    'editor_remarks' => $validated['remarks'],
                    'final_status'   => 'rejected',
                    'current_stage'  => 'rejected',
                ];
                $message = 'Submission rejected and author notified.';
            }

            $article->review()->updateOrCreate(['submit_article_id' => $article->id], $updateData);

            Log::info('Editor final decision recorded', [
                'submission_id' => $id,
                'decision'      => $validated['decision'],
                'user_id'       => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to record editor final decision', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function forwardToAuthorRevision(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            if (!$this->userHasAccess($user, 'forward article to author')) {
                Log::warning('Unauthorized forward-to-author-revision attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to send this submission back to the author.',
                ], 403);
            }

            if (!in_array('forward_to_author_revision', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not awaiting a revision decision.',
                ], 409);
            }

            $validated = $request->validate([
                'remarks' => 'required|string|max:1000',
            ], [
                'remarks.required' => 'Please provide a note for the author.',
            ]);

            $existingCount = $article->review->revision_count ?? 0;

            $article->review()->updateOrCreate(
                ['submit_article_id' => $article->id],
                [
                    'editor_id'      => $user->id,
                    'editor_status'  => 'revision_requested',
                    'editor_remarks' => $validated['remarks'],
                    'current_stage'  => 'with_author',
                    'revision_count' => $existingCount + 1,
                ]
            );

            Log::info('Submission sent back to author for revision', [
                'submission_id' => $id,
                'user_id'       => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Submission sent back to the author for revision.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to send submission to author', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function resubmit(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            $canViewAll = $user && $user->can('view all articles');

            if (!$user || (!$canViewAll && $article->user_id !== $user->id)) {
                Log::warning('Unauthorized resubmit attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to resubmit this submission.',
                ], 403);
            }

            if (!in_array('resubmit', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not awaiting your resubmission.',
                ], 409);
            }

            // Same reviewer re-checks the corrected manuscript.
            $article->review()->updateOrCreate(
                ['submit_article_id' => $article->id],
                [
                    'reviewer_status'  => 'pending',
                    'reviewer_remarks' => null,
                    'current_stage'    => 'with_reviewer',
                ]
            );

            Log::info('Author resubmitted after revision', [
                'submission_id' => $id,
                'user_id'       => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Resubmitted successfully. Sent back to the reviewer.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to resubmit', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function publish(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            if (!$this->userHasAccess($user, 'publish article')) {
                Log::warning('Unauthorized publish attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to publish this submission.',
                ], 403);
            }

            if (!in_array('publish', $this->allowedActionsFor($article), true)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This submission is not in a stage that can be published.',
                ], 409);
            }

            if ($article->issue_id) {
                $latestIssue = Issue::find($article->issue_id);
            } else {
                // Latest issue of the SAME journal this article was submitted to.
                $latestIssue = Issue::whereHas('volume', function ($q) use ($article) {
                    $q->where('journal_id', $article->journal_id);
                })
                    ->orderByDesc('published_date')
                    ->first();
            }

            if (!$latestIssue) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No issue is available for this journal yet. Please create an issue before publishing.',
                ], 422);
            }

            DB::transaction(function () use ($article, $latestIssue, $user) {
                $article->issue_id = $latestIssue->id;
                $article->save();

                $article->review()->updateOrCreate(
                    ['submit_article_id' => $article->id],
                    [
                        'final_status'  => 'published',
                        'current_stage' => 'published',
                        'is_published'  => true,
                        'published_at'  => now(),
                    ]
                );
            });

            Log::info('Submission published', [
                'submission_id' => $id,
                'user_id'       => $user->id,
                'issue_id'      => $latestIssue->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Submission published successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to publish submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id, ['review']);
            $user = $request->user('api');

            $canViewAll = $user && $user->can('view all articles');

            if (!$canViewAll) {
                Log::warning('Unauthorized delete attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to delete this submission.',
                ], 403);
            }

            DB::transaction(function () use ($article) {
                $article->delete();
            });

            Log::info('Submission deleted', [
                'submission_id' => $id,
                'user_id'       => $user->id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Submission deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete submission', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleHide(Request $request, $id)
    {
        try {
            $article = $this->findByUuid($id);
            $user = $request->user('api');

            $canViewAll = $user && $user->can('view all articles');

            if (!$canViewAll) {
                Log::warning('Unauthorized hide/unhide attempt', [
                    'submission_id' => $id,
                    'user_id'       => $user?->id,
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'You are not authorized to hide this submission.',
                ], 403);
            }

            $newState = !$article->is_hidden;

            $article->is_hidden = $newState;
            $article->hidden_at = $newState ? now() : null;
            $article->hidden_by = $newState ? $user->id : null;
            $article->save();

            Log::info('Submission hide state toggled', [
                'submission_id' => $id,
                'user_id'       => $user->id,
                'is_hidden'     => $newState,
            ]);

            return response()->json([
                'status'  => true,
                'message' => $newState
                    ? 'Submission hidden successfully.'
                    : 'Submission is visible again.',
                'data'    => ['is_hidden' => $newState],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Submission not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to toggle hide state', [
                'submission_id' => $id,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reviewers(Request $request)
    {
        try {
            $reviewers = User::permission(['review article'])
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => true,
                'data'   => $reviewers,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load reviewers', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to load reviewers.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if ($request->server('CONTENT_LENGTH') > 0 && empty($_FILES) && empty($_POST)) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => [
                    'file' => ['The uploaded data is too large. Please reduce file sizes and try again.']
                ],
            ], 422);
        }

        try {
            $validated = $request->validate([
                'full_name'                     => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'mobile_no'                     => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
                'email'                         => 'required|email:rfc,dns|max:255',
                'affiliating_institute'         => 'required|string|max:255',
                'department'                    => 'required|string|max:255',
                'orcid_id'                      => ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'],
                'affiliating_institute_address' => 'required|string|max:1000',
                'journal_id'                    => 'required|integer|exists:journals,id',
                'manuscript_title'              => 'required|string|min:10|max:500',
                'abstract_summary'              => 'required|string|min:100|max:5000',
                'keywords'                      => 'required|array|min:1|max:8',
                'keywords.*'                    => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-]+$/'],
                'signed_manuscript_pdf'         => ['required', 'file', 'mimes:pdf', 'max:51200', 'min:1'],
                'abstract_file'                 => ['required', 'file', 'mimes:pdf,doc,docx', 'max:51200', 'min:1'],
                'signature_file'                => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:2048'],
                'references'                    => 'nullable|string|max:5000',
                'co_authors'                    => 'nullable|array|max:10',
                'co_authors.*.name'             => ['required_with:co_authors', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'co_authors.*.email'            => 'required_with:co_authors|email:rfc|max:255',
                'co_authors.*.affiliation'      => 'required_with:co_authors|string|max:255',
                'co_authors.*.orcid_id'         => ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'],
                'reviewers'                     => 'nullable|array|max:5',
                'reviewers.*.name'              => ['required_with:reviewers', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'reviewers.*.email'             => 'required_with:reviewers|email:rfc|max:255',
                'reviewers.*.institution'       => 'required_with:reviewers|string|max:255',
                'reviewers.*.area_of_expertise' => 'required_with:reviewers|string|max:255',
                'declarations'                  => 'required|array|min:1',
                'declarations.*'                => 'required|string|in:original,not_under_review,all_approved,ethical_approval,data_accurate',
                'author_signature'              => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
                'submission_date'               => 'required|date|before_or_equal:today',
                'terms_accepted'                => 'required|accepted',
            ], [
                'full_name.required'                    => 'Full name is required.',
                'full_name.regex'                       => 'Full name must contain only letters, spaces, dots, or hyphens.',
                'mobile_no.required'                    => 'Mobile number is required.',
                'mobile_no.regex'                       => 'Please enter a valid 10-digit Indian mobile number (starting with 6, 7, 8, or 9).',
                'email.required'                        => 'Email address is required.',
                'email.email'                           => 'Please enter a valid email address.',
                'orcid_id.regex'                        => 'ORCID ID must be in the format: 0000-0000-0000-0000.',
                'journal_id.required'                   => 'Please select a journal.',
                'journal_id.exists'                     => 'Selected journal is invalid.',
                'manuscript_title.required'             => 'Manuscript title is required.',
                'manuscript_title.min'                  => 'Manuscript title must be at least 10 characters.',
                'abstract_summary.required'             => 'Abstract is required.',
                'abstract_summary.min'                  => 'Abstract must be at least 100 characters.',
                'abstract_summary.max'                  => 'Abstract must not exceed 5000 characters.',
                'keywords.required'                     => 'Please enter at least one keyword.',
                'keywords.min'                          => 'Please enter at least one keyword.',
                'keywords.*.regex'                      => 'Keywords must contain only letters, numbers, spaces, or hyphens.',
                'signed_manuscript_pdf.required'        => 'Please upload the manuscript PDF.',
                'signed_manuscript_pdf.mimes'           => 'Manuscript must be a PDF file.',
                'signed_manuscript_pdf.max'             => 'Manuscript PDF must not exceed 50MB.',
                'signed_manuscript_pdf.min'             => 'Manuscript PDF file appears to be empty.',
                'abstract_file.required'                => 'Please upload the source file.',
                'abstract_file.mimes'                   => 'Source file must be a PDF, DOC, or DOCX file.',
                'abstract_file.max'                     => 'Source file must not exceed 50MB.',
                'abstract_file.min'                     => 'Source file appears to be empty.',
                'signature_file.mimes'                  => 'Signature must be a JPEG or PNG image.',
                'signature_file.max'                    => 'Signature image must not exceed 2MB.',
                'references.max'                        => 'References must not exceed 5000 characters.',
                'co_authors.max'                        => 'You can add a maximum of 10 co-authors.',
                'co_authors.*.name.required_with'       => 'Co-author full name is required.',
                'co_authors.*.name.regex'               => 'Co-author name must contain only letters, spaces, dots, or hyphens.',
                'co_authors.*.email.required_with'      => 'Co-author email is required.',
                'co_authors.*.email.email'              => 'Please enter a valid co-author email address.',
                'co_authors.*.affiliation.required_with' => 'Co-author affiliation is required.',
                'co_authors.*.orcid_id.regex'           => 'Co-author ORCID ID must be in the format: 0000-0000-0000-0000.',
                'reviewers.max'                         => 'You can add a maximum of 5 reviewers.',
                'reviewers.*.name.required_with'        => 'Reviewer full name is required.',
                'reviewers.*.name.regex'                => 'Reviewer name must contain only letters, spaces, dots, or hyphens.',
                'reviewers.*.email.required_with'       => 'Reviewer email is required.',
                'reviewers.*.email.email'               => 'Please enter a valid reviewer email address.',
                'reviewers.*.institution.required_with' => 'Reviewer institution is required.',
                'reviewers.*.area_of_expertise.required_with' => 'Reviewer area of expertise is required.',
                'declarations.required'                 => 'Please check at least one declaration.',
                'declarations.min'                      => 'Please check at least one declaration.',
                'declarations.*.in'                     => 'Invalid declaration value.',
                'author_signature.required'             => 'Author signature name is required.',
                'author_signature.regex'                => 'Signature name must contain only letters, spaces, dots, or hyphens.',
                'submission_date.required'              => 'Submission date is required.',
                'submission_date.before_or_equal'       => 'Submission date cannot be a future date.',
                'terms_accepted.required'               => 'You must accept the terms and instructions.',
                'terms_accepted.accepted'               => 'You must accept the terms and instructions.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $manuscriptPath = null;
        $abstractPath   = null;
        $signaturePath  = null;

        $authenticatedUser = $this->resolveUserByEmail($validated['email']);

        DB::beginTransaction();

        try {
            $manuscriptPath = $request->file('signed_manuscript_pdf')
                ->store('articles/manuscripts', 'public');

            $abstractPath = $request->file('abstract_file')
                ->store('articles/abstracts', 'public');

            if ($request->hasFile('signature_file')) {
                $signaturePath = $request->file('signature_file')
                    ->store('articles/signatures', 'public');
            }

            $article = SubmitArticle::create([
                'user_id'                       => $authenticatedUser?->id,
                'full_name'                     => $validated['full_name'],
                'mobile_no'                     => $validated['mobile_no'],
                'email'                         => $validated['email'],
                'affiliating_institute'         => $validated['affiliating_institute'],
                'department'                    => $validated['department'],
                'orcid_id'                      => $validated['orcid_id'] ?? null,
                'affiliating_institute_address' => $validated['affiliating_institute_address'],
                'journal_id'                    => $validated['journal_id'],
                'manuscript_title'              => $validated['manuscript_title'],
                'abstract_summary'              => $validated['abstract_summary'],
                'keywords'                      => $validated['keywords'],
                'signed_manuscript_pdf'         => $manuscriptPath,
                'abstract_file'                 => $abstractPath,
                'signature_img'                 => $signaturePath,
                'references'                    => $validated['references'] ?? null,
                'declarations'                  => $validated['declarations'],
                'author_signature'              => $validated['author_signature'],
                'submission_date'               => $validated['submission_date'],
                'terms_accepted'                => true,
            ]);

            $article->review()->create([
                'current_stage' => 'submitted',
            ]);

            if (!empty($validated['co_authors'])) {
                foreach ($validated['co_authors'] as $index => $coAuthor) {
                    if (empty($coAuthor['name']) && empty($coAuthor['email'])) continue;

                    $article->coAuthors()->create([
                        'name'        => $coAuthor['name'],
                        'email'       => $coAuthor['email'],
                        'affiliation' => $coAuthor['affiliation'],
                        'orcid_id'    => $coAuthor['orcid_id'] ?? null,
                        'order'       => $index + 1,
                    ]);
                }
            }

            if (!empty($validated['reviewers'])) {
                foreach ($validated['reviewers'] as $index => $reviewer) {
                    if (empty($reviewer['name']) && empty($reviewer['email'])) continue;

                    $article->reviewers()->create([
                        'name'              => $reviewer['name'],
                        'email'             => $reviewer['email'],
                        'institution'       => $reviewer['institution'],
                        'area_of_expertise' => $reviewer['area_of_expertise'],
                        'order'             => $index + 1,
                    ]);
                }
            }

            DB::commit();

            Log::info('Article submitted', [
                'id'      => $article->id,
                'user_id' => $authenticatedUser?->id,
            ]);

            return response()->json([
                'status'   => true,
                'message'  => 'Article submitted successfully.',
                'data'     => ['id' => $article->id],
                'redirect' => '/login',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($manuscriptPath) Storage::disk('public')->delete($manuscriptPath);
            if ($abstractPath)   Storage::disk('public')->delete($abstractPath);
            if ($signaturePath)  Storage::disk('public')->delete($signaturePath);
            Log::error('Article submission failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'message' => 'Submission failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}