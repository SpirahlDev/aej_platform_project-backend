<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Person;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AuthService
{
    /**
     * Create a new administrator account.
     *
     * @param array $data
     * @return array
     */
    public function createAdmin(array $data)
    {
        try {
            // Start transaction to ensure data integrity
            DB::beginTransaction();

            // Find admin profile
            $adminProfile = Profile::where('name', 'admin')->first();

            if (!$adminProfile) {
                throw new Exception('Admin profile not found in the system');
            }

            // Create person record
            $person = Person::create([
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            // Create account record
            $account = Account::create([
                'person_id' => $person->id,
                'profile_id' => $adminProfile->id,
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'is_active' => true,
            ]);


            // if (class_exists('App\Models\ActivityLog')) {
            //     \App\Models\ActivityLog::create([
            //         'account_id' => auth()->id(),
            //         'action' => 'create_admin',
            //         'entity' => 'accounts',
            //         'entity_id' => $account->id,
            //         'details' => "Created admin account for {$person->full_name}",
            //         'ip_address' => request()->ip(),
            //     ]);
            // }

            DB::commit();

            return [
                'success' => true,
                'account' => $account,
                'person' => $person
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin account: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create admin account: ' . $e->getMessage()
            ];
        }
    }

    public function getAllAccounts(): Collection
    {
        return Account::with(['person', 'profile'])->get();
    }

    public function getAccountByEmail(string $login): Account|null{
        $account = Account::where('email', $login)->first();
        return $account ?? null;
    }
}
