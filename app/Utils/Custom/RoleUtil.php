<?php

namespace App\Utils\Custom;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Utils\Util;

class RoleUtil extends Util
{

    public function getRoles($business_id, $mutate=false) {
        $roles = Role::where('business_id', $business_id)
                    ->select([
                        'name', 
                        'id', 
                        'is_default', 
                        'business_id'
                    ])
                    ->get();

        return $mutate ? $roles->toArray() : $roles;
    }

    public function getRoleById($business_id, $roleId, $mutate=false) {
        $role = Role::where('business_id', $business_id)
                    ->where('id', $roleId)
                    ->select([
                        'name', 
                        'id', 
                        'is_default', 
                        'business_id'
                    ])
                    ->first();

        return $mutate ? $role->toArray() : $role;
    } 

    public function getRolebyRoleName($business_id, $roleName, $mutate=false, $guard_name='web') {
        $role = Role::where('business_id', $business_id)
                    ->where('name', $roleName)
                    ->where('guard_name', $guard_name)
                    ->select([
                        'name', 
                        'id', 
                        'is_default', 
                        'business_id'
                    ])
                    ->first();

        return $mutate ? $role->toArray() : $role;
    } 

    public function check_permission_existence($business_id, $role_id, $columnToHide, $guard_name='web', $permission_target=null) {
        $permission_exists = DB::table('roles')
                        ->where('roles.id', $role_id)
                        ->where('roles.business_id', $business_id)
                        ->where('roles.guard_name', $guard_name)
                        ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
                        ->where('role_has_permissions.role_id', $role_id)
                        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                        ->where('permissions.name', $columnToHide)
                        ->where('permissions.guard_name', $guard_name);
        
        if(!empty($target)) {
            $permission_exists = $permission_exists->where('permissions.target', $permission_target);
        }

        $permission_exists = $permission_exists->exists();

        return $permission_exists ? true : false;
    }

    public function createdtpermission($business_id, $roleId, $dtpermission, $guard_name='web') {
        $input = [];
        $permission['name'] = $dtpermission;
        $permission['target'] = 'dt';
        $permission['guard_name'] = $guard_name;
        $permission['created_at'] = date("Y-m-d H:i:s");
        $permission['updated_at'] = date("Y-m-d H:i:s");
        
        $permission_id = DB::table('permissions') 
                            ->insertGetId($permission); 

        if (empty($permission_id)) {
            return false;
        }

        $permission_link['permission_id'] = $permission_id;
        $permission_link['role_id'] = $roleId;

        $link_permission = DB::table('role_has_permissions') 
                            ->insert($permission_link);

        return $link_permission ? true : false;
    }

    public function getdtpermissions($business_id, $role_id, $permission_target = 'dt', $guard_name='web') {
        $permissions = [];
        $dt_permissions = DB::table('permissions')
                            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                            ->where('permissions.guard_name', $guard_name)
                            ->where('role_has_permissions.role_id', $role_id)
                            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id') 
                            ->where('roles.business_id', $business_id);

        if(!empty($permission_target)) {
            $dt_permissions = $dt_permissions->where('permissions.target', $permission_target);
        }

        $dt_permissions = $dt_permissions
                            ->select('permissions.*', 'permissions.name as permission_name')
                            ->get();

        foreach ($dt_permissions as $dt_permission) {
            $permissions[] = $dt_permission->permission_name;
        }

        return $permissions;
    }
}