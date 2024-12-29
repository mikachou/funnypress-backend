<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feed;
use Illuminate\Support\Facades\DB;

class ImportFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeds:import {file : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import feeds from a CSV file into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Check if the file exists
        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            return 1;
        }

        // Open the file for reading
        if (($handle = fopen($filePath, 'r')) === false) {
            $this->error("Unable to open the file {$filePath}.");
            return 1;
        }

        // Read the CSV header
        $header = fgetcsv($handle);

        if (!$header || !in_array('name', $header) || !in_array('url', $header)) {
            $this->error('Invalid CSV format. The file must have "name" and "url" columns.');
            fclose($handle);
            return 1;
        }

        $nameIndex = array_search('name', $header);
        $urlIndex = array_search('url', $header);

        $this->info('Starting the import...');

        // Begin a database transaction
        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $name = trim($row[$nameIndex]);
                $url = trim($row[$urlIndex]);

                // Skip empty rows
                if (empty($name) || empty($url)) {
                    continue;
                }

                // Check for duplicate based on the 'url' field only
                $existingFeed = Feed::where('url', $url)->first();
                if ($existingFeed) {
                    $this->info("Skipping duplicate URL: {$url}");
                    continue;
                }

                // Create a new feed
                Feed::create([
                    'name' => $name,
                    'url' => $url,
                ]);

                $this->info("Imported: Name={$name}, URL={$url}");
            }

            fclose($handle);

            // Commit the transaction
            DB::commit();

            $this->info('Import completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: {$e->getMessage()}");
            fclose($handle);
            return 1;
        }

        return 0;
    }
}
