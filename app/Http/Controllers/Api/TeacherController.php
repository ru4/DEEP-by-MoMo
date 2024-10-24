<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Events\UserRegisteredEvent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Resources\TeacherResource;
use App\Http\Requests\Teacher\SearchTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Resources\TeacherCollection;

use Illuminate\Support\Str;
use App\Notifications\ChoosePasswordNotification;
use Illuminate\Notifications\Notifiable;

class TeacherController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // $this->authorize('viewAny', Teacher::class);
        $teachers = Teacher::paginate();
        return TeacherCollection::make($teachers);
    }

    public function show(Teacher $teacher)
    {
        return TeacherResource::make($teacher);
    }

    public function store(StoreTeacherRequest $request)
    {
        $this->authorize('create', Teacher::class);

        DB::beginTransaction();

        try {
            // Create the user first
            $userData = $request->only(['name', 'email', 'password', 'role']);
            $userData['password'] = Str::random(8);
            $userData['role'] = 'teacher';
            $user = User::create($userData);

            // Create the teacher
            $teacherData = $request->only(['name', 'photo', 'phone_number', 'bio']);
            $teacher = Teacher::create($teacherData);

            // Associate the user with the teacher
            $teacher->user()->save($user);

            // Attach the teacher to selected schools
            $teacher->schools()->attach($request->school_ids);

            // Notify the user to choose a password
            $token = app('auth.password.broker')->createToken($user);

            $url = env('APP_URL') . '/#/reset-password?token='.$token . '&email=' . $userData['email'];
            $user->notify(new ChoosePasswordNotification($url));

            DB::commit();

            return TeacherResource::make($teacher);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'Error creating teacher', 'errors' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $this->authorize('update', $teacher);
        $teacher->update($request->validated());
        return response()->json([
            'message' => 'Teacher updated successfully.',
            'data' => TeacherResource::make($teacher)
        ]);
    }

    public function destroy(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);
        $teacher->delete();
        return response()->json(null, 204);
    }

    public function search(SearchTeacherRequest $request)
    {
        $term = $request->query('search');
        $teachers = Teacher::search($term)->paginate()->appends(['search' => $term]);

        return TeacherCollection::make($teachers);
    }
}
