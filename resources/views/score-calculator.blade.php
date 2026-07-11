@php
    $websiteSettings = \App\Models\WebsiteSetting::getSettings();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Score Calculator - {{ $websiteSettings->site_name }}</title>
    @if($websiteSettings->favicon)
        <link rel="icon" type="image/png" href="{{ $websiteSettings->favicon_url }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(180deg, #fff5f5 0%, #ffffff 100%); }
        .input-pretty { transition: all 0.2s ease; }
        .input-pretty:focus { transform: translateY(-1px); }
        @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        .pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen">
    <x-guest-header />

    <main class="pt-24 pb-16 px-4">
        <div class="max-w-7xl mx-auto">
            {{-- Hero --}}
            <div class="text-center mb-10 mt-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 mb-4 bg-red-50 border border-red-200 rounded-full text-xs font-semibold text-[#C8102E]">
                    <i class="fas fa-bolt"></i> Instant IELTS Band Calculator
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-3 tracking-tight">
                    Calculate Your <span class="bg-gradient-to-r from-[#C8102E] to-[#8B0000] bg-clip-text text-transparent">IELTS Band</span>
                </h1>
                <p class="text-gray-600 max-w-xl mx-auto text-sm">Enter your raw scores below to instantly see your IELTS band score for each section and the overall band.</p>
            </div>

            {{-- Main grid: inputs left, result right --}}
            <div class="grid lg:grid-cols-3 gap-6 mb-10">
                {{-- Inputs --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex items-center gap-2">
                        <i class="fas fa-edit text-[#C8102E]"></i>
                        <h2 class="font-bold text-gray-900">Enter Your Scores</h2>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Listening --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs">
                                        <i class="fas fa-headphones"></i>
                                    </span>
                                    Listening
                                </label>
                                <span class="text-[11px] text-gray-500">Out of 40</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="number" id="listening-raw" min="0" max="40" placeholder="0"
                                       class="input-pretty flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-lg font-semibold">
                                <div class="w-28 text-center px-3 py-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="text-[10px] text-blue-600 font-medium uppercase">Band</div>
                                    <div id="listening-band" class="text-lg font-bold text-blue-700 leading-tight">—</div>
                                </div>
                            </div>
                        </div>

                        {{-- Reading --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 inline-flex items-center justify-center text-xs">
                                        <i class="fas fa-book-open"></i>
                                    </span>
                                    Reading
                                </label>
                                <div class="flex items-center gap-3 text-[11px]">
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="reading-type" value="academic" checked class="text-emerald-600">
                                        <span class="text-gray-600">Academic</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="reading-type" value="general" class="text-emerald-600">
                                        <span class="text-gray-600">General</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="number" id="reading-raw" min="0" max="40" placeholder="0"
                                       class="input-pretty flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-lg font-semibold">
                                <div class="w-28 text-center px-3 py-2.5 bg-emerald-50 border border-emerald-200 rounded-lg">
                                    <div class="text-[10px] text-emerald-600 font-medium uppercase">Band</div>
                                    <div id="reading-band" class="text-lg font-bold text-emerald-700 leading-tight">—</div>
                                </div>
                            </div>
                        </div>

                        {{-- Writing --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 inline-flex items-center justify-center text-xs">
                                        <i class="fas fa-pen-fancy"></i>
                                    </span>
                                    Writing
                                </label>
                                <span class="text-[11px] text-gray-500">Band 0 – 9</span>
                            </div>
                            <input type="number" id="writing-band-input" min="0" max="9" step="0.5" placeholder="e.g. 6.5"
                                   class="input-pretty w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-lg font-semibold">
                        </div>

                        {{-- Speaking --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-violet-100 text-violet-600 inline-flex items-center justify-center text-xs">
                                        <i class="fas fa-microphone"></i>
                                    </span>
                                    Speaking
                                </label>
                                <span class="text-[11px] text-gray-500">Band 0 – 9</span>
                            </div>
                            <input type="number" id="speaking-band-input" min="0" max="9" step="0.5" placeholder="e.g. 7"
                                   class="input-pretty w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none text-lg font-semibold">
                        </div>

                        <button id="reset-btn"
                                class="w-full px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-undo mr-1"></i> Reset all
                        </button>
                    </div>
                </div>

                {{-- Result --}}
                <div class="lg:col-span-1">
                    <div class="bg-gradient-to-br from-[#C8102E] via-[#A00E27] to-[#8B0000] rounded-2xl shadow-xl p-6 text-white sticky top-24">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fas fa-trophy text-yellow-300"></i>
                            <h2 class="font-bold opacity-95">Your Overall Band</h2>
                        </div>

                        <div class="text-center py-6 px-4 bg-white/10 rounded-xl backdrop-blur-sm mb-4 border border-white/10">
                            <p class="text-[10px] uppercase tracking-widest opacity-80 mb-1">Final Score</p>
                            <div id="overall-band" class="text-6xl md:text-7xl font-extrabold leading-none mb-1">—</div>
                            <p id="overall-label" class="text-xs font-medium opacity-90 mt-2 min-h-[16px]">Enter all scores above</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 text-sm">
                            <div class="bg-white/10 rounded-lg p-2.5 border border-white/10">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-headphones text-[10px] opacity-80"></i>
                                    <span class="text-[10px] uppercase opacity-80 font-medium">Listening</span>
                                </div>
                                <div id="r-listening" class="text-xl font-extrabold">—</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-2.5 border border-white/10">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-book-open text-[10px] opacity-80"></i>
                                    <span class="text-[10px] uppercase opacity-80 font-medium">Reading</span>
                                </div>
                                <div id="r-reading" class="text-xl font-extrabold">—</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-2.5 border border-white/10">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-pen-fancy text-[10px] opacity-80"></i>
                                    <span class="text-[10px] uppercase opacity-80 font-medium">Writing</span>
                                </div>
                                <div id="r-writing" class="text-xl font-extrabold">—</div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-2.5 border border-white/10">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <i class="fas fa-microphone text-[10px] opacity-80"></i>
                                    <span class="text-[10px] uppercase opacity-80 font-medium">Speaking</span>
                                </div>
                                <div id="r-speaking" class="text-xl font-extrabold">—</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-white/15 text-[11px] opacity-80 text-center">
                            <i class="fas fa-info-circle mr-1"></i> Average rounded to nearest 0.5 band
                        </div>
                    </div>
                </div>
            </div>

            {{-- Conversion tables --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex items-center gap-2">
                    <i class="fas fa-table text-[#C8102E]"></i>
                    <h3 class="font-bold text-gray-900">Official IELTS Conversion Tables</h3>
                </div>

                <div class="grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    {{-- Listening --}}
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs">
                                <i class="fas fa-headphones"></i>
                            </span>
                            <h4 class="font-bold text-gray-900 text-sm">Listening</h4>
                        </div>
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="text-gray-500 border-b border-gray-200">
                                    <th class="text-left py-1.5 font-medium text-[11px]">RAW</th>
                                    <th class="text-right py-1.5 font-medium text-[11px]">BAND</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach([['39–40',9.0],['37–38',8.5],['35–36',8.0],['32–34',7.5],['30–31',7.0],['26–29',6.5],['23–25',6.0],['18–22',5.5],['16–17',5.0],['13–15',4.5],['11–12',4.0]] as [$r,$b])
                                    <tr><td class="py-1 text-gray-700">{{ $r }}</td><td class="text-right font-bold text-blue-600">{{ $b }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Reading Academic --}}
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 inline-flex items-center justify-center text-xs">
                                <i class="fas fa-book-open"></i>
                            </span>
                            <h4 class="font-bold text-gray-900 text-sm">Reading (Academic)</h4>
                        </div>
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="text-gray-500 border-b border-gray-200">
                                    <th class="text-left py-1.5 font-medium text-[11px]">RAW</th>
                                    <th class="text-right py-1.5 font-medium text-[11px]">BAND</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach([['39–40',9.0],['37–38',8.5],['35–36',8.0],['33–34',7.5],['30–32',7.0],['27–29',6.5],['23–26',6.0],['19–22',5.5],['15–18',5.0],['13–14',4.5],['10–12',4.0]] as [$r,$b])
                                    <tr><td class="py-1 text-gray-700">{{ $r }}</td><td class="text-right font-bold text-emerald-600">{{ $b }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Reading General --}}
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 inline-flex items-center justify-center text-xs">
                                <i class="fas fa-book"></i>
                            </span>
                            <h4 class="font-bold text-gray-900 text-sm">Reading (General)</h4>
                        </div>
                        <table class="w-full text-[13px]">
                            <thead>
                                <tr class="text-gray-500 border-b border-gray-200">
                                    <th class="text-left py-1.5 font-medium text-[11px]">RAW</th>
                                    <th class="text-right py-1.5 font-medium text-[11px]">BAND</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach([['40',9.0],['39',8.5],['37–38',8.0],['36',7.5],['34–35',7.0],['32–33',6.5],['30–31',6.0],['27–29',5.5],['23–26',5.0],['19–22',4.5],['15–18',4.0]] as [$r,$b])
                                    <tr><td class="py-1 text-gray-700">{{ $r }}</td><td class="text-right font-bold text-orange-600">{{ $b }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const listeningBands = [
            { min: 39, band: 9.0 }, { min: 37, band: 8.5 }, { min: 35, band: 8.0 },
            { min: 32, band: 7.5 }, { min: 30, band: 7.0 }, { min: 26, band: 6.5 },
            { min: 23, band: 6.0 }, { min: 18, band: 5.5 }, { min: 16, band: 5.0 },
            { min: 13, band: 4.5 }, { min: 11, band: 4.0 }, { min: 8, band: 3.5 },
            { min: 6, band: 3.0 }, { min: 4, band: 2.5 }, { min: 3, band: 2.0 },
            { min: 2, band: 1.5 }, { min: 1, band: 1.0 }, { min: 0, band: 0 },
        ];
        const readingAcademic = [
            { min: 39, band: 9.0 }, { min: 37, band: 8.5 }, { min: 35, band: 8.0 },
            { min: 33, band: 7.5 }, { min: 30, band: 7.0 }, { min: 27, band: 6.5 },
            { min: 23, band: 6.0 }, { min: 19, band: 5.5 }, { min: 15, band: 5.0 },
            { min: 13, band: 4.5 }, { min: 10, band: 4.0 }, { min: 8, band: 3.5 },
            { min: 6, band: 3.0 }, { min: 4, band: 2.5 }, { min: 3, band: 2.0 },
            { min: 2, band: 1.5 }, { min: 1, band: 1.0 }, { min: 0, band: 0 },
        ];
        const readingGeneral = [
            { min: 40, band: 9.0 }, { min: 39, band: 8.5 }, { min: 37, band: 8.0 },
            { min: 36, band: 7.5 }, { min: 34, band: 7.0 }, { min: 32, band: 6.5 },
            { min: 30, band: 6.0 }, { min: 27, band: 5.5 }, { min: 23, band: 5.0 },
            { min: 19, band: 4.5 }, { min: 15, band: 4.0 }, { min: 12, band: 3.5 },
            { min: 9, band: 3.0 }, { min: 6, band: 2.5 }, { min: 4, band: 2.0 },
            { min: 2, band: 1.5 }, { min: 1, band: 1.0 }, { min: 0, band: 0 },
        ];

        function rawToBand(raw, table) {
            if (raw === '' || raw === null || isNaN(raw)) return null;
            raw = parseInt(raw);
            const found = table.find(r => raw >= r.min);
            return found ? found.band : 0;
        }

        function roundOverall(score) {
            const floor = Math.floor(score);
            const frac = score - floor;
            if (frac < 0.25) return floor;
            if (frac < 0.75) return floor + 0.5;
            return floor + 1;
        }

        function fmt(b) {
            if (b === null || b === undefined || isNaN(b)) return '—';
            return Number(b).toFixed(1);
        }

        function getLabel(band) {
            if (band >= 8.5) return 'Expert User';
            if (band >= 7.5) return 'Very Good User';
            if (band >= 6.5) return 'Good User';
            if (band >= 5.5) return 'Modest User';
            if (band >= 4.5) return 'Limited User';
            if (band >= 3.5) return 'Extremely Limited User';
            if (band > 0) return 'Intermittent User';
            return '';
        }

        function calculate() {
            const lRaw = document.getElementById('listening-raw').value;
            const rRaw = document.getElementById('reading-raw').value;
            const wIn = document.getElementById('writing-band-input').value;
            const sIn = document.getElementById('speaking-band-input').value;
            const rType = document.querySelector('input[name="reading-type"]:checked').value;

            const lBand = rawToBand(lRaw, listeningBands);
            const rBand = rawToBand(rRaw, rType === 'academic' ? readingAcademic : readingGeneral);
            const wBand = wIn !== '' ? parseFloat(wIn) : null;
            const sBand = sIn !== '' ? parseFloat(sIn) : null;

            document.getElementById('r-listening').textContent = fmt(lBand);
            document.getElementById('r-reading').textContent = fmt(rBand);
            document.getElementById('r-writing').textContent = fmt(wBand);
            document.getElementById('r-speaking').textContent = fmt(sBand);

            document.getElementById('listening-band').textContent = fmt(lBand);
            document.getElementById('reading-band').textContent = fmt(rBand);

            const all = [lBand, rBand, wBand, sBand].filter(v => v !== null && !isNaN(v));
            if (all.length === 4) {
                const avg = all.reduce((s, v) => s + v, 0) / 4;
                const overall = roundOverall(avg);
                document.getElementById('overall-band').textContent = fmt(overall);
                document.getElementById('overall-label').textContent = getLabel(overall);
            } else {
                document.getElementById('overall-band').textContent = '—';
                document.getElementById('overall-label').textContent = `Fill all 4 sections (${all.length}/4 done)`;
            }
        }

        document.getElementById('reset-btn').addEventListener('click', () => {
            ['listening-raw', 'reading-raw', 'writing-band-input', 'speaking-band-input'].forEach(id => {
                document.getElementById(id).value = '';
            });
            calculate();
        });

        ['listening-raw', 'reading-raw', 'writing-band-input', 'speaking-band-input'].forEach(id => {
            document.getElementById(id).addEventListener('input', calculate);
        });
        document.querySelectorAll('input[name="reading-type"]').forEach(r => r.addEventListener('change', calculate));
        calculate();
    </script>
</body>
</html>
