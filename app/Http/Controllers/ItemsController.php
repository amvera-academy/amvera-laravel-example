<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemsController
{
    private function dataFile(): string
    {
        $dataDir = env('DATA_DIR', env('AMVERA') ? '/data' : storage_path('app'));
        if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
        return $dataDir.'/items.json';
    }

    private function readItems(): array
    {
        $file = $this->dataFile();
        if (!file_exists($file)) file_put_contents($file, '[]');
        return json_decode(file_get_contents($file), true) ?? [];
    }

    private function writeItems(array $items): void
    {
        file_put_contents($this->dataFile(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function health(): JsonResponse
    {
        return response()->json(['ok' => true, 'framework' => 'Laravel', 'storage' => $this->dataFile()]);
    }

    public function index(): JsonResponse
    {
        $items = array_reverse($this->readItems());
        return response()->json(['items' => $items, 'count' => count($items)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $items = $this->readItems();
        $ids = array_column($items, 'id');
        $item = ['id' => $ids ? max($ids) + 1 : 1, 'name' => trim($data['name'])];
        $items[] = $item;
        $this->writeItems($items);
        return response()->json(['item' => $item], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $items = $this->readItems();
        $next = array_values(array_filter($items, fn ($item) => $item['id'] !== $id));
        if (count($next) === count($items)) return response()->json(['error' => 'Item not found'], 404);
        $this->writeItems($next);
        return response()->json(['deleted' => true, 'id' => $id]);
    }
}
