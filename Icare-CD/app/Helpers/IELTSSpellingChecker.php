<?php

namespace App\Helpers;

/**
 * IELTS Spelling Checker
 *
 * Handles all spelling variations and tolerances according to IELTS official rules:
 * - British vs American spellings (both accepted)
 * - Common spelling mistakes with 1-2 character difference
 * - Plural/singular variations
 * - Hyphenation variants
 * - Article tolerance
 * - Number-word equivalence
 *
 * @author CD-IELTS Development Team
 * @version 2.0 - IELTS Compliant
 */
class IELTSSpellingChecker
{
    /**
     * Comprehensive British/American spelling variants
     * Covers 150+ most common IELTS vocabulary words
     * Updated with academic, scientific, and common IELTS terms
     */
    private static array $britishAmericanVariants = [
        // -our/-or endings (Extended)
        'colour' => 'color',
        'honour' => 'honor',
        'favour' => 'favor',
        'flavour' => 'flavor',
        'labour' => 'labor',
        'neighbour' => 'neighbor',
        'rumour' => 'rumor',
        'humour' => 'humor',
        'behaviour' => 'behavior',
        'harbour' => 'harbor',
        'vapour' => 'vapor',
        'odour' => 'odor',
        'vigour' => 'vigor',
        'endeavour' => 'endeavor',
        'splendour' => 'splendor',
        'armour' => 'armor',
        'clamour' => 'clamor',
        'candour' => 'candor',
        'rancour' => 'rancor',
        'rigour' => 'rigor',
        'savour' => 'savor',
        'ardour' => 'ardor',
        'parlour' => 'parlor',
        'tumour' => 'tumor',
        'glamour' => 'glamor',

        // -re/-er endings (Extended)
        'centre' => 'center',
        'theatre' => 'theater',
        'metre' => 'meter',
        'litre' => 'liter',
        'fibre' => 'fiber',
        'calibre' => 'caliber',
        'sombre' => 'somber',
        'spectre' => 'specter',
        'goitre' => 'goiter',
        'lustre' => 'luster',
        'meagre' => 'meager',
        'ochre' => 'ocher',
        'reconnoitre' => 'reconnoiter',
        'sabre' => 'saber',
        'saltpetre' => 'saltpeter',
        'sepulchre' => 'sepulcher',
        'sceptre' => 'scepter',

        // -ise/-ize endings (Extended - IELTS Common)
        'organise' => 'organize',
        'realise' => 'realize',
        'recognise' => 'recognize',
        'analyse' => 'analyze',
        'paralyse' => 'paralyze',
        'criticise' => 'criticize',
        'apologise' => 'apologize',
        'emphasise' => 'emphasize',
        'minimise' => 'minimize',
        'maximise' => 'maximize',
        'prioritise' => 'prioritize',
        'categorise' => 'categorize',
        'summarise' => 'summarize',
        'utilise' => 'utilize',
        'visualise' => 'visualize',
        'standardise' => 'standardize',
        'capitalise' => 'capitalize',
        'characterise' => 'characterize',
        'generalise' => 'generalize',
        'memorise' => 'memorize',
        'modernise' => 'modernize',
        'popularise' => 'popularize',
        'stabilise' => 'stabilize',
        'terrorise' => 'terrorize',
        'authorise' => 'authorize',
        'civilise' => 'civilize',
        'colonise' => 'colonize',
        'commercialise' => 'commercialize',
        'computerise' => 'computerize',
        'dramatise' => 'dramatize',
        'economise' => 'economize',
        'equalise' => 'equalize',
        'familiarise' => 'familiarize',
        'fantasise' => 'fantasize',
        'finalise' => 'finalize',
        'harmonise' => 'harmonize',
        'hospitalise' => 'hospitalize',
        'hypnotise' => 'hypnotize',
        'idealise' => 'idealize',
        'immunise' => 'immunize',
        'industrialise' => 'industrialize',
        'itemise' => 'itemize',
        'jeopardise' => 'jeopardize',
        'legalise' => 'legalize',
        'legitimise' => 'legitimize',
        'localise' => 'localize',
        'materialise' => 'materialize',
        'mechanise' => 'mechanize',
        'mobilise' => 'mobilize',
        'monopolise' => 'monopolize',
        'moralise' => 'moralize',
        'nationalise' => 'nationalize',
        'naturalise' => 'naturalize',
        'neutralise' => 'neutralize',
        'normalise' => 'normalize',
        'optimise' => 'optimize',
        'personalise' => 'personalize',
        'polarise' => 'polarize',
        'privatise' => 'privatize',
        'publicise' => 'publicize',
        'randomise' => 'randomize',
        'rationalise' => 'rationalize',
        'revolutionise' => 'revolutionize',
        'serialise' => 'serialize',
        'socialise' => 'socialize',
        'specialise' => 'specialize',
        'sterilise' => 'sterilize',
        'subsidise' => 'subsidize',
        'symbolise' => 'symbolize',
        'synchronise' => 'synchronize',
        'synthesise' => 'synthesize',
        'theorise' => 'theorize',
        'tranquilise' => 'tranquilize',
        'urbanise' => 'urbanize',
        'vaporise' => 'vaporize',
        'verbalise' => 'verbalize',

        // -isation/-ization (Extended)
        'organisation' => 'organization',
        'realisation' => 'realization',
        'civilisation' => 'civilization',
        'globalisation' => 'globalization',
        'modernisation' => 'modernization',
        'urbanisation' => 'urbanization',
        'specialisation' => 'specialization',
        'characterisation' => 'characterization',
        'authorisation' => 'authorization',
        'colonisation' => 'colonization',
        'commercialisation' => 'commercialization',
        'computerisation' => 'computerization',
        'generalisation' => 'generalization',
        'hospitalisation' => 'hospitalization',
        'industrialisation' => 'industrialization',
        'legalisation' => 'legalization',
        'localisation' => 'localization',
        'nationalisation' => 'nationalization',
        'naturalisation' => 'naturalization',
        'normalisation' => 'normalization',
        'personalisation' => 'personalization',
        'privatisation' => 'privatization',
        'standardisation' => 'standardization',
        'visualisation' => 'visualization',

        // -yse/-yze endings
        'analyse' => 'analyze',
        'paralyse' => 'paralyze',
        'catalyse' => 'catalyze',
        'hydrolyse' => 'hydrolyze',
        'electrolyse' => 'electrolyze',
        'breathalyse' => 'breathalyze',

        // -ogue/-og endings
        'catalogue' => 'catalog',
        'dialogue' => 'dialog',
        'analogue' => 'analog',
        'monologue' => 'monolog',
        'prologue' => 'prolog',
        'epilogue' => 'epilog',
        'travelogue' => 'travelog',
        'idealogue' => 'idealog',

        // -ence/-ense endings
        'defence' => 'defense',
        'offence' => 'offense',
        'licence' => 'license', // noun form
        'pretence' => 'pretense',

        // Double L variations (Extended)
        'travelled' => 'traveled',
        'travelling' => 'traveling',
        'traveller' => 'traveler',
        'cancelled' => 'canceled',
        'cancelling' => 'canceling',
        'modelling' => 'modeling',
        'labelling' => 'labeling',
        'jewellery' => 'jewelry',
        'marvellous' => 'marvelous',
        'councillor' => 'councilor',
        'counsellor' => 'counselor',
        'fuelling' => 'fueling',
        'quarrelling' => 'quarreling',
        'signalling' => 'signaling',
        'tunnelling' => 'tunneling',
        'levelled' => 'leveled',
        'levelling' => 'leveling',
        'marshalled' => 'marshaled',
        'marshalling' => 'marshaling',
        'marvelled' => 'marveled',
        'marvelling' => 'marveling',
        'panelled' => 'paneled',
        'panelling' => 'paneling',
        'parcelled' => 'parceled',
        'parcelling' => 'parceling',
        'quarrelled' => 'quarreled',
        'revelled' => 'reveled',
        'revelling' => 'reveling',
        'shrivelled' => 'shriveled',
        'shrivelling' => 'shriveling',
        'snivelled' => 'sniveled',
        'snivelling' => 'sniveling',
        'swivelled' => 'swiveled',
        'swivelling' => 'swiveling',
        'yodelled' => 'yodeled',
        'yodelling' => 'yodeling',
        'worshipped' => 'worshiped',
        'worshipping' => 'worshiping',
        'kidnapped' => 'kidnaped',
        'kidnapping' => 'kidnaping',

        // -ae/-e and -oe/-e (Extended)
        'encyclopaedia' => 'encyclopedia',
        'mediaeval' => 'medieval',
        'manoeuvre' => 'maneuver',
        'anaemia' => 'anemia',
        'diarrhoea' => 'diarrhea',
        'foetus' => 'fetus',
        'oestrogen' => 'estrogen',
        'paediatric' => 'pediatric',
        'paediatrician' => 'pediatrician',
        'orthopaedic' => 'orthopedic',
        'gynaecology' => 'gynecology',
        'archaeology' => 'archeology',
        'haemorrhage' => 'hemorrhage',
        'haemoglobin' => 'hemoglobin',
        'leukaemia' => 'leukemia',

        // Miscellaneous common variants (Extended)
        'grey' => 'gray',
        'tyre' => 'tire',
        'programme' => 'program',
        'cheque' => 'check',
        'mould' => 'mold',
        'plough' => 'plow',
        'draught' => 'draft',
        'skilful' => 'skillful',
        'fulfil' => 'fulfill',
        'enrol' => 'enroll',
        'instalment' => 'installment',
        'judgement' => 'judgment',
        'acknowledgement' => 'acknowledgment',
        'focussed' => 'focused',
        'focussing' => 'focusing',
        'storey' => 'story', // building floors
        'kerb' => 'curb',
        'speciality' => 'specialty',
        'practise' => 'practice', // verb vs noun
        'pyjamas' => 'pajamas',
        'sulphur' => 'sulfur',
        'aluminium' => 'aluminum',
        'licence' => 'license',
        'defence' => 'defense',
        'offence' => 'offense',
        'aeroplane' => 'airplane',
        'ageing' => 'aging',
        'annexe' => 'annex',
        'artefact' => 'artifact',
        'capitalisation' => 'capitalization',
        'cosy' => 'cozy',
        'councillor' => 'councilor',
        'doughnut' => 'donut',
        'inflexion' => 'inflection',
        'liquorice' => 'licorice',
        'manoeuvrable' => 'maneuverable',
        'moustache' => 'mustache',
        'omelette' => 'omelet',
        'peddler' => 'pedlar',
        'sceptical' => 'skeptical',
        'scepticism' => 'skepticism',
        'vigour' => 'vigor',
        'vice' => 'vise', // tool
        'yoghurt' => 'yogurt',
    ];

    /**
     * Common IELTS vocabulary spelling mistakes (tolerance level 1-2 chars)
     * Words students commonly misspell in IELTS exams
     * Extended with 60+ high-frequency IELTS words
     */
    private static array $commonMisspellings = [
        // Common reception/perception errors
        'receive' => ['recieve', 'recive'],
        'believe' => ['beleive', 'belive'],
        'achieve' => ['acheive', 'achive'],
        'perceive' => ['percieve', 'percive'],
        'conceive' => ['concieve', 'concive'],
        'deceive' => ['decieve', 'decive'],

        // Government & Politics
        'government' => ['goverment', 'govenment'],
        'parliament' => ['parliment'],
        'bureaucracy' => ['beaurocracy', 'beurocracy'],
        'democracy' => ['democrasy'],
        'privilege' => ['priviledge', 'privelege'],

        // Environment & Science
        'environment' => ['enviroment', 'enviornment'],
        'climate' => ['climite'],
        'temperature' => ['temprature', 'temperture'],
        'atmosphere' => ['atmoshere', 'atmosphear'],
        'pollution' => ['polution', 'polusion'],
        'maintenance' => ['maintenence', 'maintainance'],
        'satellite' => ['satelite', 'sattelite'],
        'laboratory' => ['labratory', 'laborotory'],
        'rhythm' => ['rythm', 'rhytm'],
        'weight' => ['wieght'],
        'height' => ['hieght'],

        // Business & Economy
        'business' => ['buisness', 'bussiness'],
        'definitely' => ['definately', 'definatly'],
        'separate' => ['seperate'],
        'independent' => ['independant'],
        'entrepreneur' => ['entrepeneur', 'enterpreneur'],
        'colleague' => ['collegue', 'collegue'],
        'committee' => ['comittee', 'commitee'],
        'conscience' => ['concience'],
        'competition' => ['competion'],
        'communication' => ['comunication', 'comunication'],

        // Education & Knowledge
        'necessary' => ['neccessary', 'necesary'],
        'knowledge' => ['knowlege', 'knowledg'],
        'professor' => ['professer', 'profesor'],
        'curriculum' => ['curiculum', 'curricullum'],
        'license' => ['lisense', 'licence'],
        'library' => ['libary', 'libarary'],
        'scholarship' => ['scholership'],
        'principal' => ['principle'], // school head
        'principle' => ['principal'], // rule/law
        'grammar' => ['grammer', 'gramar'],
        'language' => ['langauge', 'languege'],
        'pronunciation' => ['pronounciation', 'pronouncation'],
        'pronunciation' => ['pronounciation'],

        // Accommodation & Travel
        'accommodation' => ['accomodation', 'acommodation'],
        'restaurant' => ['restarant', 'resturant'],
        'Mediterranean' => ['Mediteranean', 'Mediterannean'],
        'Caribbean' => ['Carribean', 'Caribean'],
        'foreign' => ['foriegn', 'foregin'],
        'vehicle' => ['vehical', 'veicle'],
        'guarantee' => ['gaurantee', 'garantee'],
        'questionnaire' => ['questionaire', 'questionare'],

        // Time & Frequency
        'tomorrow' => ['tommorow', 'tommorrow'],
        'immediately' => ['immediatly', 'imediately'],
        'beginning' => ['begining', 'beggining'],
        'occurred' => ['occured', 'ocurred'],
        'occurrence' => ['occurence', 'occurance'],
        'calendar' => ['calender', 'calandar'],
        'schedule' => ['scedule', 'shedule'],

        // Equipment & Technology
        'equipment' => ['equiptment', 'equipement'],
        'technology' => ['tecnology', 'technoligy'],
        'computer' => ['computor', 'compter'],
        'database' => ['data base', 'datbase'],
        'algorithm' => ['algoritm', 'algorythm'],
        'analysis' => ['analisis', 'analysys'],

        // Psychology & Medicine
        'psychology' => ['psycology', 'physcology'],
        'psychiatrist' => ['phychiatrist', 'psichiatrist'],
        'hypocrisy' => ['hypocracy', 'hipocracy'],
        'medicine' => ['medecine', 'medicin'],
        'diagnosis' => ['diagnoses', 'diagnosys'],
        'symptom' => ['symtom', 'symptum'],
        'disease' => ['desease', 'diseas'],
        'pharmaceutical' => ['farmaceutical', 'parmaceutical'],

        // Development & Progress
        'development' => ['developement', 'devlopment'],
        'successful' => ['succesful', 'sucessful'],
        'significance' => ['signifigance', 'significence'],
        'difference' => ['diffrence', 'diference'],
        'reference' => ['refrence', 'referance'],
        'preference' => ['prefrence', 'preferance'],
        'exaggerate' => ['exagerate', 'exagerrate'],
        'existence' => ['existance', 'existense'],
        'experience' => ['experiance', 'experence'],
        'experiment' => ['experement', 'experament'],

        // Miscellaneous Common
        'address' => ['adress', 'addres'],
        'advertisement' => ['advertisment', 'advertizement'],
        'argument' => ['arguement', 'arument'],
        'calendar' => ['calender'],
        'category' => ['catagory'],
        'ceiling' => ['cieling'],
        'cemetery' => ['cemetary', 'cematery'],
        'conscience' => ['concious', 'consience'],
        'conscious' => ['concious', 'consious'],
        'definitely' => ['definately', 'definitly'],
        'embarrass' => ['embarass', 'embaress'],
        'fascinate' => ['facinate', 'facinnate'],
        'fiery' => ['firey'],
        'fluorescent' => ['florescent', 'flourescent'],
        'forty' => ['fourty'],
        'height' => ['hieght'],
        'jewelry' => ['jewlery', 'jewelery'],
        'liaise' => ['liase', 'liaze'],
        'liaison' => ['liason', 'liasion'],
        'lightning' => ['lightening'],
        'millennium' => ['millenium', 'milenium'],
        'miniature' => ['miniture', 'minature'],
        'noticeable' => ['noticable'],
        'pastime' => ['passtime', 'pass time'],
        'perseverance' => ['perserverance', 'perseverence'],
        'possession' => ['posession', 'possesion'],
        'publicly' => ['publically'],
        'recommend' => ['recomend', 'reccommend'],
        'relevant' => ['relevent', 'relavent'],
        'religious' => ['religous', 'religeous'],
        'repetition' => ['repitition', 'repetation'],
        'rhythm' => ['rythm', 'rhythym'],
        'ridiculous' => ['rediculous', 'ridiculious'],
        'sacrifice' => ['sacrafice', 'sacrifise'],
        'supersede' => ['supercede'],
        'threshold' => ['threshhold'],
        'truly' => ['truely'],
        'until' => ['untill'],
        'vacuum' => ['vacum', 'vaccum'],
        'weird' => ['wierd'],
    ];

    /**
     * Check if two words match according to IELTS spelling rules
     *
     * @param string $studentAnswer Student's answer
     * @param string $correctAnswer Correct answer
     * @return bool True if spellings are acceptable
     */
    public static function isAcceptableSpelling(string $studentAnswer, string $correctAnswer): bool
    {
        // Normalize both answers
        $student = self::normalize($studentAnswer);
        $correct = self::normalize($correctAnswer);

        // 1. Exact match after normalization
        if ($student === $correct) {
            return true;
        }

        // 2. Check British/American variants
        if (self::areBritishAmericanVariants($student, $correct)) {
            return true;
        }

        // 3. Check common misspellings database
        if (self::isKnownMisspelling($student, $correct)) {
            return true;
        }

        // 4. Check plural/singular variants
        if (self::arePluralVariants($student, $correct)) {
            return true;
        }

        // 5. Check hyphenation variants
        if (self::areHyphenationVariants($student, $correct)) {
            return true;
        }

        // 6. Levenshtein distance check (1-2 char tolerance)
        if (self::isCloseEnough($student, $correct)) {
            return true;
        }

        return false;
    }

    /**
     * Normalize a word for comparison
     * - Lowercase
     * - Trim whitespace
     * - Remove leading articles (a, an, the)
     */
    public static function normalize(string $word): string
    {
        $word = strtolower(trim($word));

        // Remove leading articles
        $word = preg_replace('/^(a|an|the)\s+/i', '', $word);

        return $word;
    }

    /**
     * Check if two words are British/American spelling variants
     */
    public static function areBritishAmericanVariants(string $word1, string $word2): bool
    {
        $w1 = strtolower($word1);
        $w2 = strtolower($word2);

        // Check both directions
        foreach (self::$britishAmericanVariants as $british => $american) {
            if (($w1 === $british && $w2 === $american) ||
                ($w1 === $american && $w2 === $british)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if student's spelling is a known common misspelling
     */
    public static function isKnownMisspelling(string $studentWord, string $correctWord): bool
    {
        $student = strtolower($studentWord);
        $correct = strtolower($correctWord);

        // Check if correct word has known misspellings
        if (isset(self::$commonMisspellings[$correct])) {
            return in_array($student, self::$commonMisspellings[$correct]);
        }

        // Check reverse (if student wrote correct and answer key has variant)
        foreach (self::$commonMisspellings as $correctForm => $variants) {
            if ($student === $correctForm && in_array($correct, $variants)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two words are plural variants
     */
    public static function arePluralVariants(string $word1, string $word2): bool
    {
        $w1 = strtolower($word1);
        $w2 = strtolower($word2);

        // Remove common plural endings
        $singular1 = preg_replace('/(ies|es|s)$/i', '', $w1);
        $singular2 = preg_replace('/(ies|es|s)$/i', '', $w2);

        if ($singular1 === $singular2 && strlen($singular1) > 2) {
            return true;
        }

        // Handle 'y' -> 'ies' conversion (city/cities)
        if (strlen($w1) > 3 && strlen($w2) > 3) {
            $base1 = rtrim($w1, 'ies');
            $base2 = rtrim($w2, 'ies');

            if ($base1 . 'y' === $w2 || $base2 . 'y' === $w1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two words are hyphenation variants
     * ice-cream = ice cream = icecream
     */
    public static function areHyphenationVariants(string $word1, string $word2): bool
    {
        // Remove hyphens and spaces
        $normalized1 = str_replace(['-', ' '], '', strtolower($word1));
        $normalized2 = str_replace(['-', ' '], '', strtolower($word2));

        return $normalized1 === $normalized2 && strlen($normalized1) > 0;
    }

    /**
     * Check if spelling is close enough using Levenshtein distance
     * Allows 1 character difference for words 6+ chars
     * Allows 2 character difference for words 10+ chars
     */
    public static function isCloseEnough(string $word1, string $word2): bool
    {
        $w1 = strtolower($word1);
        $w2 = strtolower($word2);

        $length = max(strlen($w1), strlen($w2));

        // Too short to tolerate mistakes
        if ($length < 6) {
            return false;
        }

        $distance = levenshtein($w1, $w2);

        // 1 char tolerance for 6-9 letter words
        if ($length < 10 && $distance <= 1) {
            return true;
        }

        // 2 char tolerance for 10+ letter words
        if ($length >= 10 && $distance <= 2) {
            return true;
        }

        return false;
    }

    /**
     * Get spelling suggestions for a misspelled word
     * Returns array of possible correct spellings
     */
    public static function getSuggestions(string $misspelledWord): array
    {
        $suggestions = [];
        $word = strtolower(trim($misspelledWord));

        // Check British/American variants
        foreach (self::$britishAmericanVariants as $british => $american) {
            if (levenshtein($word, $british) <= 2) {
                $suggestions[] = $british;
                $suggestions[] = $american;
            } elseif (levenshtein($word, $american) <= 2) {
                $suggestions[] = $american;
                $suggestions[] = $british;
            }
        }

        // Check common misspellings
        foreach (self::$commonMisspellings as $correct => $variants) {
            if (in_array($word, $variants)) {
                $suggestions[] = $correct;
            }
        }

        return array_unique($suggestions);
    }

    /**
     * Validate word against IELTS word limit
     *
     * @param string $answer Student's answer
     * @param int|null $wordLimit Maximum allowed words
     * @return bool True if within limit or no limit specified
     */
    public static function isWithinWordLimit(string $answer, ?int $wordLimit): bool
    {
        if ($wordLimit === null) {
            return true;
        }

        $wordCount = str_word_count($answer);
        return $wordCount <= $wordLimit;
    }

    /**
     * Check if answer contains only allowed characters
     * IELTS accepts: letters, numbers, hyphens, apostrophes, spaces
     */
    public static function hasValidCharacters(string $answer): bool
    {
        // Allow: letters, numbers, spaces, hyphens, apostrophes, periods, commas
        return preg_match('/^[a-zA-Z0-9\s\-\'\.,]+$/', $answer) === 1;
    }

    /**
     * Get spelling tolerance level description
     */
    public static function getToleranceLevel(): string
    {
        return "IELTS Standard:\n" .
               "✓ British & American spellings accepted\n" .
               "✓ 1-2 character typo tolerance for longer words\n" .
               "✓ Plural/singular variations accepted\n" .
               "✓ Hyphenation variants accepted\n" .
               "✓ Articles (a/an/the) ignored";
    }

    /**
     * Add custom spelling variant (for admin/teacher use)
     */
    public static function addCustomVariant(string $variant1, string $variant2): void
    {
        $v1 = strtolower(trim($variant1));
        $v2 = strtolower(trim($variant2));

        if (!isset(self::$britishAmericanVariants[$v1])) {
            self::$britishAmericanVariants[$v1] = $v2;
        }
    }
}
