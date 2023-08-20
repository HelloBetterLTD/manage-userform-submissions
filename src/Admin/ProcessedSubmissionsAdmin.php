<?php

namespace SilverStripers\SubmissionsManager\Admin;

use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class ProcessedSubmissionsAdmin extends SubmissionsAdmin
{

    private static $managed_models = [
        SubmittedForm::class
    ];

    private static $url_segment = 'submissions/processed';

    private static $menu_title = 'Processed Form Submissions';

    private static $url_priority = 100;

    public function getTabTitle()
    {
        return 'Processed';
    }

    public function getList()
    {
        $list = parent::getList();
        $list = $list->filter([
            'Status' => 'Processed',
            'MarkedAsSpam' => 0
        ]);
        return $list;
    }
}
