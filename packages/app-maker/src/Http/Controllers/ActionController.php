<?php

namespace AppMaker\Http\Controllers;

use AppMaker\Resources\ResourceBase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionController
{
    public function handle(Request $request, string $resource, string $action, $recordId): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        $modelClass = $resourceInstance->getModel();
        $record = $modelClass::findOrFail($recordId);

        $table = $resourceInstance->table();
        $actionInstance = $table->getRecordActionByName($action);

        if (!$actionInstance) {
            return response()->json(['error' => 'Action not found'], 404);
        }

        if (!$actionInstance->isAuthorized()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$actionInstance->isVisible($record)) {
            return response()->json(['error' => 'Action not available'], 403);
        }

        try {
            $result = $actionInstance->execute($record);

            return response()->json([
                'success' => true,
                'message' => 'Action executed successfully',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleBulk(Request $request, string $resource, string $action): JsonResponse
    {
        $resourceInstance = $this->resolveResource($resource);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['error' => 'No records selected'], 400);
        }

        $modelClass = $resourceInstance->getModel();
        $records = $modelClass::whereIn('id', $ids)->get();

        $table = $resourceInstance->table();
        $bulkActions = collect($table->getActions()['bulk'])->flatten(1);
        $actionInstance = $bulkActions->firstWhere('name', $action);

        if (!$actionInstance) {
            return response()->json(['error' => 'Bulk action not found'], 404);
        }

        try {
            // Get actual action object (not array)
            foreach ($table->bulkActions ?? [] as $bulkAction) {
                if (method_exists($bulkAction, 'toArray')) {
                    foreach ($bulkAction->toArray() as $act) {
                        if ($act['name'] === $action) {
                            $result = $bulkAction->execute($records);

                            return response()->json([
                                'success' => true,
                                'message' => 'Bulk action executed successfully',
                                'affected' => $records->count(),
                                'result' => $result,
                            ]);
                        }
                    }
                }
            }

            return response()->json(['error' => 'Bulk action not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function resolveResource(string $resource): ResourceBase
    {
        $class = config("appmaker.resources.{$resource}");

        if (!$class || !class_exists($class)) {
            abort(404, "Resource {$resource} not found");
        }

        return new $class();
    }
}
