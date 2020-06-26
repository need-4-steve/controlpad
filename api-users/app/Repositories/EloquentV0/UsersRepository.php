<?php

namespace App\Repositories\EloquentV0;

use App\Address;
use App\User;
use App\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use CPCommon\Pid\Pid;

class UsersRepository
{
    public function __construct()
    {
        $this->expandsTable = [
            'sponsor' => function ($query, $value, $params) {
                $query->with(['sponsor' => function ($query) use ($params) {
                    $query->select(User::$selects);
                }]);
            },
            'store_settings' => function ($query, $value, $params) {
                $query->with('rawStoreSettings');
            },
            'settings' => function ($query, $value, $params) {
                $query->with('settings');
            },
            'subscription' => function ($query, $value, $params) {
                $query->with(['subscription' => function ($query) use ($params) {
                    $query->select(Subscription::$selects);
                }]);
            },
        ];
        $this->paramsTable = [
            'end_date' => function ($query, $value, $params) {
                $query->where('users.'.$params['date_column'], '<=', $value);
            },
            'expands' => function ($query, $expands, $params) {
                foreach ($expands as $expand) {
                    if (isset($this->expandsTable[$expand])) {
                        $this->expandsTable[$expand]($query, $expand, $params);
                    }
                }
            },
            'per_page' => function ($query, $value, $params) {
                $query->paginate($params['per_page']);
            },
            'role_id' => function ($query, $value, $params) {
                $query->whereIn('role_id', $value);
            },
            'public_id' => function ($query, $value, $params) {
                $query->where('public_id', $value);
            },
            'search_term' => function ($query, $value, $params) {
                // Remove any outer white spaces
                // Kill any quotes, quote the full search term so that it's not split into multiple searches
                // We don't need the extra database load allowing multiple term searches
                // Reference Sofa\Eloquence\Searchable\Parser.php line 106
                // ^has a bad regex that will fail to match if quotes aren't used properly, which then throws a sql error
                $term = str_replace('"', '', trim($value));
                if (!empty($term)) {
                    if (strlen($term) > 1) {
                        // Can't encapsulate with 1 character (probably another bug?)
                        $term = '"' . $term . '"';
                    }
                    $query->search($term);
                    $query->addSelect('relevance');
                }
            },
            'sort_by' => function ($query, $value, $params) {
                $inOrder = 'ASC';
                if (strpos($value, '-') === 0) {
                    $inOrder = 'DESC';
                    $value = str_replace('-', '', $value);
                }
                return $query->orderBy('users.'.$value, $inOrder);
            },
            'start_date' => function ($query, $value, $params) {
                $query->where('users.'.$params['date_column'], '>=', $value);
            },
        ];
    }

    private function getParams($query, $params)
    {
        foreach ($params as $param => $value) {
            if (isset($this->paramsTable[$param])) {
                $this->paramsTable[$param]($query, $value, $params);
            }
        }
        return $query;
    }

    public function index($request, $sellerId = null)
    {
        $query = User::select(User::$selects);
        $this->getParams($query, $request);
        if (!is_null($sellerId)) {
            $query->join('customers', function ($join) use ($sellerId) {
                $join->on('customers.customer_id', '=', 'users.id')
                ->where('customers.user_id', $sellerId);
            });
        }
        $users = $query->paginate($request['per_page']);
        $this->expandAddresses($users->keyBy('id')->all(), $request);
        if (isset($request['expands']) && in_array('store_settings', $request['expands'])) {
            $this->mapStoreSettings($users);
        }
        return $users;
    }

    public function find($request, $identifier, $column)
    {
        $query = User::select(User::$selects);
        $this->getParams($query, $request);
        $user = $query->where('users.'.$column, $identifier)->first();
        if ($user == null) {
            return null;
        }
        if (isset($request['addresses']) && $request['addresses']) {
            $this->expandAddresses([$user->id => $user], $request);
        }
        if (isset($request['expands']) && in_array('store_settings', $request['expands'])) {
            $this->mapStoreSettings([$user]);
        }
        if (isset($request['profile_pic'])) {
            $result = app('db')
              ->select(
                  'SELECT url_md FROM media WHERE id = (SELECT media_id FROM mediables WHERE mediable_id = ? AND mediable_type = ? LIMIT 1)',
                  [$user->id, "App\\Models\\User"]
              );
            if (isset($result[0]->url_md)) {
                $user->profile_pic_url = $result[0]->url_md;
            } else {
                $user->profile_pic_url = null;
            }
        }
        return $user;
    }

    private function expandAddresses($userMap, $request)
    {
        if (isset($request['addresses']) && $request['addresses']) {
            // Custom load for address that converts data and performs a single query to the db
            $userIds = array_keys($userMap);
            $addresses = app('db')->table('addresses')
                ->select('name', 'address_1 as line_1', 'address_2 as line_2', 'city', 'state', 'zip', 'addressable_id as user_id', 'label')
                ->whereIn('addressable_id', $userIds)
                ->where('addressable_type', '=', 'App\Models\User')
                ->get();
            foreach ($addresses as $key => $address) {
                switch ($address->label) {
                    case 'Billing':
                        $userMap[$address->user_id]->billing_address = $address;
                        break;
                    case 'Shipping':
                        $userMap[$address->user_id]->shipping_address = $address;
                        break;
                    case 'Business':
                        $userMap[$address->user_id]->business_address = $address;
                        break;
                }
                unset($address->user_id);
            }
        }
    }

    private function buildCreateOrUpdateQuery(array $request, $pid = null)
    {
        $parameters = array_only($request, [
            'email',
            'public_id',
            'first_name',
            'last_name',
            'phone_number',
            'password',
            'role_id',
            'join_date',
            'sponsor_id',
        ]);
        $keys = array_keys($parameters);
        $keys[] = 'pid';
        $updates = [];
        $updateString = '';
        foreach ($parameters as $key => $parameter) {
            $updates[$key] = $parameter;
            $updates['u_'.$key] = $parameter;
            $updateString .= ''.$key.'=:u_'.$key.', ';
        }
        $updates['pid'] = isset($pid) ? $pid : Pid::create();
        $query = 'INSERT INTO users('.implode(', ', $keys).', created_at, updated_at) '.
            'VALUES(:'.implode(', :', $keys).', NOW(), NOW()) ON DUPLICATE KEY UPDATE '.
            $updateString.'updated_at=NOW();';
        return ['query' => $query, 'parameters' => $updates];
    }

    public function createOrUpdate($request, $pid = null) : User
    {
        $statement = $this->buildCreateOrUpdateQuery($request, $pid);
        app('db')->statement($statement['query'], $statement['parameters']);
        $user = isset($pid) ? User::where('pid', $pid)->first() : User::where('email', $request['email'])->first();
        if (isset($request['billing_address'])) {
            $this->createAddress($request['billing_address'], 'Billing', $user);
        }
        if (isset($request['shipping_address'])) {
            $this->createAddress($request['shipping_address'], 'Shipping', $user);
            if (!isset($request['business_address'])) {
                $this->createAddress($request['shipping_address'], 'Business', $user);
            }
        }
        if (isset($request['business_address'])) {
            $this->createAddress($request['business_address'], 'Business', $user);
        }
        return $user;
    }

    public function createOrUpdateCustomer($newUser)
    {
        $user = User::where('email', '=', $newUser['email'])->withTrashed()->first();
        if ($user === null) {
            // Insert or update race safe, update or create doesn't call on duplicate key
            app('db')->beginTransaction(); // Just in case there is some race condition with replication
            app('db')->statement(
                'INSERT INTO users(pid, email, first_name, last_name, phone_number, password, role_id, join_date, created_at, updated_at)' .
                ' VALUES(?, ?, ?, ?, ?, "", 3, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE email = email',
                [Pid::create(), $newUser['email'], $newUser['first_name'], $newUser['last_name'], $newUser['phone_number']]
            );
            $user = User::where('email', '=', $newUser['email'])->withTrashed()->first();
            app('db')->commit();
        }
        $this->expandAddresses([$user->id => $user], ['addresses' => true]);
        // If the address data was sent in with the request create a new address if the user doesn't have one
        // or update it if the user is a customer and the old address is different from the newly requested address.
        if (isset($newUser['shipping_address']) &&
            (!isset($user->shipping_address) ||
            isset($user->shipping_address) && $user->role_id === 3 && !Address::compare($newUser['shipping_address'], $user->shipping_address))
        ) {
            $this->createAddress($newUser['shipping_address'], 'Shipping', $user);
        }
        if (isset($newUser['billing_address']) &&
            (!isset($user->billing_address) ||
            isset($user->billing_address) && $user->role_id === 3 && !Address::compare($newUser['billing_address'], $user->billing_address))
        ) {
            $this->createAddress($newUser['billing_address'], 'Billing', $user);
        }
        return $user;
    }

    public function updateStatus($pids, $status) : bool
    {
        $userStatus = UserStatus::where('name', $status)->first();
        $users = User::whereIn('pid', $pids)->update(['status' => $statusName]);
        $subscriptions = Subscription::whereIn('user_pid', $pids)->update(['auto_renew' => $userStatus->renew_subscription]);
        return $users;
    }

    public function delete($pid)
    {
        return User::where('pid', $pid)->delete();
    }

    public function getCardToken($userPid)
    {
        return app('db')->table('card_token as ct')
            ->select('ct.*')
            ->join('users as u', 'u.id', '=', 'ct.user_id')
            ->where('u.pid', '=', $userPid)->first();
    }

    private function createAddress(array $address, string $label, User $user)
    {
        app('db')->statement(
            'INSERT INTO addresses(name, address_1, address_2, city, state, zip, label, addressable_id, addressable_type, created_at, updated_at)'.
            ' VALUES(:name, :address_1, :address_2, :city, :state, :zip, :label, :addressable_id, :addressable_type, NOW(), NOW()) ON DUPLICATE KEY UPDATE'.
            ' addressable_id=addressable_id, name=:u_name, address_1=:u_address_1, address_2=:u_address_2, city=:u_city, state=:u_state, zip=:u_zip, updated_at=NOW()',
            $this->convertAddressForInsert($address, $label, $user)
        );
    }

    private function convertAddressForInsert($address, $label, $user)
    {
        $insertArray = []; // Build insert array
        $insertArray['name'] = (empty($address['name']) ? $user->first_name.' '.$user->last_name : $address['name']);
        $insertArray['address_1'] = (empty($address['line_1']) ? '' : $address['line_1']);
        $insertArray['address_2'] = (empty($address['line_2']) ? '' : $address['line_2']);
        $insertArray['city'] = (empty($address['city']) ? '' : $address['city']);
        $insertArray['state'] = (empty($address['state']) ? '' : $address['state']);
        $insertArray['zip'] = (empty($address['zip']) ? '' : $address['zip']);
        $insertArray['label'] = $label;
        $insertArray['addressable_id'] = $user->id;
        $insertArray['addressable_type'] = 'App\\Models\\User'; // To match old structure polymorph
        $insertArray['u_name'] = $insertArray['name'];
        $insertArray['u_address_1'] = $insertArray['address_1'];
        $insertArray['u_address_2'] = $insertArray['address_2'];
        $insertArray['u_city'] = $insertArray['city'];
        $insertArray['u_state'] = $insertArray['state'];
        $insertArray['u_zip'] = $insertArray['zip'];
        return $insertArray;
    }

    public function mapStoreSettings($users)
    {
        foreach ($users as $user) {
            $user->store_settings = $user->rawStoreSettings->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
        }
    }
}
