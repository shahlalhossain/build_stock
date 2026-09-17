<?php

namespace App\Services;

use App\Events\Project\ProjectCreated;
use App\Events\Project\ProjectDeleted;
use App\Events\Project\ProjectDestroyed;
use App\Events\Project\ProjectRestored;
use App\Events\Project\ProjectStatusUpdated;
use App\Events\Project\ProjectUpdated;
use App\Exceptions\GeneralException;
use App\Models\ApprovalLog;
use App\Models\Project;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ProjectService.
 */
class ProjectService extends BaseService
{
    /**
     * ProjectService Constructor.
     */
    public function __construct(Project $project)
    {
        $this->model = $project;
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function storeProject(array $data = []): Project
    {
        DB::beginTransaction();
        try {
            $projectData = [
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'site_address' => $data['site_address'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'expected_end_date' => $data['expected_end_date'] ?? null,
                'estimated_budget' => $data['estimated_budget'] ?? null,
                'project_manager_id' => $data['project_manager_id'] ?? null,
                'priority_order' => $data['priority_order'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];
            $project = $this->model::create($projectData);

            event(new ProjectCreated($project));

            DB::commit();

            return $project;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Creating New Project.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateProject(Project $project, array $data = []): Project
    {
        DB::beginTransaction();

        try {
            $project->update([
                'name' => $data['name'] ?? null,
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? null,
                'site_address' => $data['site_address'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'expected_end_date' => $data['expected_end_date'] ?? null,
                'estimated_budget' => $data['estimated_budget'] ?? null,
                'project_manager_id' => $data['project_manager_id'] ?? null,
                'priority_order' => $data['priority_order'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            event(new ProjectUpdated($project));

            DB::commit();

            return $project;
        } catch (Exception $exception) {
            Log::alert($exception->getMessage());
            DB::rollBack();
            throw new GeneralException(__('There was a Problem on Updating the Project.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function updateProjectStatus(int $id, string $status, ?string $remarks = null): bool
    {
        DB::beginTransaction();
        try {

            $project = Project::findOrFail($id);

            $oldStatus = $project->status;

            if ($oldStatus === $status) {
                return true;
            }

            // Update without Triggering Spatie's "updated" Activity Log
            $project->status = $status;
            $result = $project->saveQuietly();

            ApprovalLog::create([
                'model_type' => Project::class,
                'model_id' => $project->id,
                'action_name' => $status,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
                'remarks' => $remarks,
            ]);

            activity()
                ->performedOn($project)
                ->causedBy(Auth::user())
                ->useLog('project')
                ->event('statusUpdated')
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'remarks' => $remarks,
                ])
                ->log('statusUpdated');

            event(new ProjectStatusUpdated($project));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Project Status Update Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Project Status Update'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function destroyProject($id): bool
    {
        DB::beginTransaction();

        try {
            $project = Project::findOrFail((int) $id);

            $project->is_active = false;
            $project->deleted_by = Auth::id();

            // Prevent the Custom Fields from Generating an "updated" Activity Log
            activity()->withoutLogs(function () use ($project) {
                $project->save();
            });

            $result = $project->delete();

            event(new ProjectDestroyed($project));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Project Destroy Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Destroy Project.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function restoreProject($id): bool
    {
        DB::beginTransaction();
        try {

            $project = Project::withTrashed()->findOrFail($id);

            $project->is_active = true;
            $project->deleted_by = null;

            // Update Custom Fields without Generating an "updated" Activity Log
            $project->saveQuietly();

            // SoftDeletes Restores deleted_at and Fires "restored"
            $result = $project->restore();

            event(new ProjectRestored($project));

            DB::commit();

            return $result;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Project Restore Failed: '.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Restoring the Project.'));
        }
    }

    /**
     * @throws GeneralException
     * @throws Throwable
     */
    public function deleteProject($id): bool
    {
        DB::beginTransaction();
        try {
            $project = Project::withTrashed()->findOrFail($id);

            // Permanently Delete without Automatic Activity Logging.
            activity()->withoutLogs(function () use ($project) {
                $project->forceDelete();
            });

            // Log the Permanent Deletion Explicitly.
            activity()
                ->useLog('project')
                ->event('forceDeleted')
                ->performedOn($project)
                ->causedBy(Auth::user())
                ->log('forceDeleted');

            event(new ProjectDeleted($project));

            DB::commit();

            return true;
        } catch (ModelNotFoundException $exception) {
            DB::rollBack();
            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Project Permanent Deletion Failed in Service:'.$exception->getMessage());
            throw new GeneralException(__('There was an issue on Deleting the Project.'));
        }
    }
}
