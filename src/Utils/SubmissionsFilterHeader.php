<?php

namespace SilverStripers\SubmissionsManager\Utils;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;

class SubmissionsFilterHeader extends GridFieldFilterHeader
{

    public function getSearchContext(GridField $gridField)
    {
        if (!$this->searchContext) {
            $this->searchContext = SubmissionsSearchContext::create(SubmittedForm::class);
        }
        return $this->searchContext;
    }

}
