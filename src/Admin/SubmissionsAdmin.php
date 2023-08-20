<?php

namespace SilverStripers\SubmissionsManager\Admin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DB;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\View\ArrayData;
use SilverStripers\Aurora\Admin\Distributor\PendingDistributors;
use SilverStripers\Aurora\Model\Account\DistributorApplication;
use SilverStripers\SubmissionsManager\Utils\SubmissionItemRequest;
use SilverStripers\SubmissionsManager\Utils\SubmissionsFilterHeader;
use SilverStripers\SubmissionsManager\Utils\SubmissionsSearchContext;

class SubmissionsAdmin extends ModelAdmin
{

    private static $managed_models = [
        SubmittedForm::class
    ];

    private static $url_segment = 'submissions';

    private static $menu_title = 'Form Submissions';

    private static $url_priority = 10;

    public function getTabTitle()
    {
        return 'New';
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        $fields = $form->Fields();
        /* @var $grid GridField */
        if ($grid = $fields->dataFieldByName($this->sanitiseClassName(SubmittedForm::class))) {
            $config = $grid->getConfig();
            $config->removeComponentsByType(GridFieldAddNewButton::class);
            $config->removeComponentsByType(GridFieldExportButton::class);
            $config->removeComponentsByType(GridFieldImportButton::class);

            // aggregate all form summary fields
            /* @var $displayColumns GridFieldDataColumns */
            $displayColumns = $config->getComponentByType(GridFieldDataColumns::class);
            $displayFields = array_merge(
                [
                    'ID' => 'ID',
                    'Created' => 'Submitted on',
                    'ParentName' => [
                        'title' => 'Form name',
                        'callback' => function($record, $columnName, $gridField) {
                            return $record->getParentName();
                        }
                    ],
                ],
                $this->getAggregatedColumns()
            );
            $displayColumns->setDisplayFields($displayFields);


            // update search contexts
            $config->removeComponentsByType(GridFieldFilterHeader::class);
            $config->addComponent(Injector::inst()->createWithArgs(SubmissionsFilterHeader::class, [
                false,
                function ($context) {
                    $this->updateSearchContext($context);
                }
            ]));

            /* @var $detailForm GridFieldDetailForm */
            $detailForm = $config->getComponentByType(GridFieldDetailForm::class);
            $detailForm->setItemRequestClass(SubmissionItemRequest::class);
        }
        return $form;
    }

    protected function getManagedModelTabs()
    {
        $forms = new ArrayList();
        $classes = [
            SubmissionsAdmin::class,
            ProcessedSubmissionsAdmin::class,
            ArchivedSubmissionsAdmin::class,
            SpamSubmissionsAdmin::class
        ];

        foreach ($classes as $class) {
            $singleton = Injector::inst()->get($class);
            $forms->push(ArrayData::create([
                'Title' => $singleton->getTabTitle(),
                'Link' => $singleton->Link(),
                'ClassName' => SubmittedForm::class,
                'LinkOrCurrent' => Controller::has_curr() && get_class(Controller::curr()) == $class ? 'current' : 'section'
            ]));
        }

        return $forms;
    }

    public function getList()
    {
        $list = parent::getList();
        if (get_class($this) === SubmissionsAdmin::class) {
            $list = $list->filter([
                'Status' => 'New',
                'MarkedAsSpam' => 0
            ]);
        }
        return $list;
    }

    public function getAllActiveParents()
    {
        $list = ArrayList::create();
        $query = DB::query('SELECT DISTINCT "ParentClass", "ParentID" FROM "SubmittedForm"');
        while ($row = $query->nextRecord()) {
            if ($record = DataList::create($row['ParentClass'])->byID($row['ParentID'])) {
                $list->push($record);
            }
        }
        return $list;
    }

    public function getAggregatedColumns()
    {
        $columns = [];
        foreach ($this->getAllActiveParents() as $parent) {
            $parentID = $parent->ID;

            $columnSQL = <<<SQL
SELECT DISTINCT
    "SubmittedFormField"."Name" as "Name",
    REPLACE(COALESCE("EditableFormField"."Title", "SubmittedFormField"."Title"), '.', ' ') as "Title",
    COALESCE("EditableFormField"."Sort", 999) AS "Sort"
FROM
    "SubmittedFormField"
        LEFT JOIN "SubmittedForm" ON "SubmittedForm"."ID" = "SubmittedFormField"."ParentID"
        LEFT JOIN "EditableFormField" ON "EditableFormField"."Name" = "SubmittedFormField"."Name"
WHERE
    "SubmittedForm"."ParentID" = '$parentID'
    AND "EditableFormField"."ParentID" = '$parentID'
    AND "EditableFormField"."ShowInSummary" = 1
ORDER BY "Sort", "Title"
SQL;

            $insColumns = DB::query($columnSQL)->map();
            $columns = array_merge($columns, $insColumns);
        }

        return $columns;
    }

    public function updateSearchContext($context)
    {
        $context = $context->setParentsList($this->getAllActiveParents());
        return $context;
    }

}
