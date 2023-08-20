<?php

namespace SilverStripers\SubmissionsManager\Utils;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Search\SearchContext;

class SubmissionsSearchContext extends SearchContext
{
    private $parentList = null;

    public function setParentsList($list)
    {
        $this->parentList = $list;
        return $this;
    }

    public function getParentsSources()
    {
        $parentsList = [];
        /* @var $parent DataObject */
        foreach ($this->parentList as $parent) {
            $parentsList[sprintf('%s (%s)', $parent->singular_name(), $parent->ID)] = sprintf(
                '%s - (%s)',
                $parent->getTitle(),
                $parent->singular_name()
            );
        }
        return $parentsList;
    }

    public function getSearchFields()
    {
        $fields = FieldList::create(
            DropdownField::create('Search__Parent', 'Parent / Form')
                ->setSource(
                    $this->getParentsSources()
                )
                ->setEmptyString('Select a form'),
            DateField::create('Search__From', 'From date'),
            DateField::create('Search__To', 'To date'),
        );
        return $fields;
    }

    public function getQuery($searchParams, $sort = false, $limit = false, $existingQuery = null)
    {
        $query = parent::getQuery($searchParams, $sort, $limit, $existingQuery);
        return $this->search($query);
    }

    private function search(DataList $query): DataList
    {
        /** @var DataObject $modelObj */
        $modelObj = Injector::inst()->create($this->modelClass);
        $searchableFields = $modelObj->searchableFields();

        if (!empty($this->searchParams['Search__Parent'])) {
            foreach ($this->parentList as $parent) {
                if ($this->searchParams['Search__Parent'] == sprintf('%s (%s)', $parent->singular_name(), $parent->ID)) {
                    $query = $query->filter([
                        'ParentID' => $parent->ID,
                        'ParentClass' => $parent->ClassName,
                    ]);
                }
            }
        }
        if (!empty($this->searchParams['Search__From'])) {
            $query = $query->filter('Created:GreaterThanOrEqual', $this->searchParams['Search__From']);
        }
        if (!empty($this->searchParams['Search__To'])) {
            $query = $query->filter('Created:LessThanOrEqual', $this->searchParams['Search__To']);
        }
        return $query;
    }
}
