<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\IndexUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'search' => 'string|min:1',
        ]);
        $search = $request->get('search');
        return User::getUsers($search)->paginate(15)->appends(['search' => $search]);
    }

    /**
     * @param UserRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request, User $user)
    {
        $user->createUser($request);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param User $user
     * @return IndexUserResource
     */
    public function edit(User $user)
    {
        return IndexUserResource::make($user);
    }

    /**
     * @param UserUpdateRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $user->updateUser($request);
        $user->saveImageIfExist($request, $user);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Illuminate\Validation\ValidationException
     */
    public function trashedUsers(Request $request)
    {
        $this->validate($request, [
            'search' => 'string|min:1',
        ]);
        $search = $request->get('search');
        return User::getUsers($search)->onlyTrashed()->paginate(15)->appends(['search' => $search]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreTrashedUser(int $id)
    {
        if (User::withTrashed()->where('id', $id)->restore()) {
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['Error' => 'User not restored'], 403);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTrashedUser(int $id)
    {
        if (User::withTrashed()->where('id', $id)->forceDelete()) {
            return response()->json(['status' => 'ok']);
        }
        return response()->json(['Error' => 'User not deleted'], 403);
    }
}
