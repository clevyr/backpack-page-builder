<?php

namespace Clevyr\PageBuilder\app\Http\Controllers\Admin;

use Backpack\ReviseOperation\ReviseOperation;
use Exception;
use Venturecraft\Revisionable\Revision;

/**
 * Class PageBuilderSectionPivotCrudController
 * @package Clevyr\PageBuilder\app\Http\Controllers\Admin
 */
class PageBuilderSectionPivotCrudController extends PageBuilderBaseController
{
    use ReviseOperation;

    /**
     * Setup
     *
     * @throws Exception
     */
    public function setup()
    {
        $this->crud->setModel(config('backpack.pagebuilder.page_sections_pivot_model',
            'Clevyr\PageBuilder\app\Models\PageSectionsPivot'));
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/section-data');
        $this->crud->setEntityNameStrings('page section data', 'page section data');

        parent::setup();
    }

    /**
     * Restore a specific revision for the specified resource.
     *
     * Used via AJAX in the revisions view
     *
     * @param int $id
     *
     * @return JSON Response containing the new revision that was created from the update
     * @return HTTP 500 if the request did not contain the revision ID
     */
    public function restoreRevision($id)
    {
        $this->crud->hasAccessOrFail('revise');

        $revisionId = \Request::input('revision_id', false);
        if (! $revisionId) {
            abort(500, 'Can\'t restore revision without revision_id');
        } else {
            $entry = $this->crud->getEntryWithoutFakes($id);
            $revision = Revision::findOrFail($revisionId);

            // Update the revisioned field with the old value
            $entry->update([$revision->key => json_decode($revision->old_value)]);

            $this->data['entry'] = $this->crud->getEntry($id);
            $this->data['crud'] = $this->crud;
            $this->data['revisions'] = $this->crud->getRevisionsForEntry($id); // Reload revisions as they have changed

            // Rebuild the revision timeline HTML and return it to the AJAX call
            return view($this->crud->get('revise.timelineView') ?? 'revise-operation::revision_timeline', $this->data);
        }
    }
}
