<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\Http;

class UpdateArticleScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:update-scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch scores for articles with null scores from an external API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting to process articles in batches of 10...");

        Article::whereNull('score')->chunk(10, function ($articles) {
            foreach ($articles as $article) {
                $response = Http::post('http://funnypress-ws:8000/predict', [
                    'title' => $article->title
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['score'])) {
                        $article->score = $data['score'];
                        $article->save();
                        $this->info("Updated article ID {$article->id} with score: {$data['score']}");
                    } else {
                        $this->error("Invalid response for article ID {$article->id}");
                    }
                } else {
                    $this->error("Failed to fetch score for article ID {$article->id}");
                }
            }
        });

        $this->info("Article score update process completed.");
    }
}
