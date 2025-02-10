<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feed;
use App\Models\Article;
use SimplePie\SimplePie;

class FetchFeedsArticles extends Command
{
    protected $signature = 'feeds:fetch';
    protected $description = 'Fetch article titles, URLs, and dates from all feeds stored in the database and save them to the Article entity';

    public function handle()
    {
        Feed::chunk(10, function ($feeds) {
            foreach ($feeds as $feed) {
                $this->info("Fetching feed: " . $feed->url);

                $simplePie = new SimplePie();
                $simplePie->set_feed_url($feed->url);
                $simplePie->enable_cache(false);
                $simplePie->init();
                $simplePie->handle_content_type();

                if ($simplePie->error()) {
                    $this->error("Error fetching feed: " . $simplePie->error());
                    continue;
                }

                foreach ($simplePie->get_items() as $item) {
                    $title = $item->get_title();
                    $url = $item->get_permalink();
                    $date = $item->get_date('Y-m-d H:i:s');

                    if (Article::where('url', $url)->exists()) {
                        $this->info("Skipping duplicate article: " . $title);
                        continue;
                    }

                    Article::create([
                        'title' => $title,
                        'url' => $url,
                        'date' => $date,
                    ]);

                    $this->line("- Saved: " . $title . " (" . $url . ") on " . $date);
                }
            }
        });
    }
}
