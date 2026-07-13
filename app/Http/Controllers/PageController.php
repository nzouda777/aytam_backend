<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Family;
use App\Models\Orphan;
use App\Models\Post;
use App\Models\Program;
use App\Models\Sponsorship;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', [
            'campaigns' => Campaign::with(['category'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'featuredOrphans' => Orphan::with('family')
                ->where('is_sponsored', false)
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get(),
            'posts' => $this->publishedPosts()->take(3)->values(),
            'testimonials' => Testimonial::active()->get(),
            'impactStats' => $this->impactStats(),
        ]);
    }

    public function donate(Request $request): Response
    {
        return Inertia::render('Donate', [
            'campaigns' => Campaign::with(['category'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'cause' => $request->query('cause'),
            'amount' => $request->query('amount'),
        ]);
    }

    public function sponsorship(): Response
    {
        return Inertia::render('Sponsorship', [
            'beneficiaries' => Sponsorship::with([
                'family' => fn ($query) => $query->withCount('orphans'),
                'orphan',
            ])->get(),
        ]);
    }

    public function sponsorCheckout(Request $request): Response
    {
        $category = $request->query('category'); // orphan | widow | family
        $id = $request->query('id');

        // Les identifiants peuvent être préfixés (w-XX, f-XX) côté frontend
        if ($id && (str_starts_with($id, 'w-') || str_starts_with($id, 'f-'))) {
            $id = substr($id, 2);
        }

        $beneficiary = null;

        if ($id && $category === 'orphan') {
            $beneficiary = Orphan::with('family')->find($id);
        } elseif ($id && in_array($category, ['widow', 'family'], true)) {
            $beneficiary = Family::withCount('orphans')->find($id);
        }

        return Inertia::render('SponsorCheckout', [
            'beneficiaryData' => $beneficiary,
            'category' => $category,
        ]);
    }

    public function blog(): Response
    {
        return Inertia::render('Blog', [
            'posts' => $this->publishedPosts(),
        ]);
    }

    public function blogPost(string $id)
    {
        $post = Post::where('status', 'published')
            ->where(fn ($query) => $query->where('id', $id)->orWhere('slug', $id))
            ->first();

        if (! $post) {
            return Inertia::render('NotFound')
                ->toResponse(request())
                ->setStatusCode(404);
        }

        $related = $this->publishedPosts()
            ->reject(fn ($p) => $p['id'] === $post->id)
            ->take(3)
            ->values();

        return Inertia::render('BlogPost', [
            'post' => $this->presentPost($post),
            'relatedPosts' => $related,
        ]);
    }

    public function program(string $slug)
    {
        $program = Program::active()->where('slug', $slug)->first();

        if (! $program) {
            return Inertia::render('NotFound')
                ->toResponse(request())
                ->setStatusCode(404);
        }

        return Inertia::render('Program', [
            'program' => $program,
            'campaigns' => $program->category_id
                ? Campaign::where('category_id', $program->category_id)
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get()
                : [],
        ]);
    }

    public function programCheckout(string $slug)
    {
        $program = Program::active()->where('slug', $slug)->first();

        if (! $program) {
            return Inertia::render('NotFound')
                ->toResponse(request())
                ->setStatusCode(404);
        }

        return Inertia::render('ProgramCheckout', [
            'program' => $program,
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('About', [
            'impactStats' => $this->impactStats(),
        ]);
    }

    public function setLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', SetLocale::SUPPORTED)],
        ]);

        $request->session()->put('locale', $validated['locale']);

        return back();
    }

    /**
     * Articles publiés, dans la forme attendue par le frontend.
     */
    private function publishedPosts()
    {
        return Post::where('status', 'published')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Post $post) => $this->presentPost($post));
    }

    private function presentPost(Post $post): array
    {
        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'author' => $post->author,
            'date' => $post->published_at?->toDateString(),
            'category' => $post->category,
            'image' => $post->image_url ?? $post->image,
            'readTime' => $post->read_time,
        ];
    }

    /**
     * Statistiques d'impact calculées depuis la base (cache 10 min).
     */
    private function impactStats(): array
    {
        return Cache::remember('impact-stats', 600, function () {
            return [
                'orphansSponsored' => Orphan::where('is_sponsored', true)->count(),
                'regionsServed' => Family::whereNotNull('region')->distinct()->count('region'),
                'donationsRaised' => (float) Donation::where('status', 'completed')->sum('amount'),
                'activeDonors' => Donation::where('status', 'completed')->distinct()->count('donor_email'),
            ];
        });
    }
}
