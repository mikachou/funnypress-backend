<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Models\Feed;
use App\Models\Article;
use SimplePie\SimplePie;

class FetchFeedArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $feed,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('fetch')->info("Fetching feed: " . $this->feed->url);

        $simplePie = new SimplePie();
        $simplePie->set_feed_url($this->feed->url);
        $simplePie->enable_cache(false);
        $simplePie->init();
        $simplePie->handle_content_type();

        if ($simplePie->error()) {
            Log::channel('fetch')->error("Error fetching feed: " . $simplePie->error());
            Feed::where('id', $this->feed->id)->update(['last_error_message' => $simplePie->error()]);
            return;
        } else {
            Feed::where('id', $this->feed->id)->update(['last_error_message' => null]);
        }

        foreach ($simplePie->get_items() as $item) {
            $title = $item->get_title();
            $url = $item->get_permalink();
            $date = $item->get_date('Y-m-d H:i:s') ?? date('Y-m-d H:i:s');

            $skip = false;
            if (empty($title)) {
                Log::channel('fetch')->info("Skipping empty title: " . $url);
                $skip = true;
            }

            $title = mb_substr($title, 0, 511, "UTF-8");

            if (empty($url)) {
                Log::channel('fetch')->info("Skipping empty URL: " . $title);
                $skip = true;
            }

            if (strlen($url) > 511) {
                Log::channel('fetch')->info("Skipping long URL: " . $title);
                $skip = true;
            }

            if (Article::where('url', $url)->exists()) {
                Log::channel('fetch')->info("Skipping duplicate article: " . $title);
                $skip = true;
            }

            if ($skip) {
                continue;
            }

            Article::create([
                'title' => $title,
                'url' => $url,
                'date' => $date,
            ]);

            Log::channel('fetch')->info("- Saved: " . $title . " (" . $url . ") on " . $date);
        }
    }
}
