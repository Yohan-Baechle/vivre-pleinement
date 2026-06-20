<?php

namespace App\Http\Controllers;

use App\Console\Commands\CleanCommentContent;
use App\Enums\CommentStatus;
use App\Http\Requests\CommentFormRequest;
use App\Mail\NewCommentNotification;
use App\Models\Comment;
use App\Models\Post;
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

        $key = 'comment:'.$request->ip();
        if (SubmissionThrottle::exceeded($key)) {
            $seconds = SubmissionThrottle::availableIn($key);

            return back()
                ->withInput($request->except(['website', 'consent', 'ts']))
                ->withErrors(['content' => "Trop d'envois. Réessayez dans {$seconds}s."])
                ->withFragment('commentaires');
        }
        SubmissionThrottle::hit($key);

        $data = $request->validated();

        $comment = $post->comments()->create([
            'author_name' => $data['author_name'],
            'author_email' => $data['author_email'],
            'content' => CleanCommentContent::clean($data['content']),
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
