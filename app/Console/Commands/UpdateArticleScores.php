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
        $this->info(sprintf("Starting to process articles in batches of %d...", config('app.update_scores_batch_size')));

        Article::whereNull('score')->chunkById(config('app.update_scores_batch_size'), function ($articles) {
            $json = [];
            foreach ($articles as $article) {
                $json[] = [
                    'title' => $article->title,
                    'id' => (string) $article->id,
                ];
            }

            $response = Http::post('http://funnypress-ws:8000/predict_batch', $json);

            if ($response->successful()) {
                $data = $response->json();
                foreach ($data as $item) {
                    $article = Article::find((int) $item['id']);
                    if ($article) {
                        $article->score = $item['score'];
                        $article->save();
                        $this->info("Updated article ID {$article->id} with score: {$item['score']}");
                    } else {
                        $this->error("Invalid response for article ID {$item['id']}");
                    }
                }
            } else {
                $this->error("Failed to fetch score for article ID {$article->id}");
            }
        });

        $this->info("Article score update process completed.");
    }
}
