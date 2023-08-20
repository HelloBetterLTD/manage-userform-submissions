<?php

use SilverStripe\Admin\CMSMenu;
use SilverStripers\SubmissionsManager\Admin\ArchivedSubmissionsAdmin;
use SilverStripers\SubmissionsManager\Admin\ProcessedSubmissionsAdmin;
use SilverStripers\SubmissionsManager\Admin\SpamSubmissionsAdmin;

CMSMenu::remove_menu_class(ArchivedSubmissionsAdmin::class);
CMSMenu::remove_menu_class(ProcessedSubmissionsAdmin::class);
CMSMenu::remove_menu_class(SpamSubmissionsAdmin::class);

