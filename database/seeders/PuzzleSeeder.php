<?php

namespace Database\Seeders;

use App\Models\Puzzle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PuzzleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Array of Ohio-related items
        $ohioItems = [
            [
                'answer' => 'Cincinnati',
                'word_count' => 1,
                'image_path' => 'puzzles/cincinnati.jpg',
                'category' => 'place',
                'difficulty' => 'easy',
                'hint' => 'A major city on the Ohio River',
            ],
            [
                'answer' => 'The Shoe',
                'word_count' => 2,
                'image_path' => 'puzzles/the_shoe.jpg',
                'category' => 'place',
                'difficulty' => 'medium',
                'hint' => 'A famous football stadium',
            ],
            [
                'answer' => 'The Black Keys',
                'word_count' => 3,
                'image_path' => 'puzzles/black_keys.jpg',
                'category' => 'music',
                'difficulty' => 'medium',
                'hint' => 'Rock duo from Akron',
            ],
            [
                'answer' => 'Buckeye',
                'word_count' => 1,
                'image_path' => 'puzzles/buckeye.jpg',
                'category' => 'thing',
                'difficulty' => 'easy',
                'hint' => 'State tree and mascot',
            ],
            [
                'answer' => 'Cedar Point',
                'word_count' => 2,
                'image_path' => 'puzzles/cedar_point.jpg',
                'category' => 'place',
                'difficulty' => 'easy',
                'hint' => 'Famous amusement park',
            ],
            [
                'answer' => 'Rock and Roll Hall of Fame',
                'word_count' => 5,
                'image_path' => 'puzzles/rock_hall.jpg',
                'category' => 'place',
                'difficulty' => 'medium',
                'hint' => 'Cleveland music institution',
            ],
            [
                'answer' => 'LeBron James',
                'word_count' => 2,
                'image_path' => 'puzzles/lebron.jpg',
                'category' => 'person',
                'difficulty' => 'easy',
                'hint' => 'Akron-born basketball legend',
            ],
            [
                'answer' => 'John Glenn',
                'word_count' => 2,
                'image_path' => 'puzzles/john_glenn.jpg',
                'category' => 'person',
                'difficulty' => 'medium',
                'hint' => 'Astronaut and senator',
            ],
            [
                'answer' => 'Cuyahoga Valley',
                'word_count' => 2,
                'image_path' => 'puzzles/cuyahoga_valley.jpg',
                'category' => 'place',
                'difficulty' => 'medium',
                'hint' => 'Ohio\'s only national park',
            ],
            [
                'answer' => 'Wendy\'s',
                'word_count' => 1,
                'image_path' => 'puzzles/wendys.jpg',
                'category' => 'business',
                'difficulty' => 'easy',
                'hint' => 'Fast food chain founded in Columbus',
            ],
            [
                'answer' => 'Hocking Hills',
                'word_count' => 2,
                'image_path' => 'puzzles/hocking_hills.jpg',
                'category' => 'place',
                'difficulty' => 'medium',
                'hint' => 'Popular state park with caves and waterfalls',
            ],
            [
                'answer' => 'Wright Brothers',
                'word_count' => 2,
                'image_path' => 'puzzles/wright_brothers.jpg',
                'category' => 'person',
                'difficulty' => 'easy',
                'hint' => 'Aviation pioneers from Dayton',
            ],
            [
                'answer' => 'Drew Carey',
                'word_count' => 2,
                'image_path' => 'puzzles/drew_carey.jpg',
                'category' => 'person',
                'difficulty' => 'easy',
                'hint' => 'Cleveland-born comedian and TV host',
            ],
            [
                'answer' => 'The Ohio State University',
                'word_count' => 4,
                'image_path' => 'puzzles/osu.jpg',
                'category' => 'place',
                'difficulty' => 'easy',
                'hint' => 'The largest university in the state',
            ],
            [
                'answer' => 'Goodyear',
                'word_count' => 1,
                'image_path' => 'puzzles/goodyear.jpg',
                'category' => 'business',
                'difficulty' => 'easy',
                'hint' => 'Tire company headquartered in Akron',
            ],
            [
                'answer' => 'Lake Erie',
                'word_count' => 2,
                'image_path' => 'puzzles/lake_erie.jpg',
                'category' => 'place',
                'difficulty' => 'easy',
                'hint' => 'One of the Great Lakes bordering Ohio',
            ],
            [
                'answer' => 'Neil Armstrong',
                'word_count' => 2,
                'image_path' => 'puzzles/neil_armstrong.jpg',
                'category' => 'person',
                'difficulty' => 'easy',
                'hint' => 'First person to walk on the moon, born in Wapakoneta',
            ],
            [
                'answer' => 'Cleveland Orchestra',
                'word_count' => 2,
                'image_path' => 'puzzles/cleveland_orchestra.jpg',
                'category' => 'organization',
                'difficulty' => 'medium',
                'hint' => 'World-renowned musical ensemble',
            ],
            [
                'answer' => 'Toni Morrison',
                'word_count' => 2,
                'image_path' => 'puzzles/toni_morrison.jpg',
                'category' => 'person',
                'difficulty' => 'medium',
                'hint' => 'Nobel Prize-winning author from Lorain',
            ],
            [
                'answer' => 'Serpent Mound',
                'word_count' => 2,
                'image_path' => 'puzzles/serpent_mound.jpg',
                'category' => 'place',
                'difficulty' => 'hard',
                'hint' => 'Ancient earthwork in Adams County',
            ],
            [
                'answer' => 'Jungle Jim\'s',
                'word_count' => 2,
                'image_path' => 'puzzles/jungle_jims.jpg',
                'category' => 'business',
                'difficulty' => 'medium',
                'hint' => 'Enormous international grocery store',
            ],
            [
                'answer' => 'Jack Nicklaus',
                'word_count' => 2,
                'image_path' => 'puzzles/jack_nicklaus.jpg',
                'category' => 'person',
                'difficulty' => 'medium',
                'hint' => 'Golf legend from Columbus',
            ],
            [
                'answer' => 'Ohio River',
                'word_count' => 2,
                'image_path' => 'puzzles/ohio_river.jpg',
                'category' => 'place',
                'difficulty' => 'easy',
                'hint' => 'Forms the southern border of the state',
            ],
            [
                'answer' => 'Amish Country',
                'word_count' => 2,
                'image_path' => 'puzzles/amish_country.jpg',
                'category' => 'place',
                'difficulty' => 'easy',
                'hint' => 'Region in northeast Ohio known for traditional farming communities',
            ],
            [
                'answer' => 'Kroger',
                'word_count' => 1,
                'image_path' => 'puzzles/kroger.jpg',
                'category' => 'business',
                'difficulty' => 'easy',
                'hint' => 'Supermarket chain founded in Cincinnati',
            ],
            [
                'answer' => 'Cincinnati Reds',
                'word_count' => 2,
                'image_path' => 'puzzles/reds.jpg',
                'category' => 'sports',
                'difficulty' => 'easy',
                'hint' => 'Oldest professional baseball team',
            ],
            [
                'answer' => 'Cleveland Browns',
                'word_count' => 2,
                'image_path' => 'puzzles/browns.jpg',
                'category' => 'sports',
                'difficulty' => 'easy',
                'hint' => 'NFL team with loyal but long-suffering fans',
            ],
        ];

        // Get today's date
        $today = Carbon::today();

        // Add these items as puzzles with consecutive publish dates starting from today
        foreach ($ohioItems as $index => $item) {
            // Publish date is today + $index days
            $publishDate = (clone $today)->addDays($index);

            Puzzle::create([
                'publish_date' => $publishDate,
                'answer' => $item['answer'],
                'word_count' => $item['word_count'],
                'image_path' => $item['image_path'],
                'category' => $item['category'],
                'difficulty' => $item['difficulty'],
                'hint' => $item['hint'],
            ]);
        }
    }
}
