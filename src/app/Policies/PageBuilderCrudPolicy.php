<?php

namespace Clevyr\PageBuilder\app\Policies;

use Clevyr\PageBuilder\app\Models\Page;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PageBuilderCrudPolicy
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
        return backpack_user()->can('View Page List');
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create()
    {
        return backpack_user()->can('Create Page');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update()
    {
        return backpack_user()->can('Edit Page');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete()
    {
        return backpack_user()->can('Delete Page');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore()
    {
        return backpack_user()->can('Delete Page');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete()
    {
        return backpack_user()->can('Delete Page');
    }
}
