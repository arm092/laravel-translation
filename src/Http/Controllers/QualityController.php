<?php

namespace Arm092\Translation\Http\Controllers;

use Arm092\Translation\Scanner;
use Arm092\Translation\Support\SourceLocale;
use Arm092\Translation\Support\TranslationQuality;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class QualityController extends Controller
{
    public function index(Request $request, Scanner $scanner, TranslationQuality $quality, SourceLocale $source)
    {
        $locale = (string) $request->get('locale', $source->get());
        $status = (string) $request->get('status', 'missing');
        $scan = $scanner->scan();
        $rows = match ($status) {
            'unused' => collect($quality->unused($locale))->map(fn ($key) => ['status' => 'unused', 'key' => $key, 'file' => '', 'line' => '']),
            'dynamic', 'ambiguous' => collect($scan->dynamic)->filter(fn ($item) => $status !== 'ambiguous' || $item->ambiguous)->map(fn ($item) => ['status' => $item->ambiguous ? 'ambiguous' : 'dynamic', 'key' => $item->expression, 'file' => $item->file, 'line' => $item->line]),
            default => collect($quality->missing($locale))->map(fn ($key) => ['status' => 'missing', 'key' => $key, 'file' => '', 'line' => '']),
        };
        if ($search = $request->get('search')) {
            $rows = $rows->filter(fn ($row) => str_contains(strtolower(implode(' ', $row)), strtolower($search)));
        }
        if ($path = $request->get('path')) {
            $rows = $rows->filter(fn ($row) => str_contains($row['file'], $path));
        }
        $page = LengthAwarePaginator::resolveCurrentPage();
        $results = new LengthAwarePaginator($rows->forPage($page, 50)->values(), $rows->count(), 50, $page, ['path' => $request->url(), 'query' => $request->query()]);
        $summary = ['missing' => count($quality->missing($locale)), 'unused' => count($quality->unused($locale)), 'dynamic' => count($scan->dynamic), 'ambiguous' => count(array_filter($scan->dynamic, fn ($item) => $item->ambiguous))];

        return view('translation::quality.index', compact('locale', 'status', 'results', 'summary'));
    }

    public function export(Request $request, Scanner $scanner, TranslationQuality $quality, SourceLocale $source)
    {
        $locale = (string) $request->get('locale', $source->get());
        $rows = [['status', 'locale', 'key', 'file', 'line']];
        foreach ($quality->missing($locale) as $key) {
            $rows[] = ['missing', $locale, $key, '', ''];
        }
        foreach ($quality->unused($locale) as $key) {
            $rows[] = ['unused', $locale, $key, '', ''];
        }
        foreach ($scanner->scan()->dynamic as $item) {
            $rows[] = [$item->ambiguous ? 'ambiguous' : 'dynamic', $locale, $item->expression, $item->file, $item->line];
        }

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            } fclose($handle);
        }, 'translation-quality.csv', ['Content-Type' => 'text/csv']);
    }
}
