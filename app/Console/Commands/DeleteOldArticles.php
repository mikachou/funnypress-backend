<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Carbon\Carbon;

class DeleteOldArticles extends Command
{
    protected $signature = 'articles:delete-old {--date=} {--days=}';
    protected $description = 'Delete articles older than a certain date or a number of days.';

    public function handle()
    {
        $date = $this->option('date');
        $days = $this->option('days');

        if ($date && $days) {
            $this->error('Please provide either a date or a number of days, not both.');
            return;
        }

        if (!$date && !$days) {
            $this->error('Please provide a date or a number of days.');
            return;
        }

        $cutoffDate = null;

        if ($date) {
            try {
                $cutoffDate = Carbon::parse($date)->endOfDay();
            } catch (\Exception $e) {
                $this->error('Invalid date format. Please use a recognizable date format (e.g., YYYY-MM-DD).');
                return;
            }
        } else { // days must be set
            if (!is_numeric($days) || $days < 0) {
                $this->error('The days parameter must be a positive number.');
                return;
            }
            $cutoffDate = Carbon::now()->subDays((int)$days)->startOfDay();
        }

        $this->info("Deleting articles published on or before {$cutoffDate->toDateString()}...");

        $deletedCount = Article::where('date', '<=', $cutoffDate)->delete();

        $this->info("Successfully deleted {$deletedCount} articles.");
    }
}
