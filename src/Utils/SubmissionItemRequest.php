<?php

namespace SilverStripers\SubmissionsManager\Utils;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\ORM\ValidationResult;

class SubmissionItemRequest extends GridFieldDetailForm_ItemRequest
{

    private static $allowed_actions = [
        'ItemEditForm'
    ];

    public function ItemEditForm()
    {
        $form =  parent::ItemEditForm();
        $actions = $form->Actions();
        /* @var $majorActions CompositeField */
        $majorActions = $actions->fieldByName('MajorActions');

        if (!$this->record->MarkedAsSpam) {
            if (in_array($this->record->Status, ['New'])) {
                $majorActions->push(
                    FormAction::create('doProcessed', 'Mark as processed')
                        ->setUseButtonTag(true)
                        ->addExtraClass('btn btn-outline-primary')
                );
            }

            if (in_array($this->record->Status, ['New', 'Processed'])) {
                $majorActions->push(
                    FormAction::create('doArchive', 'Archive')
                        ->setUseButtonTag(true)
                        ->addExtraClass('btn btn-outline-danger')
                );
            }
        }

        return $form;
    }

    public function doProcessed($data, $form)
    {
        $this->record->Status = 'Processed';
        $this->record->write();

        $toplevelController = $this->getToplevelController();
        if ($toplevelController && $toplevelController instanceof LeftAndMain) {
            $backForm = $toplevelController->getEditForm();
            $backForm->sessionMessage('Marked as processed', 'good', ValidationResult::CAST_HTML);
        }

        //when an item is deleted, redirect to the parent controller
        $controller = $this->getToplevelController();
        $controller->getRequest()->addHeader('X-Pjax', 'Content'); // Force a content refresh
        return $controller->redirect($this->getBackLink(), 302); //redirect back to admin section
    }

    public function doArchive($data, $form)
    {
        $this->record->Status = 'Archived';
        $this->record->write();

        $toplevelController = $this->getToplevelController();
        if ($toplevelController && $toplevelController instanceof LeftAndMain) {
            $backForm = $toplevelController->getEditForm();
            $backForm->sessionMessage('Archived submission', 'good', ValidationResult::CAST_HTML);
        }

        //when an item is deleted, redirect to the parent controller
        $controller = $this->getToplevelController();
        $controller->getRequest()->addHeader('X-Pjax', 'Content'); // Force a content refresh
        return $controller->redirect($this->getBackLink(), 302); //redirect back to admin section
    }

}
