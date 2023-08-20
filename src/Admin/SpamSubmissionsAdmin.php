<?php

namespace SilverStripers\SubmissionsManager\Admin;

use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class SpamSubmissionsAdmin extends SubmissionsAdmin
{

    private static $managed_models = [
        SubmittedForm::class
    ];

    private static $url_segment = 'submissions/spam';

    private static $menu_title = 'Processed Form Submissions';

    private static $url_priority = 100;

    public function getTabTitle()
    {
        return 'Spam';
    }


    public function getList()
    {
        $list = parent::getList();
        $list = $list->filter([
            'MarkedAsSpam' => 1
        ]);
        return $list;
    }
}
