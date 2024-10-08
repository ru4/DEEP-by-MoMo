<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\SearchProjectRequest;
use App\Models\Project;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\ProjectCollection;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\AttachStudentsToProjectRequest;
use App\Http\Requests\Project\AttachTeachersToProjectRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests; // Add this line to import the trait

    public function index()
    {
        $projects = Project::paginate();
        return ProjectCollection::make($projects);
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated());
        return ProjectResource::make($project);
    }

    public function show(Project $project)
    {
        return ProjectResource::make($project->load([
            'school1',
            'school2',
            'teachers',
            'students'
        ]));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validated());
        return ProjectResource::make($project);
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();
        return response()->json(null, 204);

    }

    public function attachStudentsToProject(AttachStudentsToProjectRequest $request, Project $project)
    {
        $this->authorize('create', Project::class);

        // Validate data
        $studentIds = $request->validated()['student_ids'];

        // Attach the students to the project without detaching existing ones
        $project->students()->syncWithoutDetaching($studentIds);


        return response()->json([
            'message' => 'Students attached to the project successfully.',
            'attached_student_count' => count($studentIds)
        ]);
    }

    public function attachTeachersToProject(AttachTeachersToProjectRequest $request, Project $project)
    {
        $this->authorize('create', Project::class);

        // Validate data
        $teacherIds = $request->validated()['teacher_ids'];

        // Attach the Teachers to the project without detaching existing ones
        $project->teachers()->syncWithoutDetaching($teacherIds);


        return response()->json([
            'message' => 'Teachers attached to the project successfully.',
            'attached_teacher_count' => count($teacherIds)
        ]);
    }


    public function search(SearchProjectRequest $request)
    {
        $term = $request->query('search');
        $projects = Project::search($term)->paginate()->appends(['search' => $term]);

        return ProjectCollection::make($projects);
    }

}