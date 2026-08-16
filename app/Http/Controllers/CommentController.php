<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Http\Requests\CommentFormRequest;
use App\Mail\NewCommentNotification;
use App\Models\Comment;
use App\Models\Post;
use App\Support\CommentSanitizer;
use App\Support\SiteContact;
use App\Support\SubmissionThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function store(CommentFormRequest $request, string $slug): RedirectResponse
    {
        $post = Post::query()->published()->where('slug', $slug)->firstOrFail();

        abort_unless($post->commentsAreOpen(), 403);

        $retryAfter = SubmissionThrottle::attempt('comment:'.$request->ip());

        if ($retryAfter !== null) {
            return back()
                ->withInput($request->except(['website', 'consent', 'ts']))
                ->withErrors(['content' => "Trop d'envois. Réessayez dans {$retryAfter}s."])
                ->withFragment('commentaires');
        }

        $data = $request->validated();

        $comment = $post->comments()->create([
            'author_name' => $data['author_name'],
            'author_email' => $data['author_email'],
            'content' => CommentSanitizer::clean($data['content']),
            'status' => CommentStatus::Pending,
            'posted_at' => now(),
            'author_ip' => $request->ip(),
        ]);

        $this->notifyModerator($comment);

        return back()
            ->with('comment_status', 'Merci ! Votre commentaire a bien été envoyé. Il sera publié après validation.')
            ->withFragment('commentaires');
    }

    private function notifyModerator(Comment $comment): void
    {
        $to = SiteContact::notifyEmail();

        if (blank($to)) {
            return;
        }

        $moderationUrl = route('filament.admin.resources.comments.index');

        Mail::to($to)->send(new NewCommentNotification($comment, $moderationUrl));
    }
}
