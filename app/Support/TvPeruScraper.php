<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMXPath;
use RuntimeException;

final class TvPeruScraper
{
    /**
     * @return array{title:string, summary:?string, author:?string, image_url:?string, source:string, url:string}
     */
    public function scrape(string $url): array
    {
        $normalizedUrl = filter_var($url, FILTER_VALIDATE_URL);
        if ($normalizedUrl === false) {
            throw new RuntimeException('El enlace proporcionado no es válido.');
        }

        $html = $this->fetch($normalizedUrl);
        
        // Usar tus selectores exactos de JavaScript
        $article = $this->parseWithJsSelectors($html);

        // Validar que tenemos al menos un título
        if (empty($article['title'])) {
            throw new RuntimeException('No se pudo extraer el título de la noticia.');
        }

        return [
            'title' => $article['title'],
            'summary' => $article['summary'] ?? null,
            'author' => $article['author'] ?? 'TVPerú Noticias',
            'image_url' => $article['image_url'] ?? null,
            'source' => 'TVPerú',
            'url' => $normalizedUrl,
        ];
    }

    private function fetch(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'header' => implode("\r\n", [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language: es-ES,es;q=0.9',
                ]),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            throw new RuntimeException('No fue posible descargar el contenido de TVPerú.');
        }

        return $html;
    }

    /**
     * @return array<string, string|null>
     */
    private function parseWithJsSelectors(string $html): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // TUS SELECTORES EXACTOS de JavaScript:
        // $('.col-md-12').find('h1').text().trim()
        $title = $this->firstMatch($xpath, [
            "//div[contains(@class, 'col-md-12')]//h1",
            "//h1"
        ]);

        // $('.bajada').text().trim()
        $summary = $this->firstMatch($xpath, [
            "//div[contains(@class, 'bajada')]",
            "//p[contains(@class, 'bajada')]"
        ]);

        // $('.img-note-alt').find('img').attr('src')
        $image = $this->firstMatch($xpath, [
            "//div[contains(@class, 'img-note-alt')]//img/@src",
            "//img[contains(@class, 'img-note-alt')]/@src",
            "//figure//img/@src",
            "//img/@src"
        ]);

        // Autor fijo como en tu JS
        $author = "TVPerú Noticias";

        // Limitar resumen a 200 caracteres como en tu JS
        if ($summary !== null && strlen($summary) > 200) {
            $summary = substr($summary, 0, 200) . '...';
        }

        return [
            'title' => $title !== null ? trim($title) : 'Noticia de TVPerú',
            'summary' => $summary !== null ? trim($summary) : null,
            'author' => $author,
            'image_url' => $image !== null ? $this->normalizeImageUrl(trim($image)) : null,
        ];
    }

    /**
     * @param array<int, string> $queries
     */
    private function firstMatch(DOMXPath $xpath, array $queries): ?string
    {
        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            if ($nodes === false || $nodes->length === 0) {
                continue;
            }

            $node = $nodes->item(0);
            if ($node === null) {
                continue;
            }

            // Para atributos, usar nodeValue; para elementos, usar textContent
            if ($node->nodeType === XML_ATTRIBUTE_NODE) {
                $value = $node->nodeValue;
            } else {
                $value = $node->textContent;
            }

            $value = trim($value ?? '');
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function normalizeImageUrl(string $imageUrl): string
    {
        // Si la URL es relativa, convertirla a absoluta
        if (strpos($imageUrl, 'http') !== 0) {
            if (strpos($imageUrl, '//') === 0) {
                return 'https:' . $imageUrl;
            }
            if (strpos($imageUrl, '/') === 0) {
                return 'https://www.tvperu.gob.pe' . $imageUrl;
            }
            return 'https://www.tvperu.gob.pe/' . ltrim($imageUrl, '/');
        }

        return $imageUrl;
    }
}