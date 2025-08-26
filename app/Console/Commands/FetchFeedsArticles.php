<?php

namespace App\Console\Commands;

use App\Jobs\FetchFeedArticlesJob;
use Illuminate\Console\Command;
use App\Models\Feed;

class FetchFeedsArticles extends Command
{
    protected $signature = 'feeds:fetch';
    protected $description = 'Fetch article titles, URLs, and dates from all feeds stored in the database and save them to the Article entity';

    public function handle()
    {
        Feed::chunkById(10, function ($feeds) {
            foreach ($feeds as $feed) {
                FetchFeedArticlesJob::dispatch($feed);
            }
        });
    }
}
