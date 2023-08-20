<?php

namespace SilverStripers\SubmissionsManager\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class SubmittedFormExtension extends DataExtension
{

    private static $db = [
        'Status' => 'Enum("New, Processed, Archived", "New")'
    ];

    private static $defaults = [
        'Status' => 'New'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Status');
    }

    public function getParentName()
    {
        /* @var $submission SubmittedForm */
        $submission = $this->owner;
        $parent = $submission->Parent();
        return $parent ? $parent->getTitle() : 'N/A';
    }

}
