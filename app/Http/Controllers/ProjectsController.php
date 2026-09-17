<?php

namespace App\Http\Controllers;

use App\DataTables\ProjectsDataTable;
use App\Exceptions\GeneralException;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ProjectsController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(ProjectsDataTable $projectsDataTable)
    {
        $projectsDataTable->showTrashed = false;

        return $projectsDataTable->render('project.index');
    }

    public function create()
    {
        return view('project.create');
    }

    public function store(StoreProjectRequest $projectRequest)
    {
        try {
            $this->projectService->storeProject($projectRequest->validated());

            return redirect()->route('project.index')->with('success', 'New Project Created Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Project Creation Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Creating Project: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function show(Project $project)
    {
        $data['project'] = $project->load(['manager', 'creator', 'updater', 'deleter', 'approvalLogs.actionedBy']);

        return view('project.show', $data);
    }

    public function edit(Project $project): View
    {
        $data['project'] = $project;

        return view('project.edit', $data);
    }

    public function update(UpdateProjectRequest $projectRequest, Project $project): RedirectResponse
    {
        try {
            $this->projectService->updateProject($project, $projectRequest->validated());

            return redirect()->route('project.index')->with('success', 'Project Updated Successfully.');
        } catch (GeneralException $generalException) {
            Log::error('Project Update Failed: '.$generalException->getMessage());

            return back()->withInput()->with('error', $generalException->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Project: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Unexpected Error Occurred. Try Again.');
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {
            $this->projectService->updateProjectStatus((int) $id, $validated['status'], $validated['remarks'] ?? null);

            return response()->json(['success' => true, 'message' => 'Project Status Updated Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Project Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Project Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Project Status Update Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Updating Project Status: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Updating the Project Status.'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->projectService->destroyProject($id);

            return response()->json(['success' => true, 'message' => 'Project Destroyed Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Project Not Found'.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Project Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Project Deletion Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Deleting Project: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Deleting the Project.'], 500);
        }
    }

    public function trash(ProjectsDataTable $projectsDataTable)
    {
        $projectsDataTable->showTrashed = true;

        return $projectsDataTable->render('project.trashed');
    }

    public function restore($id): JsonResponse
    {
        try {
            $this->projectService->restoreProject($id);

            return response()->json(['success' => true, 'message' => 'Project Restored Successfully.']);
        } catch (ModelNotFoundException $exception) {
            Log::warning('Project Not Found: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Project Not Found.'], 404);
        } catch (GeneralException $exception) {
            Log::error('Project Restoration Failed: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            Log::error('Unexpected Error on Restoring Project: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Unexpected Error Occurred on Restoring the Project.'], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->projectService->deleteProject($id);

            return response()->json(['success' => true, 'message' => 'Project Deleted Permanently.']);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to Delete Project.', 'error' => $exception->getMessage()], 500);
        }
    }
}
