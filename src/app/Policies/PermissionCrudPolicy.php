<?php

namespace Clevyr\PageBuilder\app\Policies;

use Clevyr\PageBuilder\app\Models\Page;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionCrudPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * @return void
     */
    public function viewAny() : void
    {
    }

    /**
     * Determine whether the user can view the model.
     * @return bool
     */
    public function view() : bool
    {
        return backpack_user()->can('View Permission List');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create()
    {
        return backpack_user()->can('Create Permission');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update()
    {
        return backpack_user()->can('Edit Permission');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete()
    {
        return backpack_user()->can('Delete Permission');
    }
}
