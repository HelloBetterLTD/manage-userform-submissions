<?php

namespace SilverStripers\SubmissionsManager\Admin;

use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class ArchivedSubmissionsAdmin extends SubmissionsAdmin
{

    private static $managed_models = [
        SubmittedForm::class
    ];

    private static $url_segment = 'submissions/archived';

    private static $menu_title = 'Archived Form Submissions';

    private static $url_priority = 100;

    public function getTabTitle()
    {
        return 'Archived';
    }

    public function getList()
    {
        $list = parent::getList();
        $list = $list->filter([
            'Status' => 'Archived',
            'MarkedAsSpam' => 0
        ]);
        return $list;
    }
}
