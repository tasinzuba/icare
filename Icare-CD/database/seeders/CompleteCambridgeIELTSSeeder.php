<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestSection;
use App\Models\TestSet;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\TestPartAudio;
use Illuminate\Support\Facades\DB;

class CompleteCambridgeIELTSSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // Create test sections
            $sections = $this->createTestSections();
            
            // Create Cambridge IELTS 11 Test 1
            $this->createCambridgeTest1($sections);
        });
    }

    private function createTestSections()
    {
        $sections = [];
        
        $sectionData = [
            ['name' => 'listening', 'description' => 'IELTS Listening Test', 'time_limit' => 30],
            ['name' => 'reading', 'description' => 'IELTS Academic Reading Test', 'time_limit' => 60],
            ['name' => 'writing', 'description' => 'IELTS Academic Writing Test', 'time_limit' => 60],
            ['name' => 'speaking', 'description' => 'IELTS Speaking Test', 'time_limit' => 14]
        ];

        foreach ($sectionData as $data) {
            $sections[$data['name']] = TestSection::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        return $sections;
    }

    private function createCambridgeTest1($sections)
    {
        // Create test sets for each section
        $listeningSet = TestSet::create([
            'title' => 'Cambridge IELTS 11 - Test 1 - Listening',
            'section_id' => $sections['listening']->id,
            'active' => true
        ]);

        $readingSet = TestSet::create([
            'title' => 'Cambridge IELTS 11 - Test 1 - Academic Reading',
            'section_id' => $sections['reading']->id,
            'active' => true
        ]);

        $writingSet = TestSet::create([
            'title' => 'Cambridge IELTS 11 - Test 1 - Academic Writing',
            'section_id' => $sections['writing']->id,
            'active' => true
        ]);

        $speakingSet = TestSet::create([
            'title' => 'Cambridge IELTS 11 - Test 1 - Speaking',
            'section_id' => $sections['speaking']->id,
            'active' => true
        ]);

        // Seed questions for each section
        $this->seedListeningQuestions($listeningSet);
        $this->seedReadingQuestions($readingSet);
        $this->seedWritingQuestions($writingSet);
        $this->seedSpeakingQuestions($speakingSet);
    }

    private function seedListeningQuestions($testSet)
    {
        // PART 1: Conversation about holiday rental (Questions 1-10)
        $orderNumber = 1;

        // Questions 1-5: Form Completion
        $formQuestion = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'form_completion',
            'content' => 'Complete the form below. Write NO MORE THAN TWO WORDS AND/OR A NUMBER for each answer.',
            'instructions' => 'Holiday Rental Enquiry Form',
            'order_number' => $orderNumber,
            'part_number' => 1,
            'marks' => 5,
            'form_structure' => [
                'title' => 'Holiday Rental Enquiry',
                'fields' => [
                    ['label' => 'Name', 'blank_id' => 1, 'answer' => 'Andrea Brown'],
                    ['label' => 'Address', 'blank_id' => 2, 'answer' => '24 Ardleigh Green Road'],
                    ['label' => 'Postcode', 'blank_id' => 3, 'answer' => 'N12 9FG'],
                    ['label' => 'Phone number (home)', 'blank_id' => 4, 'answer' => '020 8994 5762'],
                    ['label' => 'Dates required', 'blank_id' => 5, 'answer' => '27th June']
                ]
            ],
            'audio_transcript' => "Agent: Good morning, Burnham Holidays. How can I help you?\nWoman: Oh, good morning. I'm interested in renting a holiday cottage...[full transcript]",
            'use_part_audio' => true
        ]);
        $orderNumber += 5;

        // Questions 6-10: Multiple Choice
        $mcQuestions = [
            [
                'content' => 'The woman wants to rent a cottage for',
                'correct' => 'A',
                'options' => [
                    'A' => 'a family holiday',
                    'B' => 'a business trip',
                    'C' => 'a romantic getaway'
                ]
            ],
            [
                'content' => 'The cottage in Devon is',
                'correct' => 'B',
                'options' => [
                    'A' => 'by the sea',
                    'B' => 'in the countryside',
                    'C' => 'in a town'
                ]
            ],
            [
                'content' => 'The woman decides NOT to take the cottage in Somerset because',
                'correct' => 'C',
                'options' => [
                    'A' => 'it doesn\'t have a garden',
                    'B' => 'it\'s too expensive',
                    'C' => 'it\'s too far from the beach'
                ]
            ],
            [
                'content' => 'The woman chooses the cottage in',
                'correct' => 'A',
                'options' => [
                    'A' => 'Cornwall',
                    'B' => 'Devon',
                    'C' => 'Dorset'
                ]
            ],
            [
                'content' => 'The deposit for the cottage is',
                'correct' => 'B',
                'options' => [
                    'A' => '£150',
                    'B' => '£200',
                    'C' => '£250'
                ]
            ]
        ];

        foreach ($mcQuestions as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'multiple_choice',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 1,
                'marks' => 1,
                'use_part_audio' => true
            ]);

            foreach ($q['options'] as $letter => $content) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $letter . ') ' . $content,
                    'is_correct' => ($letter === $q['correct'])
                ]);
            }
        }

        // PART 2: Talk about city transport (Questions 11-20)
        // Questions 11-15: Matching
        $matchingQuestion = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'matching',
            'content' => 'Which form of transport does the speaker recommend for each situation?',
            'instructions' => 'Choose FIVE answers from the box and write the correct letter, A-H, next to questions 11-15.',
            'order_number' => $orderNumber,
            'part_number' => 2,
            'marks' => 5,
            'matching_pairs' => [
                ['left' => 'travelling late at night', 'right' => 'taxi'],
                ['left' => 'going to the airport', 'right' => 'shuttle bus'],
                ['left' => 'visiting the zoo', 'right' => 'tram'],
                ['left' => 'going shopping in the city centre', 'right' => 'bus'],
                ['left' => 'travelling with children', 'right' => 'car']
            ],
            'use_part_audio' => true
        ]);
        $orderNumber += 5;

        // Questions 16-20: Note Completion
        $noteQuestion = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'note_completion',
            'content' => 'Complete the notes below. Write NO MORE THAN TWO WORDS for each answer.',
            'instructions' => 'City Transport Costs',
            'order_number' => $orderNumber,
            'part_number' => 2,
            'marks' => 5,
            'section_specific_data' => [
                'note_template' => "Daily bus pass: £(16)_____ \nWeekly tram pass: £(17)_____ \nParking in city centre: £(18)_____ per hour\nTaxi to airport: approximately £(19)_____ \nBike rental: £15 per (20)_____",
                'answers' => ['4.50', '21', '3.50', '45', 'day']
            ],
            'use_part_audio' => true
        ]);
        $orderNumber += 5;

        // PART 3: Discussion about assignment (Questions 21-30)
        // Questions 21-25: Multiple Choice
        $part3MCQuestions = [
            [
                'content' => 'What is the main topic of the assignment?',
                'correct' => 'B',
                'options' => [
                    'A' => 'The history of television',
                    'B' => 'The impact of television on society',
                    'C' => 'The future of television technology'
                ]
            ],
            [
                'content' => 'The students agree that television',
                'correct' => 'A',
                'options' => [
                    'A' => 'has both positive and negative effects',
                    'B' => 'is mainly harmful to society',
                    'C' => 'is becoming less important'
                ]
            ],
            [
                'content' => 'What do they decide to include in their introduction?',
                'correct' => 'C',
                'options' => [
                    'A' => 'Statistics about TV ownership',
                    'B' => 'A quote from an expert',
                    'C' => 'A definition of television'
                ]
            ],
            [
                'content' => 'For their research, they will',
                'correct' => 'B',
                'options' => [
                    'A' => 'only use academic journals',
                    'B' => 'use a variety of sources',
                    'C' => 'focus on online resources'
                ]
            ],
            [
                'content' => 'They plan to submit their assignment',
                'correct' => 'A',
                'options' => [
                    'A' => 'one week before the deadline',
                    'B' => 'on the day of the deadline',
                    'C' => 'after getting an extension'
                ]
            ]
        ];

        foreach ($part3MCQuestions as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'multiple_choice',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 3,
                'marks' => 1,
                'use_part_audio' => true
            ]);

            foreach ($q['options'] as $letter => $content) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $letter . ') ' . $content,
                    'is_correct' => ($letter === $q['correct'])
                ]);
            }
        }

        // Questions 26-30: Summary Completion
        $summaryQuestion = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'sentence_completion',
            'content' => 'Complete the summary using the list of words, A-I, below.',
            'instructions' => 'Television Assignment Plan',
            'order_number' => $orderNumber,
            'part_number' => 3,
            'marks' => 5,
            'section_specific_data' => [
                'summary_text' => "The students are working on an assignment about television. They will examine both the (26)_____ and negative aspects of TV. Their research will include looking at the (27)_____ effects on children, as well as TV's role in (28)_____. They plan to use (29)_____ from various sources to support their arguments. The assignment should be approximately (30)_____ words long.",
                'word_bank' => ['A) positive', 'B) psychological', 'C) education', 'D) evidence', 'E) 3000', 'F) harmful', 'G) physical', 'H) entertainment', 'I) 2000'],
                'answers' => ['A', 'B', 'C', 'D', 'E']
            ],
            'use_part_audio' => true
        ]);
        $orderNumber += 5;

        // PART 4: Lecture about renewable energy (Questions 31-40)
        // Questions 31-35: Sentence Completion
        $sentenceQuestions = [
            ['content' => 'Solar panels work best in countries that have a lot of _____', 'answer' => 'sunshine'],
            ['content' => 'Wind turbines are most effective when placed _____', 'answer' => 'offshore'],
            ['content' => 'Hydroelectric power requires the construction of _____', 'answer' => 'dams'],
            ['content' => 'Geothermal energy uses heat from _____', 'answer' => 'underground'],
            ['content' => 'Biomass energy comes from _____ materials', 'answer' => 'organic']
        ];

        foreach ($sentenceQuestions as $q) {
            Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'sentence_completion',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 4,
                'marks' => 1,
                'section_specific_data' => ['correct_answer' => $q['answer']],
                'use_part_audio' => true
            ]);
        }

        // Questions 36-40: Multiple Choice
        $part4MCQuestions = [
            [
                'content' => 'What is the main advantage of renewable energy?',
                'correct' => 'C',
                'options' => [
                    'A' => 'It is cheaper than fossil fuels',
                    'B' => 'It is easier to produce',
                    'C' => 'It doesn\'t produce greenhouse gases'
                ]
            ],
            [
                'content' => 'The lecturer mentions that solar panels',
                'correct' => 'B',
                'options' => [
                    'A' => 'are becoming less expensive',
                    'B' => 'have improved in efficiency',
                    'C' => 'can work without sunlight'
                ]
            ],
            [
                'content' => 'According to the lecture, the main challenge for wind power is',
                'correct' => 'A',
                'options' => [
                    'A' => 'inconsistent wind speeds',
                    'B' => 'high maintenance costs',
                    'C' => 'noise pollution'
                ]
            ],
            [
                'content' => 'The lecturer suggests that in the future',
                'correct' => 'C',
                'options' => [
                    'A' => 'all energy will be renewable',
                    'B' => 'fossil fuels will still be important',
                    'C' => 'a mix of energy sources will be used'
                ]
            ],
            [
                'content' => 'The lecture concludes that renewable energy',
                'correct' => 'B',
                'options' => [
                    'A' => 'is not yet practical',
                    'B' => 'requires government support',
                    'C' => 'will replace all other energy sources'
                ]
            ]
        ];

        foreach ($part4MCQuestions as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'multiple_choice',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 4,
                'marks' => 1,
                'use_part_audio' => true
            ]);

            foreach ($q['options'] as $letter => $content) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $letter . ') ' . $content,
                    'is_correct' => ($letter === $q['correct'])
                ]);
            }
        }
    }

    private function seedReadingQuestions($testSet)
    {
        // PASSAGE 1: The History of the Bicycle
        $passage1 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'passage',
            'content' => 'Reading Passage 1', // Add content field
            'part_number' => 1,
            'order_number' => 0,
            'passage_text' => "The History of the Bicycle

The bicycle as we know it today has gone through many transformations since its inception. The first verifiable claim for a practically used bicycle belongs to German Baron Karl von Drais, who invented his Laufmaschine (running machine) in 1817, which was later called the draisine or hobby horse. It was a two-wheeled vehicle that the rider propelled by pushing their feet against the ground.

The next major development came from Scottish blacksmith Kirkpatrick Macmillan, who is credited with creating the first mechanically propelled two-wheel vehicle in 1839. His machine had pedals attached to the rear wheel by connecting rods. However, it was Pierre Michaux, a French blacksmith, who first mass-produced the pedal-driven bicycle in the 1860s. His velocipede, nicknamed the 'boneshaker' due to its uncomfortable ride on the cobblestone streets of the day, featured pedals attached directly to the front wheel.

The 1870s saw the introduction of the high-wheel bicycle, also known as the penny-farthing. With its large front wheel and small rear wheel, it allowed greater speeds but was dangerous to ride. The rider sat high above the center of gravity, and any sudden stop could send them flying headfirst over the handlebars – a phenomenon known as 'taking a header.'

Safety became a primary concern, leading to the development of the 'safety bicycle' by John Kemp Starley in 1885. This design featured two similarly sized wheels, a chain drive to the rear wheel, and a lower center of gravity. This basic design remains the standard for bicycles today. The addition of pneumatic tires by John Boyd Dunlop in 1888 greatly improved comfort and performance.

The 20th century brought further refinements: multiple gears, lightweight materials, and specialized designs for different purposes. The bicycle had evolved from a curious novelty to a practical means of transportation, recreation, and sport. Today, with growing environmental concerns and urban congestion, the bicycle is experiencing a renaissance as cities worldwide invest in cycling infrastructure and promote bicycle use as a sustainable transport solution.

Modern innovations continue to transform cycling. Electric bicycles extend the range and accessibility of cycling to more people. Smart technology integration provides navigation, fitness tracking, and safety features. Advanced materials like carbon fiber create incredibly light yet strong frames. The humble bicycle, nearly 200 years after its invention, continues to evolve and adapt to meet the changing needs of society."
        ]);

        $orderNumber = 1;

        // Questions 1-5: True/False/Not Given
        $tfngQuestions = [
            ['content' => 'Baron Karl von Drais invented the first pedal-powered bicycle.', 'answer' => 'False'],
            ['content' => 'The velocipede was uncomfortable to ride on rough roads.', 'answer' => 'True'],
            ['content' => 'The penny-farthing was safer than previous bicycle designs.', 'answer' => 'False'],
            ['content' => 'John Kemp Starley\'s safety bicycle is the basis for modern bicycle design.', 'answer' => 'True'],
            ['content' => 'Pneumatic tires were invented in the 20th century.', 'answer' => 'False']
        ];

        foreach ($tfngQuestions as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'true_false',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 1,
                'marks' => 1,
                'passage_id' => $passage1->id
            ]);

            $options = ['True', 'False', 'Not Given'];
            foreach ($options as $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $option,
                    'is_correct' => ($option === $q['answer'])
                ]);
            }
        }

        // Questions 6-9: Summary Completion
        $summaryQuestion = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'summary_completion',
            'content' => 'Complete the summary below using words from the passage. Use NO MORE THAN TWO WORDS for each answer.',
            'instructions' => 'The Evolution of the Bicycle',
            'order_number' => $orderNumber,
            'part_number' => 1,
            'marks' => 4,
            'passage_id' => $passage1->id,
            'section_specific_data' => [
                'summary_text' => "The first bicycle-like invention was the (6)_____, created by Baron Karl von Drais in 1817. It required riders to push their feet against the ground. Later, Pierre Michaux mass-produced the velocipede, which had pedals attached to the (7)_____. The high-wheel bicycle of the 1870s was fast but dangerous, as riders could experience \'taking a (8)_____\' if they stopped suddenly. The modern bicycle design emerged with Starley\'s (9)_____ bicycle in 1885.",
                'answers' => ['Laufmaschine', 'front wheel', 'header', 'safety']
            ]
        ]);
        $orderNumber += 4;

        // Questions 10-13: Matching Headings
        $headingQuestion = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'matching_headings',
            'content' => 'Match each paragraph with the correct heading.',
            'order_number' => $orderNumber,
            'part_number' => 1,
            'marks' => 4,
            'passage_id' => $passage1->id
        ]);

        $headings = [
            'i' => 'The dangerous penny-farthing era',
            'ii' => 'Modern innovations in cycling',
            'iii' => 'The first human-powered vehicles',
            'iv' => 'Mass production begins',
            'v' => 'Creating a safer design',
            'vi' => 'The bicycle\'s role in modern society',
            'vii' => 'Adding comfort to cycling'
        ];

        $correctMatches = [
            'Paragraph 2' => 'iii',
            'Paragraph 3' => 'iv',
            'Paragraph 4' => 'i',
            'Paragraph 5' => 'v'
        ];

        foreach ($headings as $num => $heading) {
            QuestionOption::create([
                'question_id' => $headingQuestion->id,
                'content' => $num . '. ' . $heading,
                'is_correct' => false
            ]);
        }
        $orderNumber += 4;

        // PASSAGE 2: Climate Change and Agriculture
        $passage2 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'passage',
            'content' => 'Reading Passage 2', // Add content field
            'part_number' => 2,
            'order_number' => 0,
            'passage_text' => "Climate Change and Agriculture: Challenges and Adaptations

Agriculture is both a victim of and contributor to climate change. As global temperatures rise and weather patterns become increasingly unpredictable, farmers worldwide face unprecedented challenges in maintaining crop yields and food security. Simultaneously, agricultural practices contribute approximately 24% of global greenhouse gas emissions, making the sector a significant player in climate change mitigation efforts.

The impacts of climate change on agriculture are multifaceted and vary by region. Rising temperatures affect crop growth cycles, with some regions experiencing longer growing seasons while others face heat stress that reduces productivity. Changing precipitation patterns create both droughts and floods, often in the same regions but at different times. These extremes damage crops, erode topsoil, and disrupt planting and harvesting schedules.

Farmers are adapting through various strategies. Crop diversification helps spread risk across different species with varying climate tolerances. Many are shifting planting dates to align with new weather patterns or switching to drought-resistant varieties. Water conservation techniques, including drip irrigation and rainwater harvesting, are becoming essential in water-scarce regions. Some farmers are adopting agroforestry, integrating trees into crop and livestock systems to provide shade, prevent erosion, and sequester carbon.

Technology plays an increasingly important role in climate adaptation. Precision agriculture uses sensors, GPS, and data analytics to optimize resource use and maximize yields. Drought-resistant crop varieties developed through traditional breeding and genetic modification offer hope for maintaining productivity in challenging conditions. Weather forecasting and early warning systems help farmers make informed decisions about planting, harvesting, and protecting crops from extreme events.

Policy interventions are crucial for supporting agricultural adaptation. Governments are implementing crop insurance schemes to protect farmers from climate-related losses. Investment in rural infrastructure, particularly irrigation and storage facilities, helps build resilience. Research funding for climate-smart agriculture techniques and knowledge-sharing platforms enables farmers to learn from successful adaptations elsewhere.

The agriculture sector must balance adaptation with mitigation. Sustainable intensification – producing more food with less environmental impact – is key. This includes reducing fertilizer use, improving livestock management, and protecting carbon-storing ecosystems like forests and wetlands. Regenerative agriculture practices that build soil health can both improve resilience and sequester carbon.

International cooperation is essential, as climate change and food security are global challenges. Technology transfer, financial support for developing countries, and coordinated research efforts can accelerate adaptation and mitigation. The transformation of agriculture in response to climate change represents one of humanity's greatest challenges, but also an opportunity to create more sustainable and resilient food systems for future generations."
        ]);

        // Questions 14-18: Multiple Choice
        $mcQuestions2 = [
            [
                'content' => 'According to the passage, agriculture contributes what percentage of global greenhouse gas emissions?',
                'correct' => 'B',
                'options' => [
                    'A' => 'Approximately 14%',
                    'B' => 'Approximately 24%',
                    'C' => 'Approximately 34%',
                    'D' => 'Approximately 44%'
                ]
            ],
            [
                'content' => 'Which of the following is NOT mentioned as an impact of climate change on agriculture?',
                'correct' => 'D',
                'options' => [
                    'A' => 'Changes in crop growth cycles',
                    'B' => 'Soil erosion',
                    'C' => 'Disrupted planting schedules',
                    'D' => 'Increased pest resistance'
                ]
            ],
            [
                'content' => 'What does the passage suggest about agroforestry?',
                'correct' => 'C',
                'options' => [
                    'A' => 'It is mainly used in developed countries',
                    'B' => 'It reduces crop yields',
                    'C' => 'It helps sequester carbon',
                    'D' => 'It requires significant investment'
                ]
            ],
            [
                'content' => 'The term "sustainable intensification" in the passage refers to:',
                'correct' => 'A',
                'options' => [
                    'A' => 'Producing more food with less environmental impact',
                    'B' => 'Using more fertilizers to increase yields',
                    'C' => 'Expanding agricultural land',
                    'D' => 'Focusing on single crop production'
                ]
            ],
            [
                'content' => 'According to the passage, what is essential for addressing climate change and food security?',
                'correct' => 'B',
                'options' => [
                    'A' => 'Focusing on national solutions',
                    'B' => 'International cooperation',
                    'C' => 'Reducing agricultural production',
                    'D' => 'Eliminating traditional farming methods'
                ]
            ]
        ];

        foreach ($mcQuestions2 as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'multiple_choice',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 2,
                'marks' => 1,
                'passage_id' => $passage2->id
            ]);

            foreach ($q['options'] as $letter => $content) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $letter . ') ' . $content,
                    'is_correct' => ($letter === $q['correct'])
                ]);
            }
        }

        // Questions 19-23: Yes/No/Not Given
        $ynngQuestions = [
            ['content' => 'The author believes that agriculture is solely responsible for climate change.', 'answer' => 'No'],
            ['content' => 'Some regions may benefit from longer growing seasons due to climate change.', 'answer' => 'Yes'],
            ['content' => 'Precision agriculture is too expensive for most farmers to implement.', 'answer' => 'Not Given'],
            ['content' => 'Government support is necessary for agricultural adaptation to climate change.', 'answer' => 'Yes'],
            ['content' => 'Regenerative agriculture practices always increase crop yields.', 'answer' => 'Not Given']
        ];

        foreach ($ynngQuestions as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'yes_no',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 2,
                'marks' => 1,
                'passage_id' => $passage2->id
            ]);

            $options = ['Yes', 'No', 'Not Given'];
            foreach ($options as $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $option,
                    'is_correct' => ($option === $q['answer'])
                ]);
            }
        }

        // Questions 24-26: Sentence Completion
        $sentenceQuestions = [
            ['content' => 'Farmers are using _____ to spread risk across different species.', 'answer' => 'crop diversification'],
            ['content' => 'Weather forecasting helps farmers make informed decisions about _____.', 'answer' => 'planting, harvesting, and protecting crops'],
            ['content' => 'Technology transfer and financial support can help _____ adaptation efforts.', 'answer' => 'accelerate']
        ];

        foreach ($sentenceQuestions as $q) {
            Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'sentence_completion',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 2,
                'marks' => 1,
                'passage_id' => $passage2->id,
                'section_specific_data' => ['correct_answer' => $q['answer']]
            ]);
        }

        // PASSAGE 3: The Psychology of Decision Making
        $passage3 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'passage',
            'content' => 'Reading Passage 3', // Add content field
            'part_number' => 3,
            'order_number' => 0,
            'passage_text' => "The Psychology of Decision Making: Understanding How We Choose

Human decision-making is a complex process influenced by cognitive, emotional, and social factors. Traditional economic theory assumed that people make rational decisions based on careful analysis of available information and selection of options that maximize their benefit. However, psychological research has revealed that human decision-making is far from purely rational, often influenced by biases, emotions, and mental shortcuts known as heuristics.

Cognitive biases significantly impact our choices. Confirmation bias leads us to seek information that supports our existing beliefs while ignoring contradictory evidence. The availability heuristic causes us to overestimate the likelihood of events we can easily recall, often because they were recent or emotionally significant. Anchoring bias occurs when we rely too heavily on the first piece of information we receive, using it as a reference point for subsequent judgments.

Daniel Kahneman and Amos Tversky's groundbreaking work identified two systems of thinking that govern decision-making. System 1 operates automatically and quickly, with little conscious effort. It relies on intuition and emotion, making snap judgments based on pattern recognition and past experiences. System 2 is slower, more deliberative, and logical. It requires conscious effort and is used for complex calculations and careful analysis. While System 2 can override System 1, it requires significant mental energy, leading most daily decisions to be governed by the faster, more intuitive system.

Emotions play a crucial role in decision-making, contrary to the traditional view that they interfere with rational choice. The somatic marker hypothesis suggests that emotional reactions to potential outcomes guide decision-making by creating gut feelings about different options. Patients with damage to brain regions involved in processing emotions often make poor decisions despite intact logical reasoning abilities, highlighting the importance of emotional input in practical decision-making.

Social influences profoundly affect our choices. Social proof, the tendency to follow others' behavior, especially in uncertain situations, can lead to both beneficial and harmful outcomes. Authority bias causes us to give greater weight to opinions from perceived experts, sometimes without critical evaluation. Group dynamics can result in phenomena like groupthink, where desire for harmony overrides realistic appraisal of alternatives.

The paradox of choice presents another challenge. While having options is generally positive, too many choices can lead to decision paralysis, increased anxiety, and decreased satisfaction with eventual selections. This phenomenon, termed choice overload, suggests that beyond a certain point, additional options become counterproductive.

Understanding these psychological factors has practical applications. In medicine, awareness of cognitive biases helps doctors make better diagnostic decisions. Businesses use behavioral insights to design choice architectures that guide consumers toward mutually beneficial outcomes. Public policy increasingly incorporates nudge techniques based on decision-making psychology to promote behaviors like saving for retirement or reducing energy consumption.

Improving decision-making requires recognizing our cognitive limitations and biases. Strategies include seeking diverse perspectives to counter confirmation bias, using structured decision-making processes for important choices, and creating cooling-off periods for emotional decisions. While we cannot eliminate biases entirely, awareness of how our minds work enables us to make more informed and effective choices in our personal and professional lives."
        ]);

        // Questions 27-31: Matching Features
        $matchingFeatures = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'matching_features',
            'content' => 'Match each concept with its correct description.',
            'order_number' => $orderNumber,
            'part_number' => 3,
            'marks' => 5,
            'passage_id' => $passage3->id
        ]);

        $features = [
            'A' => 'Confirmation bias',
            'B' => 'Availability heuristic', 
            'C' => 'Anchoring bias',
            'D' => 'System 1 thinking',
            'E' => 'System 2 thinking',
            'F' => 'Somatic marker hypothesis',
            'G' => 'Social proof'
        ];

        $descriptions = [
            '27. Overestimating likelihood based on easily recalled events' => 'B',
            '28. Following others\' behavior in uncertain situations' => 'G',
            '29. Automatic, intuitive decision-making' => 'D',
            '30. Emotional reactions guiding choices' => 'F',
            '31. Seeking supportive information for existing beliefs' => 'A'
        ];

        foreach ($features as $letter => $feature) {
            QuestionOption::create([
                'question_id' => $matchingFeatures->id,
                'content' => $letter . ' - ' . $feature,
                'is_correct' => false
            ]);
        }
        $orderNumber += 5;

        // Questions 32-35: Multiple Choice  
        $mcQuestions3 = [
            [
                'content' => 'According to traditional economic theory, people make decisions by:',
                'correct' => 'A',
                'options' => [
                    'A' => 'Carefully analyzing all information to maximize benefit',
                    'B' => 'Following their emotional instincts',
                    'C' => 'Copying what others do',
                    'D' => 'Using mental shortcuts'
                ]
            ],
            [
                'content' => 'What does the passage say about System 2 thinking?',
                'correct' => 'C',
                'options' => [
                    'A' => 'It is used for most daily decisions',
                    'B' => 'It operates automatically',
                    'C' => 'It requires significant mental energy',
                    'D' => 'It is faster than System 1'
                ]
            ],
            [
                'content' => 'Patients with damage to emotion-processing brain regions:',
                'correct' => 'B',
                'options' => [
                    'A' => 'Make better logical decisions',
                    'B' => 'Make poor decisions despite intact reasoning',
                    'C' => 'Cannot make any decisions',
                    'D' => 'Rely more on System 2 thinking'
                ]
            ],
            [
                'content' => 'The "paradox of choice" refers to:',
                'correct' => 'D',
                'options' => [
                    'A' => 'Having no good options available',
                    'B' => 'Making choices without thinking',
                    'C' => 'Preferring not to make decisions',
                    'D' => 'Too many options leading to worse outcomes'
                ]
            ]
        ];

        foreach ($mcQuestions3 as $q) {
            $question = Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'multiple_choice',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 3,
                'marks' => 1,
                'passage_id' => $passage3->id
            ]);

            foreach ($q['options'] as $letter => $content) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'content' => $letter . ') ' . $content,
                    'is_correct' => ($letter === $q['correct'])
                ]);
            }
        }

        // Questions 36-40: Summary Completion with Word Bank
        $summaryQuestion2 = Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'summary_completion',
            'content' => 'Complete the summary using words from the box below.',
            'instructions' => 'Decision-Making Psychology',
            'order_number' => $orderNumber,
            'part_number' => 3,
            'marks' => 5,
            'passage_id' => $passage3->id,
            'section_specific_data' => [
                'summary_text' => "Research has shown that human decision-making is influenced by various (36)_____ and is not purely rational. Kahneman and Tversky identified two thinking systems: one that is fast and (37)_____, and another that is slow and (38)_____. The importance of emotions in decision-making is demonstrated by patients with brain damage who struggle with choices despite having intact (39)_____. To improve decision-making, people should be aware of their cognitive (40)_____ and use strategies to counter them.",
                'word_bank' => ['biases', 'intuitive', 'analytical', 'deliberative', 'reasoning', 'limitations', 'emotions', 'logical', 'experiences', 'beliefs'],
                'answers' => ['biases', 'intuitive', 'deliberative', 'reasoning', 'limitations']
            ]
        ]);
    }

    private function seedWritingQuestions($testSet)
    {
        // Task 1: Academic Report Writing
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'task1_bar_chart',
            'content' => 'The chart below shows the percentage of households in owned and rented accommodation in England and Wales between 1918 and 2011.

Summarise the information by selecting and reporting the main features, and make comparisons where relevant.',
            'instructions' => 'Write at least 150 words.',
            'word_limit' => 150,
            'time_limit' => 20,
            'order_number' => 1,
            'part_number' => 1,
            'marks' => 6,
            'media_path' => 'sample-charts/ownership-rental-chart.png', // You'll need to add this image
            'section_specific_data' => [
                'sample_answer' => 'The bar chart illustrates the proportion of households in owned and rented accommodation in England and Wales from 1918 to 2011...',
                'key_features' => [
                    'Overall trend showing increase in home ownership',
                    'Crossover point around 1971',
                    'Peak ownership in 2001',
                    'Slight decline after 2001'
                ]
            ]
        ]);

        // Task 2: Essay Writing
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'task2_discussion',
            'content' => 'Some people believe that technology has made our lives more complex, while others think it has made life easier.

Discuss both views and give your own opinion.',
            'instructions' => 'Write at least 250 words.',
            'word_limit' => 250,
            'time_limit' => 40,
            'order_number' => 2,
            'part_number' => 2,
            'marks' => 9,
            'section_specific_data' => [
                'essay_structure' => [
                    'Introduction',
                    'Body Paragraph 1 - Technology makes life complex',
                    'Body Paragraph 2 - Technology makes life easier',
                    'Body Paragraph 3 - Personal opinion',
                    'Conclusion'
                ],
                'key_points' => [
                    'Balance both viewpoints',
                    'Provide specific examples',
                    'Clear personal opinion',
                    'Logical structure'
                ]
            ]
        ]);
    }

    private function seedSpeakingQuestions($testSet)
    {
        // Part 1: Introduction and Interview (4-5 minutes)
        $part1Questions = [
            [
                'content' => 'Let\'s talk about your hometown. Where is your hometown?',
                'tips' => 'Give a brief description including location and main characteristics.'
            ],
            [
                'content' => 'What do you like most about your hometown?',
                'tips' => 'Mention 2-3 specific things with brief explanations.'
            ],
            [
                'content' => 'How has your hometown changed in recent years?',
                'tips' => 'Compare past and present, give specific examples of changes.'
            ],
            [
                'content' => 'Now let\'s talk about food. What kind of food do you like to eat?',
                'tips' => 'Mention types of cuisine and specific dishes if possible.'
            ],
            [
                'content' => 'Do you prefer eating at home or eating out? Why?',
                'tips' => 'Give reasons for your preference with examples.'
            ],
            [
                'content' => 'What traditional food is popular in your country?',
                'tips' => 'Name specific dishes and explain what makes them traditional.'
            ]
        ];

        $orderNumber = 1;
        foreach ($part1Questions as $q) {
            Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'part1_personal',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 1,
                'time_limit' => 5,
                'speaking_tips' => $q['tips'],
                'read_time' => 5,
                'min_response_time' => 15,
                'max_response_time' => 45,
                'auto_progress' => true,
                'card_theme' => 'blue'
            ]);
        }

        // Part 2: Individual Long Turn (3-4 minutes)
        Question::create([
            'test_set_id' => $testSet->id,
            'question_type' => 'part2_cue_card',
            'content' => 'Describe a book that you enjoyed reading.

You should say:
- what the book was about
- why you decided to read it
- what you liked about the book
and explain why you would recommend this book to others.',
            'order_number' => $orderNumber++,
            'part_number' => 2,
            'time_limit' => 2,
            'instructions' => 'You will have 1 minute to prepare and 1-2 minutes to speak.',
            'speaking_tips' => 'Use the preparation time to make brief notes. Cover all points on the card and speak for the full time.',
            'read_time' => 60, // 1 minute preparation
            'min_response_time' => 60,
            'max_response_time' => 120,
            'auto_progress' => false,
            'card_theme' => 'purple',
            'form_structure' => [
                'fields' => [
                    ['label' => 'what the book was about'],
                    ['label' => 'why you decided to read it'],
                    ['label' => 'what you liked about the book'],
                    ['label' => 'why you would recommend this book to others']
                ]
            ]
        ]);

        // Part 3: Two-way Discussion (4-5 minutes)
        $part3Questions = [
            [
                'content' => 'We\'ve been talking about a book you enjoyed. Now I\'d like to discuss reading habits in general. Why do you think some people prefer to read books while others prefer to watch movies?',
                'tips' => 'Compare and contrast, consider different perspectives.'
            ],
            [
                'content' => 'How do you think technology has changed people\'s reading habits?',
                'tips' => 'Think about e-books, audiobooks, online content. Give specific examples.'
            ],
            [
                'content' => 'Do you think schools should encourage children to read more? How could they do this?',
                'tips' => 'Give your opinion clearly and suggest practical methods.'
            ],
            [
                'content' => 'What role do libraries play in modern society?',
                'tips' => 'Consider both traditional and modern functions of libraries.'
            ],
            [
                'content' => 'Some people say that reading fiction is a waste of time. What\'s your opinion?',
                'tips' => 'Present balanced arguments before giving your personal view.'
            ]
        ];

        foreach ($part3Questions as $q) {
            Question::create([
                'test_set_id' => $testSet->id,
                'question_type' => 'part3_discussion',
                'content' => $q['content'],
                'order_number' => $orderNumber++,
                'part_number' => 3,
                'time_limit' => 5,
                'speaking_tips' => $q['tips'],
                'read_time' => 8,
                'min_response_time' => 30,
                'max_response_time' => 90,
                'auto_progress' => true,
                'card_theme' => 'green'
            ]);
        }
    }
}