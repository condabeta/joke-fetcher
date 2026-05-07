<?php

namespace App\Console\Commands;

use App\Models\Joke;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchJokesCommand extends Command
{
    protected $signature = 'jokes:fetch';
    protected $description = 'Fetch random joke and save to DB';

    public function handle()
    {
        $this->info('Fetching joke...');

        try {
            $response = Http::get('https://official-joke-api.appspot.com/random_joke');

            if ($response->successful()) {
                $data = $response->json();

                Joke::updateOrCreate(
                    ['joke_id' => $data['id']],
                    [
                        'setup' => $data['setup'],
                        'punchline' => $data['punchline'],
                        'type' => $data['type'],
                    ]
                );

                $this->info('✅ Joke saved successfully!');
            }
        } catch (\Exception $e) {
            $this->error('Failed to fetch joke: ' . $e->getMessage());
        }
    }
}