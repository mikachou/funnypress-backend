<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;

class ExportArticles extends Command
{
    protected $signature = 'articles:export-csv {path?}';
    protected $description = 'Export all articles to a CSV file.';

    public function handle()
    {
        $path = $this->argument('path') ?? 'articles.csv';

        $this->info("Exporting articles to {$path}...");

        $articles = Article::all();

        if ($articles->isEmpty()) {
            $this->info('No articles to export.');
            return;
        }

        try {
            $file = fopen($path, 'w');

            // Add CSV header
            fputcsv($file, ['title', 'url', 'date', 'score']);

            // Add each article as a new row
            foreach ($articles as $article) {
                fputcsv($file, [
                    $article->title,
                    $article->url,
                    $article->date,
                    $article->score,
                ]);
            }

            fclose($file);

            $this->info("Successfully exported {$articles->count()} articles to {$path}.");

        } catch (\Exception $e) {
            $this->error("An error occurred while exporting the articles: {$e->getMessage()}");
        }
    }
}
