<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * 取得使用者列表
     *
     * @param array $filters
     * @return array
     */
    public function getUsers(array $filters = []): object
    {
        $result = $this->userRepository->getUsers($filters);

        return $result;
    }

    /**
     * 建立新使用者
     *
     * @param array $userData
     * @return array
     */
    public function createUser(array $userData): array
    {
        try {
            DB::beginTransaction();

            // 建立使用者
            $user = $this->userRepository->createUser($userData);

            DB::commit();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 根據 ID 取得使用者
     *
     * @param int $id
     * @return array
     */
    public function getUserById(int $id): array
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new Exception('使用者不存在');
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ];
    }

    /**
     * 更新使用者
     *
     * @param int $id
     * @param array $userData
     * @return array
     */
    public function updateUser(int $id, array $userData): array
    {
        try {
            DB::beginTransaction();

            $user = $this->userRepository->updateUser($id, $userData);

            if (!$user) {
                throw new Exception('使用者不存在');
            }

            DB::commit();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'updated_at' => $user->updated_at,
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 刪除使用者
     *
     * @param int $id
     * @return array
     */
    public function deleteUser(int $id): array
    {
        try {
            DB::beginTransaction();

            $result = $this->userRepository->deleteUser($id);

            if (!$result) {
                throw new Exception('使用者不存在或刪除失敗');
            }

            DB::commit();

            return [];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
