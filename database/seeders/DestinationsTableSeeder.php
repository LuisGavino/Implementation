<?php

namespace Database\Seeders;

use App\Category;
use App\Destinations;
use App\Tag;
use Illuminate\Database\Seeder;

class DestinationsTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [];
        foreach ([
            'Family Getaways',
            'Weekend Escapes',
            'Beach Holidays',
            'Nature & Adventure',
            'Group Tours',
            'Cultural Experiences',
            'Road Trips',
            'Solo Travel',
            'Travel with Friends',
        ] as $name) {
            $categories[] = Category::firstOrCreate(['name' => $name]);
        }

        $tours = [
            [
                'pricing' => '₱18500',
                'title' => 'El Nido, Palawan',
                'description' => 'Explore turquoise lagoons, towering limestone cliffs, hidden beaches, and unforgettable island-hopping adventures in Palawan.',
                'content' => 'Discover the natural beauty of El Nido, Palawan, where dramatic limestone cliffs rise above clear blue water. Visit the Big Lagoon and Small Lagoon, relax on white-sand beaches, snorkel among colorful marine life, and enjoy fresh Filipino seafood. This package is ideal for travelers who want a mix of relaxation, scenery, and outdoor adventure. Package inclusions and boat schedules should be confirmed before travel.',
                'category_id' => 2,
                'image' => 'images/destination-1.jpg',
                'duration' => '4 Days / 3 Nights',
                'group_size' => '6-10 People',
                'tour_type' => 'Beach & Adventure',
                'tag_names' => ['Travel', 'Beach', 'Adventure'],
            ],
            [
                'pricing' => '₱12900',
                'title' => 'Boracay, Aklan',
                'description' => 'Enjoy powdery white sand, spectacular sunsets, island activities, and the lively coastal atmosphere of Boracay.',
                'content' => 'Boracay is famous for White Beach, clear waters, and beautiful sunsets. Spend your days swimming, trying water activities, visiting nearby beaches, or enjoying a relaxed walk along the shore. In the evening, sample Filipino favorites at local restaurants and watch the sky change colors over the sea. This tour offers a flexible island escape for couples, families, and groups of friends.',
                'category_id' => 3,
                'image' => 'images/destination-2.jpg',
                'duration' => '3 Days / 2 Nights',
                'group_size' => '8-12 People',
                'tour_type' => 'Beach & Culture',
                'tag_names' => ['Travel', 'Beach'],
            ],
            [
                'pricing' => '₱15800',
                'title' => 'Siargao Island, Surigao del Norte',
                'description' => 'Discover surf breaks, palm-lined roads, rock pools, and laid-back island life in the surfing capital of the Philippines.',
                'content' => 'Siargao combines a relaxed island atmosphere with outdoor adventure. Visit the famous Cloud 9 surf area, explore the Magpupungko rock pools when conditions allow, cruise through palm-lined countryside, and take an island-hopping trip to nearby beaches. Enjoy local seafood and the warm hospitality of island communities. Weather, tides, and sea conditions can affect activities, so itineraries should remain flexible.',
                'category_id' => 4,
                'image' => 'images/destination-3.jpg',
                'duration' => '4 Days / 3 Nights',
                'group_size' => '6-10 People',
                'tour_type' => 'Beach & Adventure',
                'tag_names' => ['Adventure', 'Beach'],
            ],
            [
                'pricing' => '₱9900',
                'title' => 'Bohol Countryside',
                'description' => 'See the Chocolate Hills, meet the tiny Philippine tarsier at an accredited sanctuary, and enjoy river and countryside scenery.',
                'content' => 'Explore Bohol beyond its beaches with a countryside journey through rolling green landscapes and iconic geological formations. Visit the Chocolate Hills viewpoint, learn about local history and culture, and choose responsible wildlife experiences at an accredited tarsier sanctuary. Depending on your itinerary, you may also enjoy a Loboc River cruise and discover local crafts and Filipino cuisine.',
                'category_id' => 6,
                'image' => 'images/destination-4.jpg',
                'duration' => '3 Days / 2 Nights',
                'group_size' => '8-12 People',
                'tour_type' => 'Cultural & Sightseeing',
                'tag_names' => ['Travel', 'Adventure'],
            ],
            [
                'pricing' => '₱11200',
                'title' => 'Cebu Island Escape',
                'description' => 'Combine heritage landmarks, local food, waterfalls, and beautiful coastal scenery on a Cebu getaway.',
                'content' => 'Cebu offers a rich blend of history, food, and nature. Explore heritage sites in Cebu City, try local specialties such as puso and Cebu lechon, and plan a day trip to waterfalls or nearby coastal areas based on your interests and current local conditions. The island is a great choice for travelers looking for a varied itinerary with both cultural stops and outdoor experiences.',
                'category_id' => 1,
                'image' => 'images/destination-5.jpg',
                'duration' => '4 Days / 3 Nights',
                'group_size' => '10-15 People',
                'tour_type' => 'Cultural & Sightseeing',
                'tag_names' => ['Travel', 'Adventure'],
            ],
            [
                'pricing' => '₱8500',
                'title' => 'Baguio & the Cordilleras',
                'description' => 'Enjoy cool mountain air, pine-covered roads, local markets, and the cultural heritage of the Cordillera region.',
                'content' => 'Escape to Baguio and the Cordilleras for cooler weather, mountain views, local markets, gardens, and regional cuisine. Explore the city at a relaxed pace, discover local crafts, and consider guided excursions to nearby highland communities. Visitors should travel respectfully, follow local guidance, and check road and weather conditions when planning mountain trips.',
                'category_id' => 7,
                'image' => 'images/destination-6.jpg',
                'duration' => '3 Days / 2 Nights',
                'group_size' => '6-10 People',
                'tour_type' => 'Nature & Sightseeing',
                'tag_names' => ['Travel', 'Adventure'],
            ],
        ];

        $tagModels = [];
        foreach (['Travel', 'Beach', 'Adventure'] as $tagName) {
            $tagModels[$tagName] = Tag::firstOrCreate(['name' => $tagName]);
        }

        foreach ($tours as $tour) {
            $tagNames = $tour['tag_names'];
            unset($tour['tag_names']);
            $tour['category_id'] = $categories[$tour['category_id'] - 1]->id;
            $tour['published_at'] = now();

            $destination = Destinations::updateOrCreate(
                ['title' => $tour['title']],
                $tour
            );

            $destination->tags()->sync(
                collect($tagNames)->map(fn ($name) => $tagModels[$name]->id)->all()
            );
        }
    }
}
