<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PlaylistController extends Controller
{
    private array $titleStopWords = [
        'a', 'an', 'and', 'at', 'by', 'for', 'from', 'in', 'live', 'lyrics',
        'music', 'of', 'official', 'on', 'song', 'the', 'to', 'video', 'with',
    ];

    private function fetchRemoteHtml(string $url): string
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);

            return $response->successful() ? $response->body() : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function normalizeSearchText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
        $value = preg_replace('/\bthik\b/i', 'tihik', $value);
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function titleTerms(string $title): array
    {
        $words = preg_split('/\s+/', $this->normalizeSearchText($title));
        $terms = [];

        foreach ($words ?: [] as $word) {
            if (strlen($word) < 3) continue;
            if (in_array($word, $this->titleStopWords, true)) continue;
            if (!in_array($word, $terms, true)) $terms[] = $word;
        }

        return $terms;
    }

    private function slugify(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($value)), '-');
    }

    private function slugVariants(string $value): array
    {
        $words = array_values(array_filter(preg_split('/\s+/', $this->normalizeSearchText($value)) ?: []));
        if (empty($words)) return [];

        $phrases = [implode(' ', $words)];

        if (count($words) > 2) {
            $phrases[] = implode(' ', array_slice($words, 1));
        }

        $withoutExcellent = array_values(array_filter($words, fn ($word) => $word !== 'excellent'));
        if ($withoutExcellent !== $words) {
            $phrases[] = implode(' ', $withoutExcellent);
        }

        return array_values(array_unique(array_filter(array_map(fn ($phrase) => $this->slugify($phrase), $phrases))));
    }

    private function titleMatchRatio(string $haystack, string $title): float
    {
        $terms = $this->titleTerms($title);
        if (empty($terms)) return 0;

        $normalizedHaystack = ' ' . $this->normalizeSearchText($haystack) . ' ';
        $hits = 0;

        foreach ($terms as $term) {
            if (str_contains($normalizedHaystack, ' ' . $term . ' ')) {
                $hits++;
            }
        }

        return $hits / count($terms);
    }

    private function hasMeaningfulArtist(string $artist): bool
    {
        $normalizedArtist = $this->normalizeSearchText($artist);
        return $normalizedArtist !== '' && !preg_match('/^(unknown|unknown artist|official|topic|channel)$/i', $normalizedArtist);
    }

    private function sourceLooksRelevant(string $html, string $url, string $title, string $artist = ''): bool
    {
        $terms = $this->titleTerms($title);
        if (empty($terms)) return false;

        preg_match('/<title[^>]*>([\s\S]*?)<\/title>/i', $html, $titleMatch);
        preg_match_all('/<h[1-3][^>]*>([\s\S]*?)<\/h[1-3]>/i', $html, $headingMatches);

        $pageIdentity = trim(($titleMatch[1] ?? '') . ' ' . implode(' ', $headingMatches[1] ?? []) . ' ' . urldecode($url));
        $identityRatio = $this->titleMatchRatio($pageIdentity, $title);
        $bodyPreview = substr($this->stripHtmlToText($html), 0, 2000);
        $bodyRatio = $this->titleMatchRatio($bodyPreview, $title);

        $needsArtistMatch = $this->hasMeaningfulArtist($artist) && count($terms) <= 1;
        $artistRatio = $this->hasMeaningfulArtist($artist)
            ? max($this->titleMatchRatio($pageIdentity, $artist), $this->titleMatchRatio($bodyPreview, $artist))
            : 0;

        if ($needsArtistMatch && $artistRatio < 0.5) {
            return false;
        }

        if ($identityRatio >= 0.6) return true;
        if (count($terms) <= 2 && $identityRatio >= 0.5) return true;

        return $bodyRatio >= 0.8 || ($artist && $bodyRatio >= 0.6 && $this->titleMatchRatio($pageIdentity, $artist) > 0);
    }

    private function stripHtmlToText(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $html);
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/i', '', $html);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\/(p|div|section|article|li|h1|h2|h3|h4|h5|h6|pre)>/i', "\n", $text);
        $text = preg_replace('/<li[^>]*>/i', '• ', $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r/", '', $text);
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    private function scoreLyrics(string $lyrics): int
    {
        if (!$lyrics) return 0;
        $lines = array_values(array_filter(array_map('trim', preg_split("/\r?\n/", $lyrics))));
        return strlen($lyrics) + count($lines) * 15;
    }

    private function scoreChords(string $chords): int
    {
        if (!$chords) return 0;
        preg_match_all('/\b[A-G][b#]?(?:m|maj|min|sus|dim|aug|add|M|alt|7|9|11|13|5|6)?(?:\/[A-G][b#]?)?\b/', $chords, $matches);
        return strlen($chords) + count($matches[0] ?? []) * 20;
    }

    private function extractDuckDuckGoUrls(string $html): array
    {
        $urls = [];

        preg_match_all('/uddg=([^"&]+)/', $html, $encodedMatches);
        foreach ($encodedMatches[1] ?? [] as $encodedUrl) {
            $url = urldecode($encodedUrl);
            if (preg_match('/duckduckgo\.com|google\.com|youtube\.com|facebook\.com|instagram\.com/i', $url)) continue;
            if (!in_array($url, $urls, true)) $urls[] = $url;
        }

        return array_slice($urls, 0, 6);
    }

    private function cleanScrapedText(string $text): string
    {
        $text = preg_replace('/\b(?:VIDEO|More Songs of|SHARE)\b[\s\S]*/i', '', $text);
        $text = preg_replace('/Share this:[\s\S]*/i', '', $text);
        $text = preg_replace('/(?:Submit|Correct|Add).{0,40}(?:Lyrics|Chords)[\s\S]*/i', '', $text);
        $text = preg_replace('/^\s*"[^"]+".*$/mi', '', $text);
        $text = preg_replace('/\bThis should be[\s\S]*/i', '', $text);
        $text = preg_replace('/\bEnjoy\.[\s\S]*/i', '', $text);
        $text = preg_replace('/^\s*(?:LYRICS|Print|Download Lyrics|Download as PDF|Download as Word)\s*$/mi', '', $text);
        $text = preg_replace('/^\s*(?:•|â€¢|-)\s*$/mi', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    private function chordPattern(): string
    {
        return '(?:[A-G][b#]?(?:maj|min|sus|dim|aug|add|alt|m|M|7|9|11|13|5|6|2|4)*(?:\/[A-G][b#]?)?)';
    }

    private function preserveChordTags(string $html): string
    {
        return preg_replace_callback(
            '/<span[^>]*class="[^"]*\bchord\b[^"]*"[^>]*>([\s\S]*?)<\/span>/i',
            fn ($match) => '[' . trim(strip_tags($match[1])) . ']',
            $html
        );
    }

    private function buildArrangedChordLine(array $placements): string
    {
        $chordLine = '';

        foreach ($placements as [$position, $chord]) {
            $position = max(0, (int) $position);
            $currentLength = strlen($chordLine);

            if ($position > $currentLength) {
                $chordLine .= str_repeat(' ', $position - $currentLength);
            } elseif ($currentLength > 0) {
                $chordLine .= ' ';
            }

            $chordLine .= $chord;
        }

        return rtrim($chordLine);
    }

    private function arrangeMarkedInlineChords(string $line): string
    {
        $pattern = '/\[(' . $this->chordPattern() . ')\]/';
        if (!preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
            return rtrim($line);
        }

        $lyrics = '';
        $placements = [];
        $cursor = 0;

        foreach ($matches[0] as $index => $match) {
            [$marker, $offset] = $match;
            $before = substr($line, $cursor, $offset - $cursor);
            $lyrics .= $before;
            $placements[] = [strlen($lyrics), $matches[1][$index][0]];
            $cursor = $offset + strlen($marker);
        }

        $lyrics .= substr($line, $cursor);
        $chordLine = $this->buildArrangedChordLine($placements);
        $lyrics = rtrim(preg_replace('/ {2,}/', ' ', $lyrics));
        $lyrics = preg_replace('/\bnae\b/i', 'name', $lyrics);
        $lyrics = preg_replace('/\bGnd\b/', 'And', $lyrics);

        return $lyrics === '' ? $chordLine : $chordLine . "\n" . $lyrics;
    }

    private function markPlainInlineChords(string $line): string
    {
        $line = preg_replace('/\bAGnd\b/', 'GAnd', $line);
        $chordPattern = $this->chordPattern();
        $lineLength = strlen($line);
        $marked = '';
        $matches = 0;
        $embeddedMatches = 0;

        for ($index = 0; $index < $lineLength; $index++) {
            $remaining = substr($line, $index);

            if (!preg_match('/^' . $chordPattern . '/', $remaining, $match)) {
                $marked .= $line[$index];
                continue;
            }

            $chord = $match[0];
            $next = $line[$index + strlen($chord)] ?? '';
            $previous = $index > 0 ? $line[$index - 1] : '';
            $previousAllowsChord = $index === 0 || preg_match('/[\s(\/|]/', $previous) || preg_match('/[a-z]/', $previous);
            $nextAllowsChord = $next === '' || preg_match('/[\s),:;\]]/', $next) || preg_match('/[a-zA-Z]/', $next);
            $commonWords = ['and', 'call', 'deep', 'eyes', 'fail', 'feet', 'for', 'from', 'grace', 'great', 'guide'];

            if (($index === 0 || preg_match('/\s/', $previous)) && preg_match('/^[A-Za-z]+/', $remaining, $wordMatch)) {
                if (in_array(strtolower($wordMatch[0]), $commonWords, true)) {
                    $marked .= $line[$index];
                    continue;
                }
            }

            if ($chord === 'Am' && ($line[$index + 2] ?? '') === 'e') {
                $chord = 'A';
                $next = $line[$index + 1] ?? '';
            }

            if (!$previousAllowsChord || !$nextAllowsChord) {
                $marked .= $line[$index];
                continue;
            }

            $matches++;
            if ($previous !== '' && preg_match('/[a-z]/', $previous)) {
                $embeddedMatches++;
            }

            $marked .= '[' . $chord . ']';
            $wordCursor = $index + strlen($chord);
            while ($wordCursor < $lineLength && preg_match('/[A-Za-z]/', $line[$wordCursor])) {
                $marked .= $line[$wordCursor];
                $wordCursor++;
            }

            $index = $wordCursor - 1;
        }

        if ($matches < 2 && $embeddedMatches === 0) {
            return $line;
        }

        return $marked;
    }

    private function arrangeChordSheet(string $text): string
    {
        $lines = preg_split("/\r?\n/", $text) ?: [];
        $arranged = [];

        foreach ($lines as $line) {
            $line = rtrim($line);
            $trimmed = trim($line);

            if ($trimmed === '') {
                $arranged[] = '';
                continue;
            }

            if (preg_match('/^(?:verse|chorus|bridge|pre-chorus|intro|instrumental|ending|tag|outro)\b.*:?$/i', $trimmed)) {
                $arranged[] = $trimmed;
                continue;
            }

            if (preg_match('/^(?:\s+|\(?' . $this->chordPattern() . '\)?)+$/', $trimmed)) {
                $arranged[] = preg_replace('/[()]/', '', $line);
                continue;
            }

            if (preg_match('/\[(' . $this->chordPattern() . ')\]/', $line)) {
                $arranged[] = $this->arrangeMarkedInlineChords($line);
                continue;
            }

            $markedLine = $this->markPlainInlineChords($line);
            $arranged[] = $markedLine !== $line
                ? $this->arrangeMarkedInlineChords($markedLine)
                : $line;
        }

        return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", $arranged)));
    }

    private function scrapeLyricsFromHtml(string $html, string $title, string $artist = '', string $url = ''): string
    {
        if (!$this->sourceLooksRelevant($html, $url, $title, $artist)) {
            return '';
        }

        $patterns = [
            '/<div[^>]+class="[^"]*\blyric-text\b[^"]*"[^>]*>([\s\S]*?)<\/div>/i',
            '/<div[^>]+(?:id|class)="[^"]*(?:lyrics|lyric|songtext|entry-content|post-body)[^"]*"[^>]*>([\s\S]*?)<\/div>/i',
            '/<div class="post-body entry-content[^"]*">([\s\S]*?)<\/div>/i',
            '/<div class="[^"]*entry-content[^"]*">([\s\S]*?)<\/div>/i',
            '/<pre[^>]*>([\s\S]*?)<\/pre>/i',
            '/<article[^>]*>([\s\S]*?)<\/article>/i',
        ];

        $best = '';
        $bestScore = 0;

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $html, $match) || empty($match[1])) continue;
            $text = $this->cleanScrapedText($this->stripHtmlToText($match[1]));
            if (strlen($text) < 60) continue;

            $score = $this->scoreLyrics($text);
            if ($this->titleMatchRatio($text, $title) >= 0.5) $score += 250;

            if ($score > $bestScore) {
                $best = trim($text);
                $bestScore = $score;
            }
        }

        return $bestScore > 160 ? $best : '';
    }

    private function scrapeChordsFromHtml(string $html, string $title, string $artist = '', string $url = ''): string
    {
        if (!$this->sourceLooksRelevant($html, $url, $title, $artist)) {
            return '';
        }

        $patterns = [
            '/<div[^>]+id="song-lyrics"[^>]*>([\s\S]*?)<\/div>/i',
            '/<pre[^>]*id="core"[^>]*>([\s\S]*?)<\/pre>/i',
            '/<pre[^>]*class="[^"]*(?:chord|tab|js-tab-content)[^"]*"[^>]*>([\s\S]*?)<\/pre>/i',
            '/<pre[^>]*>([\s\S]*?)<\/pre>/i',
            '/<div[^>]+class="[^"]*chord-text[^"]*"[^>]*>([\s\S]*?)<\/div>/i',
            '/<div[^>]+id="snippet--snippetXteView"[^>]*>([\s\S]*?)<link/is',
            '/"content":"([^"]+)"/i',
        ];

        $best = '';
        $bestScore = 0;

        foreach ($patterns as $pattern) {
            if (!preg_match_all($pattern, $html, $matches) || empty($matches[1])) continue;
            $raw = $this->preserveChordTags(str_replace(['\"', '\n', '\/'], ['"', "\n", '/'], implode("\n\n", $matches[1])));
            $text = str_replace(['[/ch]', '[ch]', '[/tab]', '[tab]'], '', $this->stripHtmlToText($raw));
            $text = $this->cleanScrapedText($text);
            $text = preg_replace('/^\s*--\s*START\s*--\s*$/mi', '', $text);
            $text = preg_replace('/\n{3,}/', "\n\n", $text);
            $text = $this->arrangeChordSheet($text);
            if (strlen($text) < 60) continue;

            $score = $this->scoreChords($text);
            if ($this->titleMatchRatio($text, $title) >= 0.5) $score += 250;

            if ($score > $bestScore) {
                $best = trim($text);
                $bestScore = $score;
            }
        }

        return $bestScore > 180 ? $best : '';
    }

    private function fetchLyricsFromKnownSites(string $artist, string $title): string
    {
        $slug = $this->slugify($title);
        $urls = array_filter([
            $slug ? "https://www.theworshipsongs.com/parts/lyrics/{$slug}-lyrics.html" : '',
            $slug ? "https://christianlyricsonline.net/lyrics/{$slug}/" : '',
        ]);

        foreach ($urls as $url) {
            $pageHtml = $this->fetchRemoteHtml($url);
            if (!$pageHtml) continue;

            $lyrics = $this->scrapeLyricsFromHtml($pageHtml, $title, $artist, $url);
            if ($lyrics) return $lyrics;
        }

        return '';
    }

    private function fetchChordsFromKnownSites(string $artist, string $title): string
    {
        $artistSlugs = $this->slugVariants($artist);
        $titleSlugs = $this->slugVariants($title);

        foreach (array_values(array_unique([
            rawurlencode($title),
            rawurlencode($title . '!'),
        ])) as $encodedTitle) {
            if (!$encodedTitle) continue;

            $url = "https://ultimatechords.io/song/title/{$encodedTitle}";
            $pageHtml = $this->fetchRemoteHtml($url);
            if (!$pageHtml) continue;

            $chords = $this->scrapeChordsFromHtml($pageHtml, $title, $artist, $url);
            if ($chords) return $chords;
        }

        foreach ($artistSlugs as $artistSlug) {
            foreach ($titleSlugs as $titleSlug) {
                $directUrls = [
                    "https://www.guitartabsexplorer.com/{$artistSlug}/{$titleSlug}-chords",
                    "https://www.chords-and-tabs.net/song/name/{$artistSlug}-{$titleSlug}",
                ];

                foreach ($directUrls as $url) {
                    $pageHtml = $this->fetchRemoteHtml($url);
                    if (!$pageHtml) continue;

                    $chords = $this->scrapeChordsFromHtml($pageHtml, $title, $artist, $url);
                    if ($chords) return $chords;
                }
            }
        }

        $slugs = array_values(array_unique(array_filter([
            $this->slugify(trim("{$title} {$artist}")),
            $this->slugify($title),
            $this->slugify(trim("{$artist} {$title}")),
        ])));

        foreach ($slugs as $slug) {
            $url = "https://pnwchords.com/{$slug}/";
            $pageHtml = $this->fetchRemoteHtml($url);
            if (!$pageHtml) continue;

            $chords = $this->scrapeChordsFromHtml($pageHtml, $title, $artist, $url);
            if ($chords) return $chords;
        }

        $searchQueries = [
            'https://www.e-chords.com/search-all/' . urlencode(trim("{$title} {$artist}")),
            'https://www.azchords.com/search?q=' . urlencode(trim("{$title} {$artist}")),
            'https://www.chordie.com/result.php?q=' . urlencode(trim("{$title} {$artist}")),
        ];

        foreach ($searchQueries as $searchUrl) {
            $searchHtml = $this->fetchRemoteHtml($searchUrl);
            if (!$searchHtml) continue;

            preg_match_all('/href="([^"]+)"/i', $searchHtml, $matches);
            foreach ($matches[1] ?? [] as $href) {
                $url = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (str_starts_with($url, '/')) {
                    $parts = parse_url($searchUrl);
                    $url = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . $url;
                }

                if (!preg_match('/(e-chords\.com\/chords|azchords\.com\/.*\.html|chordie\.com\/chord\.php)/i', $url)) {
                    continue;
                }

                $pageHtml = $this->fetchRemoteHtml($url);
                if (!$pageHtml) continue;

                $chords = $this->scrapeChordsFromHtml($pageHtml, $title, $artist, $url);
                if ($chords) return $chords;
            }
        }

        return '';
    }

    private function fetchLyricsFromSearchResults(string $artist, string $title): string
    {
        $knownLyrics = $this->fetchLyricsFromKnownSites($artist, $title);
        if ($knownLyrics) return $knownLyrics;

        $searchHtml = $this->fetchRemoteHtml('https://html.duckduckgo.com/html/?q=' . urlencode(trim("{$artist} {$title} lyrics")));
        foreach ($this->extractDuckDuckGoUrls($searchHtml) as $url) {
            $pageHtml = $this->fetchRemoteHtml($url);
            if (!$pageHtml) continue;
            $lyrics = $this->scrapeLyricsFromHtml($pageHtml, $title, $artist, $url);
            if ($lyrics) return $lyrics;
        }

        return '';
    }

    private function fetchChordsFromSearchResults(string $artist, string $title): string
    {
        $knownChords = $this->fetchChordsFromKnownSites($artist, $title);
        if ($knownChords) return $knownChords;

        $searchHtml = $this->fetchRemoteHtml('https://html.duckduckgo.com/html/?q=' . urlencode(trim("{$artist} {$title} chords")));
        foreach ($this->extractDuckDuckGoUrls($searchHtml) as $url) {
            $pageHtml = $this->fetchRemoteHtml($url);
            if (!$pageHtml) continue;
            $chords = $this->scrapeChordsFromHtml($pageHtml, $title, $artist, $url);
            if ($chords) return $chords;
        }

        return '';
    }

    public function index()
    {
        $folders = Folder::with(['songs' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->get();
        
        $data = $folders->map(function($f) {
            return [
                'id' => (string) $f->id,
                'name' => $f->name,
                'songs' => $f->songs->map(function($s) {
                    return [
                        'id' => (string) $s->id,
                        'title' => $s->title,
                        'artist' => $s->artist ?? 'Unknown Artist',
                        'url' => $s->url,
                        'lyrics' => $s->lyrics ?? '',
                        'chords' => $s->chords ?? '',
                    ];
                })->values()
            ];
        });

        return response()->json($data);
    }

    public function sync(Request $request)
    {
        $foldersData = $request->all();

        try {
            DB::transaction(function() use ($foldersData) {
                
                // 1. RULE: FOLDER DELETION VALIDATION
                $incomingFolderIds = collect($foldersData)->pluck('id')->filter()->toArray();
                
                $foldersToDelete = empty($incomingFolderIds) 
                    ? Folder::all() 
                    : Folder::whereNotIn('id', $incomingFolderIds)->get();

                foreach ($foldersToDelete as $folderToDelete) {
                    // I-check kung naa bay kanta sulod ani nga folder
                    if (Song::where('folder_id', $folderToDelete->id)->exists()) {
                        throw new \Exception("Cannot delete folder '{$folderToDelete->name}' because it still contains songs. Please empty it first.");
                    }
                    $folderToDelete->delete();
                }

                // 2. FOLDER & SONG SYNCING LOOP
                foreach($foldersData as $folderData) {
                    if (!isset($folderData['id']) || !isset($folderData['name'])) continue;

                    // Buhat o I-update ang folder
                    $folder = Folder::updateOrCreate(
                        ['id' => (string) $folderData['id']],
                        ['name' => $folderData['name']]
                    );

                    $songs = $folderData['songs'] ?? [];
                    $incomingSongIds = collect($songs)->pluck('id')->filter()->toArray();
                    
                    // I-delete ang mga kanta sa DB nga gitangtang ni User aning foldera
                    if (!empty($incomingSongIds)) {
                        Song::where('folder_id', $folder->id)->whereNotIn('id', $incomingSongIds)->delete();
                    } else {
                        Song::where('folder_id', $folder->id)->delete();
                    }

                    // I-save ang mga kanta (I-mintinar ang han-ay/order para sa Drag and Drop)
                    $seenUrlsInPayload = []; // Tig-detect if naay duplicate link gikan sa parehas nga request

                    foreach($songs as $index => $song) {
                        if (!isset($song['id']) || !isset($song['url'])) continue;

                        // 3. RULE: YOUTUBE LINK EXISTENCE VALIDATION
                        $isYoutubeLink = str_contains($song['url'], 'youtu');

                        if ($isYoutubeLink) {
                            // Check kung gi-add niya og kaduha sa usa ka request
                            if (in_array($song['url'], $seenUrlsInPayload)) {
                                throw new \Exception("Duplicate YouTube link detected for '{$song['title']}'.");
                            }
                            $seenUrlsInPayload[] = $song['url'];

                            // Check sa Database kung nag-exist na ba ni nga link sa maong folder
                            $existingSong = Song::where('folder_id', $folder->id)
                                                ->where('url', $song['url'])
                                                ->where('id', '!=', $song['id'])
                                                ->first();

                            if ($existingSong) {
                                throw new \Exception("The YouTube link for '{$song['title']}' already exists in the folder '{$folder->name}'.");
                            }
                        }

                        // Kung pasado tanan, i-save/update ang kanta
                        Song::updateOrCreate(
                            ['id' => (string) $song['id']],
                            [
                                'folder_id' => $folder->id,
                                'title' => $song['title'] ?? 'Unknown Title',
                                'artist' => $song['artist'] ?? 'Unknown Artist',
                                'url' => $song['url'],
                                'lyrics' => $song['lyrics'] ?? '',
                                'chords' => $song['chords'] ?? '',
                                'order' => $index
                            ]
                        );
                    }
                }
            });

            return response()->json(['message' => 'Synced successfully', 'status' => 'success'], 200);

        } catch (\Exception $e) {
            // I-return ngadto sa frontend nga 400 Bad Request aron masabtan sa Axios nga naay error sa validation
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 400); 
        }
    }

    public function upload(Request $request) {
        $request->validate([
            'audio' => 'required|mimes:mp3,wav,ogg|max:30000', // max 30MB
            'folder_id' => 'required',
            'title' => 'required'
        ]);

        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $path = $file->store('songs', 'public'); 
            
            $song = \App\Models\Song::create([
                'id' => (string) str_replace('.', '', microtime(true)),
                'folder_id' => $request->folder_id,
                'title' => $request->title,
                'artist' => 'Local Upload',
                'url' => asset('storage/' . $path), 
                'file_path' => $path,
                'lyrics' => '',
                'chords' => '',
                'order' => 0
            ]);

            return response()->json($song);
        }
    }

    public function fetchSongResources(Request $request)
    {
        $validated = $request->validate([
            'artist' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
        ]);

        $artist = trim($validated['artist'] ?? '');
        $title = trim($validated['title']);

        $lyrics = $this->fetchLyricsFromSearchResults($artist, $title);
        $chords = $this->fetchChordsFromSearchResults($artist, $title);

        return response()->json([
            'lyrics' => $lyrics,
            'chords' => $chords,
        ]);
    }
}
